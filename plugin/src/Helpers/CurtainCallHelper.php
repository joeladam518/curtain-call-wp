<?php

namespace CurtainCallWP\Helpers;

class CurtainCallHelper
{
    public static function strip_http($str = null)
    {
        if (empty($str)) return false;
        
        $stripped_url = preg_replace('#^https?://#', '', $str);
        return $stripped_url;
    }
}
