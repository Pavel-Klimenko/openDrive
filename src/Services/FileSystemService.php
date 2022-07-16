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


    /**
     * Converts bytes into human readable file size.
     *
     * @param string $bytes
     * @return string human readable file size (2,87 Мб)
     * @author Mogilev Arseny
     */
    public function FileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }



}