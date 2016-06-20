<?php
namespace efrogg\Coverage;


use efrogg\Coverage\Controllers\CoverageController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class CoverageProvider implements ControllerProviderInterface{

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

        $app["coverage"] = function($app) {
            return new CoverageController($app['db']);
        };
//        $controllers->get("/test/{id}", "coverage:find");
        $controllers->post("/sendCoverage", "coverage:sendCoverage");

        return $controllers;
    }
}