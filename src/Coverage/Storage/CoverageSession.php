<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 20/06/16
 * Time: 16:13
 */

namespace Efrogg\Coverage\Storage;


class CoverageSession extends StorageModel
{

    protected static $_tableName = 'cc_sessions';
    protected static $_primaryKey = 'id_session';
    protected static $_relations = array();

    protected static $_tableFields = array(
        'id_session',
        'id_project',
        'session_name',
        'started_at'
    );

    public $id_session;
    public $id_project;
    public $session_name;
    public $started_at;

    protected static function defineRelations() {
        // une session a un projet
        self::addRelationOneToOne('id_project','\\efrogg\\Coverage\\Storage\\CoverageProject','id_project');
    }

    public function getLinesDetail() {
        $total = $this->getCoverageProject()->getLineCount();
        $result = $this->db->execute("SELECT status,count(*) AS nb FROM cc_lines WHERE id_session = ? GROUP BY status ",
            array($this->id_session));
        $result = array_combine($result->fetchColumn("status"),$result->fetchColumn("nb"));
        $final = [
            "total" => $total,
            "uncovered"=> $result[-1],
            "dead_code"=> $result[-2],
            "covered"=> $result[1]
        ];
        return $final;

    }
}