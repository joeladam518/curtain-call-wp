<?php

if (defined('CCWP_DEBUG') && CCWP_DEBUG) {
    if (!function_exists('fnln')) {
        /**
         * Return the file name and line number from where this function was called
         * in a pretty format. Mainly for log messaging.
         *
         * @return string
         */
        function ccwp_fnln(): string
        {
            $backtrace = debug_backtrace()[0];
            return basename($backtrace['file']) . ' (#' . $backtrace['line'] . ') ';
        }
    }
    
    
    if (!function_exists("pr")) {
        /**
         * Debug print:
         * print_r() a data structure to the output buffer inside of pre tags.
         *
         * @param  mixed   &$obj The data structure to be dumped... (string, array, stdClass etc.)
         * @param  boolean $exit If true, exit after outputting.
         * @return void
         */
        function pr(&$obj, $exit = false): void
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
    
    
    if (!function_exists("dmp")) {
        /**
         * Debug dump:
         * var_dump() a data structure to the output buffer inside of pre tags.
         *
         * @param  mixed   &$obj The data structure to be dumped... (string, array, stdClass etc.)
         * @param  boolean $exit $exit If true, exit after outputting.
         * @return void
         */
        function dmp(&$obj, $exit = false): void
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
}
