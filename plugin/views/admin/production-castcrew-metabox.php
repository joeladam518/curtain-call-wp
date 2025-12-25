<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;
/**
 * @var string $wp_nonce
 * @var WP_Post $post
 * @var array $metabox
 * @var array $all_cast_crew_names
 * @var array|CastAndCrew[] $cast_members
 * @var array|CastAndCrew[] $crew_members
 */
use CurtainCall\Models\CastAndCrew;

echo $wp_nonce;
?>
<div id="ccwp-production-cast-crew-react-root"></div>
