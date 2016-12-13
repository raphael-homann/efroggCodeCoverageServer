<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace Efrogg\Coverage\Storage;


class CoverageData extends StorageModel
{

    protected static $_tableName = 'cc_data';
    protected static $_primaryKey = 'id_data';
    protected static $_relations = array();

    protected static $_tableFields = array(
        'hash',
        'id_session',
        'count',
        'type',
        'detail',
        'severity',
    );


    public $id_data;
    public $hash;
    public $id_session;
    public $count;
    public $type;
    public $severity;
    public $detail;

    protected static function defineRelations() {
        // une session a un projet
        self::addRelationOneToOne('id_session',CoverageSession::class,'id_session',[],"session");
    }

}