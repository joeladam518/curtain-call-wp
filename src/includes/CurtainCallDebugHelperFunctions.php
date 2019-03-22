<?php

if (!function_exists("debug_dump"))
{
    function debug_dump(&$obj, $exit = false)
    {
        ob_start();
        var_dump($obj);
        $out = ob_get_contents();
        ob_end_clean();
        $out = '<pre>' . $out . '</pre>';
        echo $out;
        if ($exit) { exit; }
    }
}

if (!function_exists("debug_print"))
{
    function debug_print($obj, $exit = false)
    {
        ob_start();
        print_r($obj);
        $out = htmlspecialchars(ob_get_contents());
        ob_end_clean();
        $out = '<pre>' . $out . '</pre>';
        echo $out;
        if ($exit) { exit; }
    }
}

if (!function_exists("dump"))
{
    function dump($obj, $exit = false)
    {
        debug_dump($obj, $exit);
    }
}

if (!function_exists("pr"))
{
    function pr($obj, $exit = false)
    {
        debug_print($obj, $exit);
    }
} 
   