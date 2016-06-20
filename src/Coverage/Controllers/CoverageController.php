<?php

namespace Coverage\Controllers;

use efrogg\Db\Adapters\DbAdapter;
use efrogg\Webservice\Webservice;
use Symfony\Component\HttpFoundation\JsonResponse;

class CoverageController extends Webservice
{
    public function __construct(DbAdapter $db) {
        parent::__construct($db);
    }

    public function find($id) {
        $res = $this-> db -> execute("select * FROM Persons");
        return new JsonResponse($res->fetchAll());
    }
}