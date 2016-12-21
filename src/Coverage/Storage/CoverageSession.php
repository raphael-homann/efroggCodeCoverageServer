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
        self::addRelationOneToOne('id_project','\\Efrogg\\Coverage\\Storage\\CoverageProject','id_project');
        self::addRelationOneToMany('id_session','\\Efrogg\\Coverage\\Storage\\CoverageData','id_session','custom_data');
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

    public function getCustomBySeverityAndType() {
        $final = [
            "errors" => [],
            "deprecated"=>[],
            "mysql"=>[],
        ];
        foreach($this->db->execute("SELECT severity,count(*) AS distinct_nb, SUM(count) AS nb FROM cc_data WHERE id_session = ? AND type='error' GROUP BY severity ",
            array($this->id_session))->fetchAll() as $error) {
            $final["errors"][$this->convertSeverity($error["severity"])] = $error["nb"];
        }
        foreach($this->db->execute("SELECT severity,count(*) AS distinct_nb, SUM(count) AS nb FROM cc_data WHERE id_session = ? AND type='deprecated' GROUP BY severity ",
            array($this->id_session))->fetchAll() as $error) {
            $final["deprecated"][$this->convertSeverity($error["severity"])] = $error["nb"];
        }
        foreach($this->db->execute("SELECT severity,count(*) AS distinct_nb, SUM(count) AS nb FROM cc_data WHERE id_session = ? AND type='mysql' GROUP BY severity ",
            array($this->id_session))->fetchAll() as $error) {
            $final["mysql"][$this->convertSeverity($error["severity"])] = $error["nb"];
        }
        return $final;

    }

    private function convertSeverity($severity)
    {
        switch($severity) {
            case 2:
                return "warning";
            case 3:
                return "danger";
            case 4:
                return "error";
//            case 1:
            default:
                return "notice";
        }
    }
}