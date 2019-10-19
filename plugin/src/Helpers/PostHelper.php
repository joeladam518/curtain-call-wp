<?php

namespace CurtainCallWP\Helpers;

class PostHelper
{
    public static function get_custom_field($field_name = null, $post_id = null)
    {
        if (!empty($field_name)) {
            if (!empty($post_id)) {
                return get_post_meta($post_id, $field_name, true);
            } else {
                return get_post_meta(get_the_ID(), $field_name, true);
            }
        }
        
        return false;
    }
    
    public static function strip_http($str = null)
    {
        if (empty($str)) return false;
        
        $stripped_url = preg_replace('#^https?://#', '', $str);
        return $stripped_url;
    }
}