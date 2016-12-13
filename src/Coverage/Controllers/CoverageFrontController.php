<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 28/06/16
 * Time: 09:20
 */

namespace Efrogg\Coverage\Controllers;


use Efrogg\Coverage\Renderer\CoverageDirectoryRenderer;
use Efrogg\Coverage\Storage\CoverageData;
use Efrogg\Coverage\Storage\CoverageFile;
use Efrogg\Coverage\Storage\CoverageProject;
use Efrogg\Coverage\Storage\CoverageSession;
use Efrogg\Db\Adapters\DbAdapter;
use PicORM\Exception;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CoverageFrontController
{
    /** @var  DbAdapter */
    protected $db;

    /** @var \Twig_Environment */
    protected $twig;

    /** @var Application */
    private $app;

    /**
     * CoverageFrontController constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->db = $app["db"];
        $this->twig = $app["twig"];
        $this->twig->addFilter(new \Twig_SimpleFilter("toPath", function ($str) {
            return $this->extractFilename($str);
        }));
    }

    public function extractFilename($path)
    {
        if (empty($path)) {
            return '[HOME]';
        }
        return array_pop(explode("/", $path));
    }


    public function displayFile($id_session, $id_file)
    {
        $renderer = new CoverageDirectoryRenderer('cc_files', $this->db);
//        $renderer->setTwig($this->twig);
        $renderer->setIdSession($id_session);
        $renderer->setIdFile($id_file);
        return new Response($this->twig->render("list.twig", $renderer->getData()));
    }


    public function displaySession($id_session)
    {
        $session = CoverageSession::findOne(["id_session" => $id_session]);
        if ($session) {
            // cherche le point racine du projet
            $file = CoverageFile::findOne([
                "level_depth" => 0,
                "id_project" => $session->id_project
            ]);
            if ($file) {
                return $this->displayFile($id_session, $file->id_file);
            }
            return new Response("root not found", 404);
        }
        return new Response("session not found", 404);
    }

    public function displayProject($id_project)
    {
        $project = CoverageProject::findOne(["id_project" => $id_project]);

        if ($project) {
            $sessions = CoverageSession::find(["id_project" => $id_project]);
            return new Response($this->twig->render("project.twig", [
                "project" => $project,
                "sessions" => $sessions
            ]));

        }
        return new Response("project not found", 404);
    }

    public function displayIndex()
    {
        $projects = CoverageProject::find();
        return new Response($this->twig->render("index.twig", [
            "projects" => $projects
        ]));
    }

    public function displayData($id_session,$type_data) {

        $session = CoverageSession::findOne(["id_session" => $id_session]);
        $project = CoverageProject::findOne(["id_project" => $session->id_project]);
        $data = CoverageData::find([
            "id_session" => $id_session,
            "type"=>$type_data
        ],["severity"=>"DESC","count"=>"DESC"]);
        $custom_data = [];
        /** @var CoverageData $oneData */
        foreach($data as $oneData) {
            $custom_data[$this->convertSeverity($oneData->severity)][] = [
                "count"=>$oneData->count,
                "data"=>json_decode($oneData->detail,true)
            ];
        }
        return new Response($this->twig->render("data.twig", [
            "session" => $session,
            "project" => $project,
            "custom_data"=>$custom_data
        ]));

    }

    private function convertSeverity($severity)
    {
        switch($severity) {
            case 2:
                return "warning";
            case 3:
                return "danger";
            case 4:
                return "danger";
//            case 1:
            default:
                return "info";
        }
    }

    public function completeProject($id_project)
    {
        $api = new CoverageApiController($this->db);
        $api->completeProject($id_project);
        return RedirectResponse::create("/project/" . $id_project);

    }

    public function displayConfigureProject($id_project)
    {
        $project = CoverageProject::findOne(["id_project" => $id_project]);
        return new Response($this->twig->render("configure-project.twig", [
            "project" => $project
        ]));
    }

    public function configureProject($id_project, Request $request)
    {
        $project = CoverageProject::findOne(["id_project" => $id_project]);
        if ($project->isNew()) {
            return new Response("project not found", 404);
        } else {
            $project->path = rtrim($request->request->get("path"), "/");
            if ($project->save()) {
                return RedirectResponse::create("/project/" . $id_project);
            } else {
                throw new Exception("erreur sauvegarde");
            }
        }
    }


    public function deleteProject($id_project) {
        $sessions = CoverageSession::find(array("id_project" => $id_project));
        foreach($sessions as $session) {
            $this->doDeleteSession($session);
        }
        $project = CoverageProject::findOne(["id_project" => $id_project]);
        if($this->doDeleteProject($project)) {
            return RedirectResponse::create("/");

        }
        return Response::create("Erreur",503);

    }
    public function deleteSession($id_session)
    {
        $session = CoverageSession::findOne(["id_session" => $id_session]);
        if($session && $this->doDeleteSession($session)) {
            return RedirectResponse::create("/project/" . $session->id_project);
        }
        return Response::create("Erreur",503);
    }

    private function doDeleteSession(CoverageSession $session)
    {
        $result = $this->db->execute("DELETE FROM cc_lines WHERE id_session = ?",array($session->id_session));
        if($result->isValid()) {
            $result = $this->db->execute("DELETE FROM cc_data WHERE id_session = ?", array($session->id_session));
        }
        if($result->isValid()) {
            $result = $this->db->execute("DELETE FROM cc_callback WHERE id_session = ?", array($session->id_session));
        }
        if($result->isValid()) {
            $result = $this->db->execute("DELETE FROM cc_callback_url WHERE id_session = ?", array($session->id_session));
        }
        if($result->isValid()) {
            $result = $this->db->execute("DELETE FROM cc_url WHERE id_session = ?", array($session->id_session));
        }
        if($result->isValid()) {
            echo "<br>deleted session [".$session->id_session."]: ".$this->db->getAffectedRows()." lines";
            return $session->delete();
        } else {
            throw new Exception($result->getErrorMessage());
        }
    }

    private function doDeleteProject(CoverageProject $project)
    {
        $result = $this->db->execute("DELETE FROM cc_files WHERE id_project = ?",array($project->id_project));
        if($result->isValid()) {
            echo "<br>deleted project [".$project->id_project."]: ".$this->db->getAffectedRows()." files";
            return $project->delete();
        } else {
            throw new Exception($result->getErrorMessage());
        }

    }

    private function convertLevel($severity)
    {
    }

}