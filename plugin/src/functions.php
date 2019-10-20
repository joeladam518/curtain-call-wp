<?php

if (!function_exists('ccwp_assets_url')) {
    /**
     * Get the url path of CurtainCallWP's minified assets
     * @return string
     */
    function ccwp_assets_url(): string
    {
        return ccwp_plugin_url('dist/');
    }
}

if (!function_exists('ccwp_assets_path')) {
    /**
     * Get the dir path of CurtainCallWP's minified assets
     * @return string
     */
    function ccwp_assets_path(): string
    {
        return ccwp_plugin_path('dist/');
    }
}

if (!function_exists('ccwp_plugin_url')) {
    /**
     * return the plugin dir url
     * @param string $path
     * @return string
     */
    function ccwp_plugin_url(string $path = ''): string
    {
        $url_path = plugin_dir_url(dirname(__FILE__));
        
        if ($path !== '') {
            $url_path = $url_path . $path;
        }
        
        return $url_path;
    }
}

if (!function_exists('ccwp_plugin_path')) {
    /**
     * return the plugin dir path
     * @param string $path
     * @return string
     */
    function ccwp_plugin_path(string $path = ''): string
    {
        $dir_path = plugin_dir_path(dirname(__FILE__));
        
        if ($path !== '') {
            $dir_path = $dir_path . $path;
        }
        
        return $dir_path;
    }
}

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
