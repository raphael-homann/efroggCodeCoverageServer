<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 13/12/16
 * Time: 13:13
 */

namespace Efrogg\Coverage\Renderer;


class CoverageTwigExtension extends \Twig_Extension
{
    protected $container;

    public function getFilters() {
        return array(
            'json_decode'   => new \Twig_Filter_Method($this, 'jsonDecode'),
        );
    }

    public function jsonDecode($str) {
        return json_decode($str,true);
    }

}