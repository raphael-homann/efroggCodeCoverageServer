<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 13/12/16
 * Time: 03:27
 */

namespace Efrogg\Coverage\Storage\Migrations;


use Efrogg\Db\Migration\Migration;

class InstallCustomData extends Migration
{
    public function up()
    {
        $this->table("cc_data")->create("
        CREATE TABLE `cc_data` (
          `id_data` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_session` int(10) UNSIGNED NOT NULL,
          `hash` VARCHAR(32) NOT NULL,
          `count` int(11) unsigned NOT NULL,
          `type` VARCHAR(255) NOT NULL,
          `severity` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
          `detail` TEXT NOT NULL DEFAULT '',
          PRIMARY KEY (id_data),
          INDEX (id_session),
          INDEX (hash),
          INDEX (`count`),
          INDEX (`type`),
          INDEX (severity)
        ) ENGINE=InnoDB");
    }

    public function down()
    {
        $this->table("cc_data")->delete();
    }
}