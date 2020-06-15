<?php

namespace CurtainCallWP\Helpers;

use Carbon\CarbonImmutable as Carbon;
use Carbon\Exceptions\InvalidFormatException;
use CurtainCallWP\PostTypes\CurtainCallJoin;
use WP_Post;
use CurtainCallWP\PostTypes\CurtainCallPost;
use CurtainCallWP\PostTypes\Production;
use CurtainCallWP\PostTypes\CastAndCrew;
use Throwable;

class CurtainCallHelper
{
    /**
     * @return array
     */
    public static function getAlphabet(): array
    {
        return ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    }
    
    /**
     * @param array $data
     * @return array|CurtainCallPost[]
     * @throws Throwable
     */
    public static function convertToCurtainCallPosts(array $data): array
    {
        $posts = [];
        $join_fields = CurtainCallJoin::getJoinFields(true);
        
        foreach ($data as $post_data) {
            $wp_post_data = [];
            $ccwp_join_data = [];
            // Separate CCWP Join data from WP_Post data
            foreach ($post_data as $key => $value) {
                if (in_array($key, $join_fields)) {
                    $ccwp_join_data[$key] = $value;
                } else {
                    $wp_post_data[$key] = $value;
                }
            }
            
            // Convert to WP_Post
            $wp_post_data = (object)$wp_post_data;
            $wp_post = new WP_Post($wp_post_data);
            
            $ccwp_post = null;
            // Convert to CurtainCallPost
            switch ($wp_post->post_type) {
                case Production::POST_TYPE:
                    /** @var Production $ccwp_post */
                    $ccwp_post = Production::make($wp_post);
                    break;
                case CastAndCrew::POST_TYPE:
                    /** @var CastAndCrew $ccwp_post */
                    $ccwp_post = CastAndCrew::make($wp_post);
                    break;
            }

            // Add to posts array
            if ($ccwp_post !== null) {
                $ccwp_post->setCurtainCallPostJoin(new CurtainCallJoin($ccwp_join_data));
                $posts[] = $ccwp_post;
            }
        }

        return $posts;
    }
    
    /**
     * @param string|null $date_string
     * @return Carbon|null
     */
    public static function toCarbon(?string $date_string): ?Carbon
    {
        if (empty($date_string)) {
            return null;
        }
    
        try {
            $carbon_date = Carbon::parse($date_string);
        } catch (InvalidFormatException $e) {
            return null;
        }
        
        return $carbon_date;
    }
    
    /**
     * @param string|null $from
     * @param string      $to
     * @param null        $default
     * @return string|null
     */
    public static function convertDate(?string $from, string $to = 'Y-m-d', $default = null): ?string
    {
        $date = static::toCarbon($from);
        
        return $date ? $date->format($to) : $default;
    }
}