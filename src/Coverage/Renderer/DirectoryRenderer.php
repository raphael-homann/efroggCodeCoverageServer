<?php

namespace efrogg\Coverage\Renderer;

use Efrogg\Db\Adapters\DbAdapter;

class DirectoryRenderer {
    protected $table_name;
    /**
     * @var DbAdapter
     */
    protected $db;

    /**
     * ArborescenceRenderer constructor.
     * @param $table_name
     * @param $db
     */
    public function __construct($table_name, $db)
    {
        $this->table_name = $table_name;
        $this->db = $db;
    }


}