<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 21/06/16
 * Time: 15:15
 */

namespace efrogg\Coverage\Storage;


use efrogg\Db\Adapters\DbAdapter;
use PicORM\Model;

class StorageModel extends Model
{

    /** @var  DbAdapter */
    protected $db;

    public static function findOrCreate($where=array(),$order=array()) {
        $found = self::findOne($where,$order);
        if(is_null($found)) {
            $found = new static();
            foreach($where as $k => $v) {
                if(property_exists($found,$k)) {
                    $found->{$k} = $v;
                }
            }
        }
        return $found;
    }

    public function setDb(DbAdapter $db) {
        $this->db = $db;
    }

}