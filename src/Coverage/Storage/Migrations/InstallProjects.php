<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 21/06/16
 * Time: 10:11
 */

namespace efrogg\Coverage\Storage\Migrations;


use Efrogg\Db\Migration\Migration;

class InstallProjects extends Migration
{
    public function up()
    {
        $this->db->execute("CREATE TABLE `cc_projects` (
          `id_project` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` varchar(50) NOT NULL,
          `path` varchar(512) DEFAULT NULL,
          PRIMARY KEY (id_project),
          UNIQUE (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
    }

    public function down()
    {
        $this->db->execute('DROP TABLE IF EXISTS  cc_projects');
    }
}