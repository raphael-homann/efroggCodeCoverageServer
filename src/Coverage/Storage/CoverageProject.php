<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace efrogg\Coverage\Storage;



class CoverageProject extends StorageModel
{

    protected static $_tableName = 'cc_projects';
    protected static $_primaryKey = 'id_project';

    protected static $_tableFields = array(
        'name',
        'path'
    );

    public $id_project;
    public $name;
    public $path;

    protected static $_relations = array();

    protected static function defineRelations() {
        // un projet a plusieurs sessions
        self::addRelationOneToMany('id_project','\\efrogg\\Coverage\\Storage\\CoverageSession','id_project');
        self::addRelationOneToMany('id_project','\\efrogg\\Coverage\\Storage\\CoverageFile','id_project');
    }

}