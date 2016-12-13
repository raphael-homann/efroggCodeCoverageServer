<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace Efrogg\Coverage\Storage;


class CoverageCallback extends StorageModel
{

    protected static $_tableName = 'cc_callback';
    protected static $_primaryKey = 'id_callback';
    protected static $_relations = array();

    protected static $_tableFields = array(
        'id_data',
        'id_session',
        'hash',
        'count',
        'detail',
    );

    public $id_callback;
    public $id_data;
    public $id_session;
    public $hash;
    public $count;
    public $detail;

    protected static function defineRelations()
    {
        // une session a un projet
//        self::addRelationOneToOne('id_project','\\Efrogg\\Coverage\\Storage\\CoverageProject','id_project');
    }

}