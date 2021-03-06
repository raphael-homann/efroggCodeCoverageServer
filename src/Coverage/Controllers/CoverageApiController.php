<?php

namespace Efrogg\Coverage\Controllers;

use Efrogg\Coverage\Storage\CoverageCallback;
use Efrogg\Coverage\Storage\CoverageCallbackUrl;
use Efrogg\Coverage\Storage\CoverageData;
use Efrogg\Coverage\Storage\CoverageFile;
use Efrogg\Coverage\Storage\CoverageProject;
use Efrogg\Coverage\Storage\CoverageProjectExplorer;
use Efrogg\Coverage\Storage\CoverageSession;
use Efrogg\Coverage\Storage\CoverageStorageInstaller;
use Efrogg\Coverage\Storage\CoverageUrl;
use Efrogg\Db\Adapters\DbAdapter;
use Efrogg\Db\Tools\DbTools;
use Efrogg\Webservice\Webservice;
use PicORM\Exception;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CoverageApiController extends Webservice
{
    protected $projectName;
    protected $sessionId;
    protected $exclusions = [
        "/_deprecated/i",
        "/_old/i",

        // lib... useless ?
        "/kint2/i",
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
        $data = $request->request->get("data");

        $coverageData = $data["coverage"];
        $customData = $data["custom"];

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
        $url = null;

        foreach($customData as $type => $type_data) {
            if(!empty($type_data)) {
                // on a de la data (error / deprecated
                // on stocke l'url
                if(null === $url) {
                    $url = CoverageUrl::findOrCreate([
                        "url"=>$data["url"],
                        "id_session"=>$session->id_session,
                    ]);
                    if($url->isNew()) {
                        $url -> save();
                    }
                }
            }

            foreach($type_data as $hash => $one_data) {
                $data = CoverageData::findOrCreate(array(
                    "hash" => $hash,
                    "id_session" => (int)$session->id_session
                ));
                if($data->isNew()) {
                    $data->type = $type;
                    $data->detail = json_encode($one_data["data"]);
                    $data->severity = $one_data["severity"];
                    $data->count = $one_data["count"];
                } else {
                    $data->count += $one_data["count"];
                }
                $data->save();


                foreach($one_data["backtrace"] as $hash_trace => $traceDetail) {
                    $traceModel = CoverageCallback::findOrCreate([
                       "id_data"=>$data->id_data,
                        "id_session"=>$session->id_session,
                        "hash"=>$hash_trace
                    ]);
                    if($traceModel -> isNew()) {
                        $traceModel->count = $traceDetail["count"];
                        $traceModel->detail = json_encode($traceDetail["trace"]);
                    } else {
                        $traceModel->count += $traceDetail["count"];
                    }
                    $traceModel->save();

                    try{

                        $link = CoverageCallbackUrl::findOrCreate([
                            "id_callback"=>$traceModel->id_callback,
                            "id_url"=>$url->id_url,
                        ]);
                        if($link->isNew()) {
                            $link->id_session = $session->id_session;
                            $link->save();
                        }
                    } catch(Exception $e) {
                        var_dump($e);
                    }
                }


            }
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
        $this->db->execute("DELETE FROM cc_files WHERE id_project = ?",array($id));
//        $this->db->execute("TRUNCATE TABLE cc_lines");
    }
}