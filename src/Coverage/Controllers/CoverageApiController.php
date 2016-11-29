<?php

namespace efrogg\Coverage\Controllers;

use efrogg\Coverage\Storage\CoverageFile;
use efrogg\Coverage\Storage\CoverageProject;
use efrogg\Coverage\Storage\CoverageProjectExplorer;
use efrogg\Coverage\Storage\CoverageSession;
use efrogg\Coverage\Storage\CoverageStorageInstaller;
use Efrogg\Db\Adapters\DbAdapter;
use Efrogg\Db\Tools\DbTools;
use Efrogg\Webservice\Webservice;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CoverageApiController extends Webservice
{
    protected $projectName;
    protected $sessionId;
    protected $exclusions = [
        "/_deprecated/i",
        "/tcpdf/i",
        "/fpdi/"
    ];

    public function __construct(DbAdapter $db)
    {
        parent::__construct($db);
    }

    public function find($id)
    {
        $res = $this->db->execute("select * FROM Persons");
        return new JsonResponse($res->fetchAll());
    }

    public function rollback(Request $request, Application $application)
    {
        $storage = new CoverageStorageInstaller($this->db);
        $success = $storage->down();
        if ($success >= 0) {
            return new JsonResponse(array("ACK" => "success", "batch_removed" => $storage->getBatchCount()));
        }
        return new JsonResponse(array("ACK" => "failed"));
    }

    public function migrate(Request $request, Application $application)
    {
        $storage = new CoverageStorageInstaller($this->db);
        $success = $storage->up();
        if ($success) {
            return new JsonResponse(array("ACK" => "success", "batch_created" => $storage->getBatchCount()));
        }
        return new JsonResponse(array("ACK" => "failed", "message" => $storage->getLastError()));

    }

    public function sendCoverage(Request $request, Application $application)
    {
        $this->extractProjectData($request);
        $coverageData = $request->request->get("data");

        $project = CoverageProject::findOrCreate(array("name" => $this->projectName));
        if ($project->isNew()) {
            $project->save();
        }

        $session = CoverageSession::findOrCreate(array("session_name" => $this->sessionId));
        if ($session->isNew()) {
            $session->started_at = array("NOW()");
            $session->id_project = $project->id_project;
            $session->setCoverageProject($project);
            $session->save();
        }

        foreach ($coverageData as $fileName => $lines) {
            $pathHash = DbTools::getHash32($fileName);

            $file = CoverageFile::findOrCreate(array(
                "id_project" => $project->id_project,
                "path_hash" => $pathHash,
                "path" => $fileName
            ));
            if ($file->isNew()) {
                $file->save();
            }


            $sql = "INSERT INTO cc_lines
              (id_file,id_session,line_number,status) VALUES ";

            $linesSQL = array();
            foreach ($lines as $number => $status) {
                $linesSQL[] = "(" . (int)$file->id_file . "," .(int)$session->id_session.",".(int)$number.','.(int)$status.")";
            }


            $sql .= implode(",", $linesSQL) . " ON DUPLICATE KEY UPDATE status = GREATEST(status,VALUES(status)) ";
            $this->db->execute($sql);
//            $session->addLines($lines);
        }

        return new JsonResponse(array("ACK" => "success","session"=>$session->id_session));

    }

    public function completeProject($idProject) {
//        $this->updateNtree($id_project);
//exit;
//        $this->resetProject($idProject);       //todo
        $project = CoverageProject::findOne(array("id_project"=>$idProject));
        if($project) {
            if(file_exists($project->path) && is_dir($project->path)) {
                $explorer = new CoverageProjectExplorer($idProject,$project->path);
                $explorer -> addExclusions($this->exclusions);
                $explorer -> discover();
                $this->updateNtree($idProject);
                return new JsonResponse(array("files"=>$explorer->getAddedFiles()+$explorer->getUpdatedFiles()));

            }
            throw new HttpException(404,"path not found");
        } else {
            throw new HttpException(404,"project not found");
        }
    }

    public function configureProject($idProject,Request $request) {
        $project = CoverageProject::findOne(array("id_project"=>$idProject));
        if($project) {
           if($request->request->has("path")) {
                $project->path = $request->request->get("path");
            }
            $project->save();
        } else {
            throw new HttpException(404,"project not found");
        }

        return new JsonResponse(array("ACK" => "success"));
    }


    protected function extractProjectData(Request $request)
    {
        $this->projectName = $request->request->get("_project");
        $this->sessionId = $request->request->get("_session");
    }

    private function updateNtree($id_project)
    {
//$this->db->execute("UPDATE cc_files SET nleft = NULL,nright=NULL,level_depth=NULL");//TODO
        $files = CoverageFile::find(array("id_project" => $id_project),array("path"=>"ASC"));
        $n = 0;
        $lastLevel=-1;           // level du fichier précédent
        $lastOfLevel=array();
        $lastFilePath='';
        $currentLevel = 0;

        /** @var CoverageFile $file */
        foreach($files AS $file) {
//echo "<br> ".$file->path;
            $currentLevel = count(explode("/",$file->path))-1;

            if($currentLevel > $lastLevel && !empty($lastFilePath) && strpos($file->path,$lastFilePath)!==0) {
//                echo " skip ";

                // on descend dans un dossier différent (/avis => /cache/tutu) => ignore
                continue;
            }
            if($currentLevel > $lastLevel ) {
//echo " descend ";
                // on descend
            } elseif($currentLevel <= $lastLevel ) {
//echo " reste ou remonte ($currentLevel <= $lastLevel)";
                // on remonte
                /** @var CoverageFile $lastFileOfLevel */
                for($i = $lastLevel;$i>=$currentLevel;$i--) {
                    $lastFileOfLevel = $lastOfLevel[$i];
                    if(!is_null($lastFileOfLevel)) {
//    echo "last[$i] : ".$lastFileOfLevel->path;
                        $lastFileOfLevel->nright = $n++;
//    echo " - right ".$lastFileOfLevel->nright;

                    }
                }
            }

            $file -> nleft = $n++;
//echo " - left ".$file -> nleft;

            $file->level_depth = $currentLevel;
            $lastOfLevel[$currentLevel] = $file;

            $lastLevel = $currentLevel;
            $lastFilePath = $file->path;
//                var_dump($file);
//            exit;
        }


        while($currentLevel>=0) {
            $lastFileOfLevel = $lastOfLevel[$currentLevel];
            if(!is_null($lastFileOfLevel)) {
//echo "last[$currentLevel] : ".$lastFileOfLevel->path;
                $lastFileOfLevel->nright = $n++;
//echo " - right ".$lastFileOfLevel->nright;
            }
            $currentLevel--;
        }
        // et on finit en remontant les fichiers laissés

//exit;
        /** @var CoverageFile $file */
        foreach($files AS $file) {
            $file->save();
//echo "<br>".$file->path." - $file->level_depth - ".$file -> nleft."-".$file->nright;
        }
    }

    private function resetProject($id)
    {
        $this->db->execute("TRUNCATE TABLE cc_files");
        $this->db->execute("TRUNCATE TABLE cc_lines");
    }
}