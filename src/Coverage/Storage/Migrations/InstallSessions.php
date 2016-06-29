<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 21/06/16
 * Time: 10:11
 */

namespace efrogg\Coverage\Storage\Migrations;


use efrogg\Db\Migration\Migration;

class InstallSessions extends Migration
{
    public function up()
    {
        $this->db->execute("CREATE TABLE `cc_sessions` (
          `id_session` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_project` int(10) UNSIGNED NOT NULL,
          `session_name` VARCHAR(255) NOT NULL,
          `started_at` DATETIME NOT NULL,
          PRIMARY KEY (id_session),
          INDEX (id_project),
          UNIQUE (session_name)
        ) ENGINE=InnoDB");
    }

    public function down()
    {
        $this->db->execute('DROP TABLE IF EXISTS  cc_sessions');
    }
}