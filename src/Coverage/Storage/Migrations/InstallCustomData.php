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
          `detail` TEXT NOT NULL,
          PRIMARY KEY (id_data),
          INDEX (id_session),
          INDEX (hash),
          INDEX (`count`),
          INDEX (`type`),
          INDEX (severity)
        ) ENGINE=InnoDB");

        $this->table("cc_callback")->create("
        CREATE TABLE `cc_callback` (
          `id_callback` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_data` int(10) UNSIGNED NOT NULL,
          `id_session` int(10) UNSIGNED NOT NULL,
          `hash` VARCHAR(32) NOT NULL,
          `count` int(11) unsigned NOT NULL,
          `detail` TEXT NOT NULL,
          PRIMARY KEY (id_callback),
          INDEX (id_session),
          INDEX (hash),
          INDEX (`count`),
          INDEX (`id_data`),
          INDEX (`count`)
        ) ENGINE=InnoDB");

        $this->table("cc_url")->create("
        CREATE TABLE `cc_url` (
          `id_url` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `id_session` int(10) UNSIGNED NOT NULL,
          `url` VARCHAR(255) NOT NULL,
          PRIMARY KEY (id_url),
          INDEX (id_session),
          INDEX (`url`)
        ) ENGINE=InnoDB");

        $this->table("cc_callback_url")->create("
        CREATE TABLE `cc_callback_url` (
          `id_url_callback` int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `id_url` int(10) UNSIGNED NOT NULL,
          `id_callback` int(10) UNSIGNED NOT NULL,
          `id_session` int(10) UNSIGNED NOT NULL,
          UNIQUE (id_callback,id_url),
          INDEX (id_session)
        ) ENGINE=InnoDB");

    }

    public function down()
    {
        $this->table("cc_data")->delete();
        $this->table("cc_callback")->delete();
        $this->table("cc_url")->delete();
        $this->table("cc_callback_url")->delete();
    }
}