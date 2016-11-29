<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 21/06/16
 * Time: 15:15
 */

namespace efrogg\Coverage\Storage;


use Efrogg\Db\Adapters\DbAdapter;
use PicORM\Model;
use Silex\Application;

class StorageModel extends Model
{


    /** @var  Application */
    protected static $app;

    /** @var  DbAdapter */
    protected $db;

    /**
     * StorageModel constructor.
     */
    public function __construct()
    {
        $this->setDb(self::$app["db"]);
    }


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

    public function hydrate($data, $strictLoad = true)
    {
        parent::hydrate($data, $strictLoad);
        $this->setDb(self::$app["db"]);
    }


    public static function setApp(Application $app) {
        self::$app = $app;
    }

}