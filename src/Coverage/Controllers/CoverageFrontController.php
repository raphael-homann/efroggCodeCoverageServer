<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 28/06/16
 * Time: 09:20
 */

namespace efrogg\Coverage\Controllers;


use efrogg\Coverage\Renderer\CoverageDirectoryRenderer;
use efrogg\Coverage\Storage\CoverageFile;
use efrogg\Coverage\Storage\CoverageProject;
use efrogg\Coverage\Storage\CoverageSession;
use efrogg\Db\Adapters\DbAdapter;
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
        $this->twig->addFilter(new \Twig_SimpleFilter("toPath",function($str) {return $this->extractFilename($str);}));
    }

    public function extractFilename($path) {
        if(empty($path)) return '[HOME]';
        return array_pop(explode("/",$path));
    }



    public function displayFile($id_session,$id_file) {
        $renderer = new CoverageDirectoryRenderer('cc_files', $this->db);
//        $renderer->setTwig($this->twig);
        $renderer->setIdSession($id_session);
        $renderer->setIdFile($id_file);
         return new Response($this->twig->render("list.twig",$renderer->getData()));
    }

    public function displaySession($id_session) {
        $session = CoverageSession::findOne(["id_session"=>$id_session]);
        if($session) {
            // cherche le point racine du projet
            $file = CoverageFile::findOne([
                "level_depth"=>0,
                "id_project"=>$session->id_project
            ]);
            if($file) {
                return $this->displayFile($id_session,$file->id_file);
            }
            return new Response("root not found",404);
        }
        return new Response("session not found",404);
    }
    public function displayProject($id_project) {
        $project = CoverageProject::findOne(["id_project"=>$id_project]);

        if($project) {
            $sessions = CoverageSession::find(["id_project"=>$id_project]);
            return new Response($this->twig->render("project.twig",[
                "project"=>$project,
                "sessions"=>$sessions
            ]));

        }
        return new Response("project not found",404);
    }
    public function displayIndex()
    {
        $projects = CoverageProject::find();
        return new Response($this->twig->render("index.twig", [
            "projects" => $projects
        ]));
    }

    public function completeProject($id_project) {
        $api = new CoverageApiController($this->db);
        $api->completeProject($id_project);
        return RedirectResponse::create("/project/".$id_project);

    }

    public function displayConfigureProject($id_project) {
        $project = CoverageProject::findOne(["id_project"=>$id_project]);
        return new Response($this->twig->render("configure-project.twig", [
            "project" => $project
        ]));
    }
    public function configureProject($id_project,Request $request) {
        var_dump($id_project);
        $project = CoverageProject::findOne(["id_project"=>$id_project]);
        if($project->isNew()) {
            return new Response("project not found",404);
        } else {
            $project->path=rtrim($request->request->get("path"),"/");
            if($project->save()) {
                return RedirectResponse::create("/project/".$id_project);
            } else {
                throw new Exception("erreur sauvegarde");
            }
        }
    }



}