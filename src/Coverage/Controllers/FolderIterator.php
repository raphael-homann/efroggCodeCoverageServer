<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 23/06/16
 * Time: 17:00
 */

namespace efrogg\Coverage\Controllers;


class FolderIterator extends \RecursiveFilterIterator {
    public static $refuse = array(
        'vendor',
        '..',
        '.',
        'html_imgs',
        '.git'
    );

    public static $extensions = array(
        'php'
    );

    public static function acceptFile(\SplFileInfo $file) {
        if($file->isDir() || in_array(
                $file->getExtension(),
                self::$extensions
            )) {
            // extension autorisée
            //
            return !in_array(
                $file->getFilename(),
                self::$refuse,
                true
            );
        }
        return false;
    }


    public function accept() {
        return self::acceptFile($this->current());
    }

}