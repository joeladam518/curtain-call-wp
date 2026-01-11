<?php

declare(strict_types=1);

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

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
