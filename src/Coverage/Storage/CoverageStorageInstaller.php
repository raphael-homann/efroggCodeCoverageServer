<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 15:54
 */

namespace efrogg\Coverage\Storage;

use efrogg\Coverage\Storage\Migrations\InstallFiles;
use efrogg\Coverage\Storage\Migrations\InstallLines;
use efrogg\Coverage\Storage\Migrations\InstallProjects;
use efrogg\Coverage\Storage\Migrations\InstallSessions;
use efrogg\Db\Adapters\DbAdapter;
use efrogg\Db\Migration\MigrationManager;

class CoverageStorageInstaller extends MigrationManager {
    /**
     * @var DbAdapter
     */
    protected $db;

    public function __construct(DbAdapter $db)
    {
        parent::__construct($db);
        $this->addMigration(new InstallProjects());
        $this->addMigration(new InstallSessions());
        $this->addMigration(new InstallFiles());
        $this->addMigration(new InstallLines());
    }

}
