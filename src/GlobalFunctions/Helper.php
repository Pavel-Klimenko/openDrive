<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 10/07/22
 * Time: 00:18
 */

namespace App\GlobalFunctions;


class Helper {

    public static function prent($var) {

        //$bt = debug_backtrace();
        //$bt = $bt[0];
        //$file = $bt["file"];
        //$line = $bt["line"];

        //echo "<div style='padding:3px 5px; background:#99CCFF; font-weight:bold;'>File: $file [$line]</div>";
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }



}