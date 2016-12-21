<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace Efrogg\Coverage\Storage;


class CoverageCallbackUrl extends StorageModel
{

    protected static $_tableName = 'cc_callback_url';
    protected static $_primaryKey = 'id_url_callback';
    protected static $_relations = array();

    protected static $_tableFields = array(
        'id_url',
        'id_callback',
        'id_session',
    );

    public $id_url_callback;
    public $id_url;
    public $id_callback;
    public $id_session;

    protected static function defineRelations()
    {
        // une session a un projet
        self::addRelationOneToOne('id_url',CoverageUrl::class,'id_url');
    }

}