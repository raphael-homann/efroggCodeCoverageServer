<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace efrogg\Coverage\Storage;


class CoverageSession extends StorageModel
{

    protected static $_tableName = 'cc_sessions';
    protected static $_primaryKey = 'id_session';
    protected static $_relations = array();

    protected static $_tableFields = array(
        'id_session',
        'id_project',
        'session_name',
        'started_at'
    );

    public $id_session;
    public $id_project;
    public $session_name;
    public $started_at;

    protected static function defineRelations() {
        // une session a un projet
        self::addRelationOneToOne('id_project','\\efrogg\\Coverage\\Storage\\CoverageProject','id_project');
    }

}