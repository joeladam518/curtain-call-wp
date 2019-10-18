<?php

/**
 * Return the file name and line number from where this function was called
 * in a pretty format. Mainly for log messaging.
 *
 * @return String
 */
if (!function_exists('fnln')) {
    function fnln()
    {
        $backtrace = debug_backtrace()[0];
        return basename($backtrace['file']) . ' (#' . $backtrace['line'] . ') ';
    }
}

/**
 * Debug print:
 * print_r() a data structure to the output buffer inside of pre tags.
 *
 * @param  mixed   &$obj The data structure to be dumped... (string, array, stdClass etc.)
 * @param  boolean $exit If true, exit after outputting.
 * @return void
 */
if (!function_exists("pr")) {
    function pr(&$obj, $exit = false)
    {
        ob_start();
        print_r($obj);
        $out = htmlspecialchars(ob_get_contents());
        ob_end_clean();
        $out = '<pre>' . $out . '</pre>' . PHP_EOL;
        echo $out;
        if ($exit) { exit; }
    }
}

/**
 * Debug dump:
 * var_dump() a data structure to the output buffer inside of pre tags.
 *
 * @param  mixed   &$obj The data structure to be dumped... (string, array, stdClass etc.)
 * @param  boolean $exit $exit If true, exit after outputting.
 * @return void
 */
if (!function_exists("dmp")) {
    function dmp(&$obj, $exit = false)
    {
        ob_start();
        var_dump($obj);
        $out = ob_get_contents();
        ob_end_clean();
        $out = '<pre>' . $out . '</pre>' . PHP_EOL;
        echo $out;
        if ($exit) { exit; }
    }
}
