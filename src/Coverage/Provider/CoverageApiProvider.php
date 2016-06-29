<?php
namespace efrogg\Coverage\Provider;


use efrogg\Coverage\Controllers\CoverageApiController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class CoverageApiProvider implements ControllerProviderInterface{

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
            return new CoverageApiController($app['db']);
        };
//        $controllers->get("/test/{id}", "coverage:find");
        $controllers->post("/sendCoverage", "coverage:sendCoverage");
        $controllers->get("/migrate", "coverage:migrate");
        $controllers->get("/rollback", "coverage:rollback");
        $controllers->get("/project/{idProject}/complete", "coverage:completeProject");
        $controllers->post("/project/{idProject}/configure", "coverage:configureProject");

        return $controllers;
    }
}