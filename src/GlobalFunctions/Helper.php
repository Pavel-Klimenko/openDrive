<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 10/07/22
 * Time: 00:18
 */

namespace App\GlobalFunctions;


class Helper {

    public static function getPercentOfTotal($part, $total, $precision = false) {
        $percent = ($part / $total) * 100;
        if ($precision) $percent = round($percent, $precision);
        return $percent;
    }

}