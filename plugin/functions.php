<?php
if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

if (!function_exists('ccwpGetPostType')) {
    /**
     * Return the post-type from the current screen or $_GET['post_type']
     *
     * @param WP_Screen|null $screen = null
     * @return string|null
     */
    function ccwpGetPostType(?WP_Screen $screen = null): string|null
    {
        $screen ??= get_current_screen();
        $postType = $screen?->post_type;

        if (!$postType && isset($_GET['post_type'])) {
            $postType = sanitize_text_field($_GET['post_type']);
        }

        if (!$postType && isset($_GET['post'])) {
            $postType = get_post_type((int)$_GET['post']);
        }

        return $postType ?: null;
    }
}

if (!function_exists('ccwpPluginPath')) {
    /**
     * Return the plugin dir path
     *
     * @param string $path
     * @return string
     */
    function ccwpPluginPath(string $path = ''): string
    {
        $dirPath = plugin_dir_path(__FILE__);

        return trim("{$dirPath}{$path}");
    }
}

if (!function_exists('ccwpPluginUrl')) {
    /**
     * Return the plugin dir url
     *
     * @param string $path
     * @return string
     */
    function ccwpPluginUrl(string $path = ''): string
    {
        $urlPath = plugin_dir_url(__FILE__);

        return trim("{$urlPath}{$path}");
    }
}

if (!function_exists('ccwpStripShortCodeGallery')) {
    /**
     * @param string $content
     * @return string
     */
    function ccwpStripShortCodeGallery(string $content): string
    {
        preg_match_all('~'.get_shortcode_regex().'~s', $content, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
            foreach ($matches as $shortcode) {
                if ('gallery' === $shortcode[2]) {
                    $pos = strpos($content, $shortcode[0]);
                    if ($pos !== false) {
                        return substr_replace($content, '', $pos, strlen($shortcode[0]));
                    }
                }
            }
        }

        return $content;
    }
}

if (!function_exists('getCustomField')) {
    /**
     * @param string   $field_name
     * @param int|null $post_id
     * @return mixed
     */
    function getCustomField(string $field_name, ?int $post_id = null)
    {
        if ($post_id) {
            return get_post_meta($post_id, $field_name, true);
        }

        return get_post_meta(get_the_ID(), $field_name, true);
    }
}

if (defined('CCWP_DEBUG') && CCWP_DEBUG) {
    if (!function_exists('fnln')) {
        /**
         * Return the file name and line number from where this function was called
         * in a pretty format. Mainly for log messaging.
         *
         * @param bool $return
         * @return string|void
         */
        function fnln(bool $return = false)
        {
            $backtrace = debug_backtrace()[0];
            $out = basename($backtrace['file']) . ' (#' . $backtrace['line'] . ') ';

            if ($return) {
                return $out;
            }

            echo $out;
        }
    }

    if (!function_exists("pr")) {
        /**
         * Debug print:
         * print_r() a data structure to the output buffer inside pre tags.
         *
         * @param  mixed   $obj The data structure to be dumped... (string, array, stdClass etc.)
         * @param  boolean $exit If true, exit after outputting.
         * @return void
         */
        function pr($obj, bool $exit = false): void
        {
            ob_start();
            print_r($obj);
            $out = htmlspecialchars(ob_get_contents());
            ob_end_clean();
            $out = '<pre>' . $out . '</pre>' . PHP_EOL;
            echo $out;
            if ($exit) {
                exit;
            }
        }
    }

    if (!function_exists("dmp")) {
        /**
         * Debug dump:
         * var_dump() a data structure to the output buffer inside pre tags.
         *
         * @param  mixed   $obj The data structure to be dumped... (string, array, stdClass etc.)
         * @param  boolean $exit $exit If true, exit after outputting.
         * @return void
         */
        function dmp($obj, bool $exit = false): void
        {
            ob_start();
            var_dump($obj);
            $out = ob_get_contents();
            ob_end_clean();
            $out = '<pre>' . $out . '</pre>' . PHP_EOL;
            echo $out;
            if ($exit) {
                exit;
            }
        }
    }
}
