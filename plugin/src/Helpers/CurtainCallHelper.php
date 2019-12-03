<?php

namespace CurtainCallWP\Helpers;

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
}