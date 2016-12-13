<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace Efrogg\Coverage\Storage;


class CoverageUrl extends StorageModel
{

    protected static $_tableName = 'cc_url';
    protected static $_primaryKey = 'id_url';
    protected static $_relations = array();

    protected static $_tableFields = array(
        'id_session',
        'url',
    );


    public $id_url;
    public $id_session;
    public $url;

    protected static function defineRelations() {
        // une session a un projet
//        self::addRelationOneToOne('id_project','\\Efrogg\\Coverage\\Storage\\CoverageProject','id_project');
    }

}