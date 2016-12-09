<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 15:54
 */

namespace Efrogg\Coverage\Storage;

use Efrogg\Db\Adapters\DbAdapter;
use Efrogg\Db\Migration\AutoInstallMigration;
use Efrogg\Db\Migration\MigrationManager;

class CoverageStorage {
    /**
     * @var DbAdapter
     */
    protected $db;

    public function __construct(DbAdapter $db)
    {
        $this->db = $db;
    }


    /**
     * @return int : nombre de migration jouées / -1 en cas d'erreur
     */
    public function install() {
        $migrationManager = new MigrationManager($this->db);
        $migrationManager->addMigration(new AutoInstallMigration());
        return $migrationManager->up();
    }

    /**
     * @return int : nombre de migration jouées / -1 en cas d'erreur
     */
    public function rollback() {
        $migrationManager = new MigrationManager($this->db);
        return $migrationManager->down();
    }
}
