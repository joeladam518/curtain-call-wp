<?php

declare(strict_types=1);

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

/**
 * @var string $wp_nonce
 * @var WP_Post $post
 * @var array $metabox
 * @var string $name
 * @var string $date_start
 * @var string $date_end
 * @var string $show_times
 * @var string $ticket_url
 * @var string $venue
 */

// @mago-ignore lint:no-unescaped-output
echo $wp_nonce;
?>
<div id="ccwp-production-details-react-root"></div>
