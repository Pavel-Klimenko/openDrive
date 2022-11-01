<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 10/07/22
 * Time: 00:18
 */

namespace App\GlobalFunctions;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class Helper extends AbstractController {

    public static function getPercentOfTotal($part, $total, $precision = false) {
        $percent = ($part / $total) * 100;
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
                    $size += $this->getDirectorySize($folderPath . '/' . $file);
                }
            }

        }

        return $size;
    }

}