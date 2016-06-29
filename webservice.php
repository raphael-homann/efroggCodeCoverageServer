<?php
use efrogg\Coverage\Provider\CoverageApiProvider;
use efrogg\Db\Adapters\Pdo\PdoDbAdapter;
use efrogg\Webservice\WebserviceBootstrap;
use Silex\Application;

$autoloader = require "vendor/autoload.php";

\PicORM\PicORM::configure(array(
    'datasource' => new PDO("mysql:dbname=web;host=test_mysql","root","root")
));

$bootstrap = new WebserviceBootstrap(array("allow_debug"=>true));
$bootstrap->setAuth("9D22NNDJ721JHMMGECKRPTMHHKPGHPPV");
$bootstrap->addProvider("/api",new CoverageApiProvider($app));
$bootstrap->setDb(new PdoDbAdapter(\PicORM\PicORM::getDataSource()));
$bootstrap->run();


