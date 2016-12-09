<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace Efrogg\Coverage\Storage;


class CoverageFile extends StorageModel
{

    protected static $_tableName = 'cc_files';
    protected static $_primaryKey = 'id_file';
    protected static $_relations = array();

    protected static $_tableFields = array(
        'id_file',
        'id_project',
        'path',
        'path_hash',
        'line_count',
        'is_dir',
        'nleft',
        'nright',
        'level_depth',
    );


    public $id_file;
    public $id_project;
    public $path;
    public $path_hash;
    public $line_count;
    public $is_dir = 0;
    public $nleft;
    public $nright;
    public $level_depth;

    protected static function defineRelations() {
        // une session a un projet
        self::addRelationOneToOne('id_project','\\efrogg\\Coverage\\Storage\\CoverageProject','id_project');
    }



    protected $project;
    protected $file_name;

    public function addLines($lines)
    {
    }
}