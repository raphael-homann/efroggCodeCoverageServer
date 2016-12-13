<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 15:54
 */

namespace Efrogg\Coverage\Storage;

use Efrogg\Coverage\Storage\Migrations\InstallCustomData;
use Efrogg\Coverage\Storage\Migrations\InstallFiles;
use Efrogg\Coverage\Storage\Migrations\InstallLines;
use Efrogg\Coverage\Storage\Migrations\InstallProjects;
use Efrogg\Coverage\Storage\Migrations\InstallSessions;
use Efrogg\Db\Adapters\DbAdapter;
use Efrogg\Db\Migration\MigrationManager;

class CoverageStorageInstaller extends MigrationManager {
    /**
     * @var DbAdapter
     */
    protected $db;

    public function __construct(DbAdapter $db)
    {
        parent::__construct($db);
//        $this->addMigrationFolder(__DIR__."/Migrations",'Efrogg\Coverage\Storage\Migrations');
        $this->addMigration(new InstallProjects());
        $this->addMigration(new InstallSessions());
        $this->addMigration(new InstallFiles());
        $this->addMigration(new InstallLines());
        $this->addMigration(new InstallCustomData());
    }

}
