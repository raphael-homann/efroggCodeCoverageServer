<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 24/06/16
 * Time: 17:02
 */

use Efrogg\Coverage\Provider\CoverageFrontProvider;
use Efrogg\Db\Adapters\Pdo\PdoDbAdapter;
use PicORM\PicORM;
use Silex\Provider\ServiceControllerServiceProvider;

ini_set("display_errors","on");

$autoload = include_once 'vendor/autoload.php';

PicORM::configure(array(
    'datasource' => new PDO("mysql:dbname=web;host=test_mysql", "root", "root")
));


$application = new Silex\Application();
\Efrogg\Coverage\Storage\StorageModel::setApp($application);

$application["debug"] = true;
$application["db"] = new PdoDbAdapter(PicORM::getDataSource());
$application["twig"] = function() {
    $twig = new Twig_Environment(
        new Twig_Loader_Filesystem("templates"),
        array(
            'debug' => true,
        ));
    $twig->addExtension(new Twig_Extension_Debug());
    return $twig;
};

$application->register(new ServiceControllerServiceProvider());
$application->mount("/", new CoverageFrontProvider());
$application->run();
exit;





