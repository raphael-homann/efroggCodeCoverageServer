<?php
use efrogg\Coverage\Provider\CoverageApiProvider;
use Efrogg\Db\Adapters\Pdo\PdoDbAdapter;
use Efrogg\Webservice\WebserviceBootstrap;
use Silex\Application;

$autoloader = require "vendor/autoload.php";

\PicORM\PicORM::configure(array(
    'datasource' => new PDO("mysql:dbname=web;host=test_mysql","root","root")
));
$application = new Silex\Application();
\efrogg\Coverage\Storage\StorageModel::setApp($application);

$application["db"] = new PdoDbAdapter(\PicORM\PicORM::getDataSource());

ini_set("display_errors","on");
$bootstrap = new WebserviceBootstrap(array("allow_debug"=>true));
$bootstrap->setAuth("9D22NNDJ721JHMMGECKRPTMHHKPGHPPV");
$bootstrap->addProvider("/api",new CoverageApiProvider($app));
$bootstrap->setDb(new PdoDbAdapter(\PicORM\PicORM::getDataSource()));
$bootstrap->run();


