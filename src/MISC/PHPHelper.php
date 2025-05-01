<?php

namespace App\MISC;

class PHPHelper {

    public static function debug_to_console($msg) {
        echo "<script>console.log('Debug Objects: " . $msg . "' );</script>";
    }
}