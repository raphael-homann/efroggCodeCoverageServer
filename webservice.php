<?php
use efrogg\Coverage\CoverageProvider;
use efrogg\Db\Adapters\Mysql\MysqlDbAdapter;
use efrogg\Db\Adapters\Pdo\PdoDbAdapter;
use eFrogg\Webservice\Exception\HttpJsonException;
use efrogg\Webservice\WebserviceAuthenticator;
use efrogg\Webservice\WebserviceBootstrap;
use Silex\Application;

$autoloader = require "vendor/autoload.php";

$bootstrap = new WebserviceBootstrap(array("allow_debug"=>true));
$bootstrap->setAuth("9D22NNDJ721JHMMGECKRPTMHHKPGHPPV");
$bootstrap->addProvider("/api",new CoverageProvider($app));
$bootstrap->setDb(new PdoDbAdapter(new PDO("mysql:dbname=web;host=test_mysql","root","root")));
$bootstrap->run();


