<?php

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

if (defined('CCWP_DEBUG') && CCWP_DEBUG === true) {
    if (!function_exists('fnln')) {
        /**
         * Return the file name and line number from where this function was called
         * in a pretty format. Mainly for log messaging.
         *
         * @return string
         */
        function fnln(): string
        {
            $backtrace = debug_backtrace()[0];
            return basename($backtrace['file']) . ' (#' . $backtrace['line'] . ') ';
        }
    }

    if (!function_exists('ccwp_print_fnln')) {
        /**
         * Echo the result of ccwp_fnln()
         *
         * @return void
         */
        function print_fnln(): void
        {
            $backtrace = debug_backtrace()[0];
            echo basename($backtrace['file']) . ' (#' . $backtrace['line'] . ') ';
        }
    }
}
