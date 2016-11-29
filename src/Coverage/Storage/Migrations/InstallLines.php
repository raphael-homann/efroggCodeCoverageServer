<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 21/06/16
 * Time: 10:11
 */

namespace efrogg\Coverage\Storage\Migrations;


use Efrogg\Db\Migration\Migration;

class InstallLines extends Migration
{
    public function up()
    {
        $this->db->execute("CREATE TABLE `cc_lines` (
          `id_line` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_file` int(10) UNSIGNED NOT NULL,
          `id_session` int(10) UNSIGNED NOT NULL,
          `line_number` int(10) NOT NULL,
          `status` TINYINT(1) NOT NULL,
          PRIMARY KEY (id_line),
          INDEX (id_file),
          INDEX (id_session),
          INDEX (line_number),
          UNIQUE session_line (id_file,id_session,line_number)
        ) ENGINE=InnoDB");
    }

    public function down()
    {
        $this->db->execute('DROP TABLE IF EXISTS `cc_lines`');
    }
}