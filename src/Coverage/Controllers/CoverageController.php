<?php

namespace Coverage\Controllers;

use efrogg\Db\Adapters\DbAdapter;
use efrogg\Webservice\Webservice;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CoverageController extends Webservice
{
    protected $projectName;
    protected $sessionId;

    public function __construct(DbAdapter $db) {
        parent::__construct($db);
    }

    public function find($id) {
        $res = $this-> db -> execute("select * FROM Persons");
        return new JsonResponse($res->fetchAll());
    }

    public function sendCoverage(Request $request, Application $application) {
        $this->extractProjectData($request);
        $coverageData = $request->request->get("data");
        return new JsonResponse(array("ACK"=>"success"));

    }


    protected function extractProjectData(Request $request)
    {
        $this-> projectName = $request->request -> get("_project");
        $this-> sessionId = $request->request -> get("_session");
    }
}