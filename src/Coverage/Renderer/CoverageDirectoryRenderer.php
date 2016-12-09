<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 27/06/16
 * Time: 11:24
 */

namespace Efrogg\Coverage\Renderer;


use Efrogg\Coverage\Storage\CoverageFile;
use Efrogg\Coverage\Storage\CoverageProject;
use Efrogg\Coverage\Storage\CoverageSession;
use Twig_SimpleFilter;

class CoverageDirectoryRenderer extends DirectoryRenderer
{

    protected $id_file = null;
    protected $id_session = null;

    /** @var  \Twig_Environment */
    protected $twig;

    /** @var  CoverageFile */
    protected $cc_file;

    /** @var  string */
    protected $template='list.twig';

    /** @var  CoverageProject */
    protected $cc_project;

    /** @var  CoverageSession */
    protected $cc_session;

    /**
     * @param $id_file
     * @return $this
     * @throws \Exception
     */
    public function setIdFile($id_file)
    {
        $res = $this->db->execute('SELECT * FROM cc_files WHERE id_file = ?',
            array($id_file));
        if($res -> isValid()) {
            $this->id_file = (int)$res->fetch()['id_file'];
        } else {
            throw new \Exception("invalid id_file");
        }
        return $this;
    }

    public function getData()
    {
        $this->cc_session = CoverageSession::findOne(array("id_session"=>$this->id_session));
        $this->cc_file = CoverageFile::findOne(array("id_file"=>$this->id_file));
        $this->cc_project = CoverageProject::findOne(array("id_project"=>$this->cc_file->id_project));

        if($this->cc_file) {
            $data["breadcrumb"]=$this->getDataBreadcrumb();
            $data["files"]=$this->getDataFiles();
            if(!$this->cc_file->is_dir) {
                $data["file_details"] = $this->getDetailFile();
            }
            $data["file"]=$this->cc_file;
            $data["project"]=$this->cc_project;
            $data["session"]=$this->cc_session;
            return $data;
        } else {
            throw new \Exception("invalid id_file");
        }
    }

    private function getDataBreadcrumb()
    {
        // fil d'ariane
        $sql = 'SELECT f2.* FROM cc_files f1
      INNER JOIN cc_files f2 ON f2.id_project = f1.id_project AND f2.nleft<=f1.nleft AND f2.nright>=f1.nright
      WHERE f1.id_file=' . $this->cc_file->id_file . '
      ORDER BY f2.level_depth ASC';
        $fil = $this->db->execute($sql)->fetchAll();
        return $fil;
    }

    private function getDataFiles()
    {
        if ($this->cc_file-> is_dir) {
            // selectionne la descendance

            $sql = 'SELECT f2.* FROM cc_files f1
      INNER JOIN cc_files f2 ON f2.id_project = f1.id_project AND f2.nleft>f1.nleft AND f2.nright<f1.nright AND f2.level_depth=f1.level_depth+1
      WHERE f1.id_file=' . $this->cc_file->id_file . '
      GROUP BY f2.id_file
      ORDER BY f2.is_dir DESC,f2.path ASC';
//            var_dump(htmlentities($sql));
            $q = $this->db->execute($sql);
            $data = $q->fetchAll();
            $this -> completeCoverageData($data);
            return $data;
        }
        return array();
    }

    private function getDetailFile() {

        $lines = explode("\n",file_get_contents($this->cc_project->path.$this->cc_file->path));

        $data=array();
        foreach($lines as $n => $line) {
            $data[$n] = array("content"=>utf8_encode($line),"status"=>0);
        }
        $lines_status = $this->db->execute("SELECT * FROM cc_lines WHERE id_file = ? AND id_session=?",
                array($this->cc_file->id_file,$this->cc_session->id_session))->fetchAll();
        foreach($lines_status as $status) {
            $data[$status['line_number']-1]['status'] = $status["status"];
        }


        foreach($data as &$item) {
            $item['status_txt'] = $this -> statusToTxt($item['status']);
        }
        return $data;
    }

    private function completeCoverageData(&$data)
    {
        $indexData = array();
        foreach($data as $k => $file) {
            $indexData[$file["id_file"]] = $k;
            $data[$k]["covered"] = 0;
            $data[$k]["uncovered"] = 0;
            $data[$k]["deadcode"] = 0;

        }
//        var_dump($indexData);
        $files = implode(",",array_keys($indexData));

        $sql = "SELECT f1.id_file,f1.path,SUM(IFNULL(f2.line_count,0)) count_lines, SUM(NOT f2.is_dir) AS count_files, count(*) AS nb
         FROM cc_files f1
          INNER JOIN cc_files f2 ON f2.nleft>=f1.nleft AND f2.nright<=f1.nright
         WHERE f1.id_file IN ($files)
         GROUP BY f1.id_file
        ";
        $all = $this->db->execute($sql)->fetchAll();
//        var_dump($all);
//        var_dump($data);
        foreach($all as &$file) {
            $k = $indexData[$file["id_file"]];
            $data[$k]["total_lines"] = $file['count_lines'];
            $data[$k]["count_files"] = $file['count_files'];
//            var_dump($file);
//            var_dump($data[$k]);
//            exit;
        }
//        var_dump($all);


        $sql = "SELECT f1.id_file,f1.path
            ,SUM(IFNULL(l.status,-1)=1) AS covered
            ,SUM(IF(l.status IS NULL,IFNULL(f2.line_count,0),l.status=-1)) AS uncovered
            ,SUM(IFNULL(l.status,-1)=-2) AS deadcode
         FROM cc_files f1                                                         -- dossier en cours
          INNER JOIN cc_files f2 ON f2.nleft>=f1.nleft AND f2.nright<=f1.nright   -- fichiers / dossiers dedans
          LEFT JOIN cc_lines l ON l.id_file = f2.id_file AND l.id_session=?      -- lignes vues
         WHERE f1.id_file IN ($files)
         GROUP BY f1.id_file
        ";
//        var_dump($sql);
        $all = $this->db->execute($sql,[$this->id_session])->fetchAll();

        $real_total = 0;
        $max_lines = 0;
        $min_lines = 1000000;
//        $max_ratio = 0;
        foreach($all as $file) {
            $k = $indexData[$file["id_file"]];
            $data[$k]["covered"] = $file['covered'];
            $data[$k]["uncovered"] = $file['uncovered'];
            $data[$k]["deadcode"] = $file['deadcode'];
            $data[$k]["effective_lines"] = $data[$k]["total_lines"];// ($file['covered']+$file['uncovered']+$file['deadcode']);
            $real_total += $data[$k]["effective_lines"];
            $max_lines = max($max_lines,$data[$k]["effective_lines"]);
            $min_lines = min($min_lines,$data[$k]["effective_lines"]);
//            $max_ratio = max($max_ratio,pow($data[$k]["effective_lines"],$pow));
        }
        $log=5000/max(1,(min(2,round($max_lines/max(100,$min_lines)))));
//        var_dump($all);
        foreach($data as &$item) {
//print_r($max_lines);
//        exit;
//            $scale = $max_lines;
            $scale = $item["effective_lines"];
//            $scale = $scale;
            if($item["effective_lines"]>0) {
//                $ratio = pow($item["effective_lines"],$pow)/$max_ratio;
//                $reste = $item["effective_lines"]-$item['covered']-$item['deadcode']-$item['covered'];
                $ratio = log(($item["effective_lines"]*($log-1)/$max_lines)+1,$log);
//                $item['covered']=100;
                $item["percent_covered"] = floor(1000*($item['covered'])*1 / $scale)/10;
                $item["percent_uncovered"] = floor(1000*($item['uncovered'])*1 / $scale)/10;
                $item["percent_deadcode"] = floor(1000*($item['deadcode'])*1 / $scale)/10;
//                $item["percent_deadcode"] = floor(1000*($item['deadcode'])/ $scale)/10;
                $item["ratio"] = 30+max(0,floor(700*$ratio)/10);
            } else {
//            if($item["total_lines"]>0) {
//                $item["percent_covered"] = 100*round($item['covered'] / $item["total_lines"], 3);
//                $item["percent_uncovered"] = 100*round($item['uncovered'] / $item["total_lines"], 3);
//                $item["percent_deadcode"] = 100*round($item['deadcode'] / $item["total_lines"], 3);
//            } else {
                $item["percent_covered"] = 0;
                $item["percent_uncovered"] = 0;
                $item["percent_deadcode"] = 0;
            }
        }
    }

    private function statusToTxt($status)
    {
        if($status == 0) return "useless";
        if($status == 1) return "covered";
        if($status == -1) return "uncovered";
        if($status == -2) return "deadcode";
    }

    /**
     * @param null $id_session
     * @return CoverageDirectoryRenderer
     */
    public function setIdSession($id_session)
    {
        $this->id_session = $id_session;
        return $this;
    }

}