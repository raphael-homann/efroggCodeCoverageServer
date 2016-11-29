<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 24/06/16
 * Time: 12:12
 */

namespace efrogg\Coverage\Storage;


use efrogg\Coverage\Controllers\FolderIterator;
use Efrogg\Db\Tools\DbTools;

class CoverageProjectExplorer
{
    protected $exclusions = [];
    private $idProject;
    private $rootPath;

    protected $addedFiles = 0;
    protected $updatedFiles = 0;


    /**
     * CoverageProjectExplorer constructor.
     */
    public function __construct($idProject, $rootPath)
    {
        $this->idProject = $idProject;
        $this->rootPath = rtrim($rootPath,"/");
    }

    public function discover()
    {
        $this->addedFiles=0;
        $this->updatedFiles=0;

        $this->addFile($this->rootPath);
        $it = new \RecursiveIteratorIterator(new FolderIterator (new \RecursiveDirectoryIterator($this->rootPath)),
            \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $filePath) {
            $this->addFile($filePath);
        }
    }

    private function addFile($filePath)
    {
        if(!$this->accept($filePath)) return;

        $relativePath = $filePath;
        if (strpos($filePath, $this->rootPath) === 0) {
            $relativePath = substr($filePath, strlen($this->rootPath));
        }
        $isDir = is_dir($filePath);
        $hash = DbTools::getHash32($relativePath);

        $file = CoverageFile::findOrCreate(array(
            "id_project" => $this->idProject,
            "path_hash" => $hash,
            "path" => $relativePath
        ));
        if ($isDir) {
            $file->is_dir = 1;
        } else {
            $file->line_count = count(explode("\n", file_get_contents($filePath)));
        }
        if ($file->isNew()) {
            $this->addedFiles++;
        } else {
            $this->updatedFiles++;
        }
//        echo "<br>$filePath";
        $file->save();

    }

    /**
     * @return int
     */
    public function getAddedFiles()
    {
        return $this->addedFiles;
    }

    /**
     * @return int
     */
    public function getUpdatedFiles()
    {
        return $this->updatedFiles;
    }

    public function addExclusions($exclusions)
    {
        if(!is_array($exclusions)) $exclusions=array($exclusions);
        foreach($exclusions as $exclusion) {
            $this->exclusions[]=$exclusion;
        }
    }

    private function accept($filePath)
    {
        foreach($this->exclusions as $exclusion) {
            if(preg_match($exclusion,$filePath)) return false;
        }
        return true;
    }
}