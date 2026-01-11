<?php

declare(strict_types=1);


use CurtainCall\Exceptions\WordpressDbInstanceNotFoundException;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

if (!function_exists('ccwp_get_custom_field')) {
    /**
     * @param string $field_name
     * @param int|null $post_id
     * @return mixed
     */
    function ccwp_get_custom_field(string $field_name, ?int $post_id = null): mixed
    {
        if ($post_id) {
            return get_post_meta($post_id, $field_name, true);
        }

        $id = get_the_ID();

        if ($id === false) {
            return null;
        }

        return get_post_meta($id, $field_name, true);
    }
}

if (!function_exists('ccwp_get_wpdb')) {
    /**
     * Get the global wpdb object
     *
     * @return wpdb
     * @throws WordpressDbInstanceNotFoundException
     */
    function ccwp_get_wpdb(): wpdb
    {
        /** @var wpdb|null $wpdb */
        // @mago-ignore lint:no-global analysis:no-global
        $wpdb = $GLOBALS['wpdb'] ?? null;

        if (!$wpdb) {
            throw new WordpressDbInstanceNotFoundException();
        }

        return $wpdb;
    }
}

if (!function_exists('ccwp_plugin_path')) {
    /**
     * Return the plugin dir path
     *
     * @param string $path
     * @return string
     */
    function ccwp_plugin_path(string $path = ''): string
    {
        $dirPath = plugin_dir_path(__FILE__);

        return trim("{$dirPath}{$path}");
    }
}

if (!function_exists('ccwp_plugin_url')) {
    /**
     * Return the plugin dir url
     *
     * @param string $path
     * @return string
     */
    function ccwp_plugin_url(string $path = ''): string
    {
        $urlPath = plugin_dir_url(__FILE__);

        return trim("{$urlPath}{$path}");
    }
}

if (!function_exists('ccwp_the_content_without_gallery')) {
    /**
     * Output the post's content without the gallery shortcode
     *
     * @return void
     */
    function ccwp_the_content_without_gallery(): void
    {
        $content = get_the_content();

        /** @var string[][] $matches */
        $matches = [];
        preg_match_all('~' . get_shortcode_regex() . '~s', $content, $matches, PREG_SET_ORDER);
        if ($matches) {
            foreach ($matches as $shortcode) {
                if (count($shortcode) === 3 && $shortcode[2] === 'gallery') {
                    $pos = strpos($content, $shortcode[0]);
                    if ($pos !== false) {
                        $content = substr_replace($content, '', $pos, strlen($shortcode[0]));
                    }
                }
            }
        }

        /** @var string $content */
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);

        // @mago-ignore lint:no-unescaped-output
        echo $content;
    }
}

if (!function_exists('ccwp_the_post_gallery')) {
    /**
     * Output the post's gallery shortcode
     *
     * @return void
     */
    function ccwp_the_post_gallery(): void
    {
        /** @var string $gallery */
        $gallery = get_post_gallery();

        // @mago-ignore lint:no-unescaped-output
        echo $gallery;
    }
}
