<?php

namespace CurtainCallWP\Helpers;

use Carbon\CarbonImmutable as Carbon;

class CurtainCallHelpers 
{
    public static function strip_http($str = null)
    {
        if (empty($str)) return false;
        
        $stripped_url = preg_replace('#^https?://#', '', $str);
        return $stripped_url;
    }
}
