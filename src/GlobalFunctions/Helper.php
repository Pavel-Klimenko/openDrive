<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 10/07/22
 * Time: 00:18
 */

namespace App\GlobalFunctions;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class Helper extends AbstractController {

    public static function getPercentOfTotal($part, $total, $precision = false) {

        if ($total != 0) {
            $percent = ($part / $total) * 100;
        } else {
            $percent = 0;
        }

        if ($precision) $percent = round($percent, $precision);
        return $percent;
    }

    public static function getDirectorySize(string $folderPath):int
    {
        $files = scandir($folderPath);
        unset($files[0], $files[1]);
        $size = 0;
        foreach ($files as $file) {
            if (file_exists($folderPath . '/' . $file)) {
                $size += filesize($folderPath . '/' . $file);
                if (is_dir($folderPath . '/' . $file)) {
                    $size += self::getDirectorySize($folderPath . '/' . $file);
                }
            }

        }

        return $size;
    }

    public static function getFileStoragePath($path) {
        if (str_contains($path, '-')) {
            $arLink = explode('-', $path);
            $storagePath = $_SERVER['DOCUMENT_ROOT'] . '/storage/' . implode('/', $arLink);
        } else {
            $storagePath = $_SERVER['DOCUMENT_ROOT'] . '/storage/' . $path;
        }

        return $storagePath;
    }

}