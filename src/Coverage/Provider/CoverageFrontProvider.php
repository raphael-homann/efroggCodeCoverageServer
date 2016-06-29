<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 28/06/16
 * Time: 08:56
 */

namespace efrogg\Coverage\Provider;


use efrogg\Coverage\Controllers\CoverageFrontController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class CoverageFrontProvider implements ControllerProviderInterface
{

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $app["front"] = function($app) {
            return new CoverageFrontController($app);
        };
//        $controllers->get("/test/{id}", "coverage:find");
        $controllers->get("/", "front:displayIndex");
        $controllers->get("/project/{id_project}", "front:displayProject");
        $controllers->get("/project/{id_project}/complete", "front:completeProject");
        $controllers->get("/session/{id_session}", "front:displaySession");
        $controllers->get("/session/{id_session}/file/{id_file}", "front:displayFile");
        $controllers->get("/project/{id_project}/configure", "front:displayConfigureProject");
        $controllers->post("/project/{id_project}/configure", "front:configureProject");

        return $controllers;
    }
}