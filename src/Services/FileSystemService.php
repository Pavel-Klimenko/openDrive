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

    public $fileSystem;

    public CONST FILE_EXTENSIONS = [
        'img' => ['jpg', 'png', 'jpeg', 'webp'],
        'audio' => ['mp3', 'aac', 'wav', 'flac'],
        'video' => ['mp4', 'avi', 'mov', 'mpeg'],
        'documents' => ['txt', 'doc', 'doc', 'pdf'],
        'archives' => ['rar', 'zip', '7z', 'tar'],
    ];

    public CONST BASE_PATH = 'user_files';


    public function __construct() {
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


        if ($bytes == 0) return 0;


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


    public function getFileType($extension) {
        foreach (self::FILE_EXTENSIONS as $fileType => $arrFileTypes) {
            if (in_array($extension, $arrFileTypes)) {
                return $fileType;
            }
        }

        return 'OTHER';
    }


    public function getFileTypeExtensions($type): array
    {
        switch ($type) {
            case "img":
                $arrFileExtensions = self::FILE_EXTENSIONS['img'];
                break;
            case "audio":
                $arrFileExtensions = self::FILE_EXTENSIONS['audio'];
                break;
            case "video":
                $arrFileExtensions = self::FILE_EXTENSIONS['video'];
                break;
            case "documents":
                $arrFileExtensions = self::FILE_EXTENSIONS['documents'];
                break;
            case "archives":
                $arrFileExtensions = self::FILE_EXTENSIONS['archives'];
                break;
            default:
                $arrFileExtensions = [];
        }

        return $arrFileExtensions;
    }


    public function getFileStyles($type): array
    {
        switch ($type) {
            case "documents":
                $textColorClass = 'text-success';
                $iconClass = 'bx bxs-file-doc';
                break;
            case "img":
                $textColorClass = 'text-primary';
                $iconClass = 'bx bx-image';
                break;
            case "video":
                $textColorClass = 'text-danger';
                $iconClass = 'bx bx-video';
                break;
            case "audio":
                $textColorClass = 'text-primary';
                $iconClass = 'bx bx-video';
                break;
            case "archives":
                $textColorClass = 'text-warning';
                $iconClass = 'bx bx-image';
                break;
            default:
                $arrFileExtensions = [];
        }

        return [
            'COLOR_CLASS' => $textColorClass,
            'ICON_CLASS' => $iconClass
        ];
    }



}