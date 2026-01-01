<?php

declare(strict_types=1);

use CurtainCall\Models\CastAndCrew;

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die();
}

/**
 * @var string $wp_nonce
 * @var WP_Post $post
 * @var array $metabox
 * @var array $options
 * @var array|CastAndCrew[] $cast_members
 * @var array|CastAndCrew[] $crew_members
 */

// @mago-ignore lint:no-unescaped-output
echo $wp_nonce;
?>
<div id="ccwp-production-cast-crew-react-root"></div>
