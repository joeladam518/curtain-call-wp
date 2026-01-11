<?php

declare(strict_types=1);

use CurtainCall\Models\Production;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

/**
 * @var string $wp_nonce
 * @var WP_Post $post
 * @var array $metabox
 * @var Production|null $production
 */

// @mago-ignore lint:no-unescaped-output
echo $wp_nonce;
?>
<div id="ccwp-production-details-react-root"></div>
