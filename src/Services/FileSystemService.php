<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 10/07/22
 * Time: 00:18
 */

namespace App\Services;

use App\Services;
use phpDocumentor\Reflection\Type;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Path;


class FileSystemService {

    public $helper;
    public $fileSystem;


    public function __construct(Services\HelperService $helper) {
        $this->helper = $helper;
        $this->fileSystem = new Filesystem();
    }


    public function createFolder(string $folderPath) {
        try {
            $this->fileSystem->mkdir($_SERVER['DOCUMENT_ROOT'] . $folderPath);

        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }
    }



    public function move($origin, $target, $type = false) {

        $origin = $_SERVER['DOCUMENT_ROOT'] . $origin;
        $target = $_SERVER['DOCUMENT_ROOT'] . $target;

        //$this->helper->prent($target);


        if ($this->fileSystem->exists($origin)) {

            if (!$type) $type = (is_dir($origin)) ? 'folder' : 'file';


            if ($type == 'file') {
                $this->fileSystem->copy($origin, $target, true);
            } elseif ($type == 'folder') {
                $this->fileSystem->mirror($origin, $target);
            }

            if ($this->fileSystem->exists($target)) $this->fileSystem->remove($origin);

        }

    }



}