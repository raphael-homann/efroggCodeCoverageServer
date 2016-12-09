<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 21/06/16
 * Time: 10:11
 */

namespace Efrogg\Coverage\Storage\Migrations;


use Efrogg\Db\Migration\Migration;

class InstallFiles extends Migration
{
    public function up()
    {
        $this->db->execute("CREATE TABLE `cc_files` (
          `id_file` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_project` int(10) UNSIGNED NOT NULL,
          `path` VARCHAR(512) NOT NULL,
          `path_hash` INT(11) UNSIGNED NOT NULL,
          `line_count` INT(11) DEFAULT NULL,
          `is_dir` TINYINT(1) NOT NULL DEFAULT 0,
          `nleft` INT(11) DEFAULT NULL,
          `nright` INT(11) DEFAULT NULL,
          `level_depth` SMALLINT(6) DEFAULT NULL,
          PRIMARY KEY (id_file),
          INDEX (path_hash),
          INDEX (path),
          INDEX (nleft),
          INDEX (nright),
          INDEX (level_depth),
          INDEX (id_project)
        ) ENGINE=InnoDB");
    }

    public function down()
    {
        $this->db->execute('DROP TABLE IF EXISTS  cc_files');
    }
}