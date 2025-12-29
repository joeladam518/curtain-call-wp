<?php

declare(strict_types=1);

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die();
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
<div class="ccwp-form-group">
    <label for="ccwp_production_name">Production Name</label>
    <input type="text" id="ccwp_production_name" name="ccwp_production_name" value="<?php echo esc_html($name); ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_date_start"><strong>Production Dates - Opened*</strong></label>
    <input
        type="text"
        class="ccwp_datepicker_input"
        id="ccwp_date_start"
        name="ccwp_date_start"
        value="<?php echo esc_html($date_start); ?>"
        autocomplete="off"
    >
</div>

<div class="ccwp-form-group">
    <label for="ccwp_date_end"><strong>Production Dates - Closed*</strong></label>
    <input
        type="text"
        class="ccwp_datepicker_input"
        id="ccwp_date_end"
        name="ccwp_date_end"
        value="<?php echo esc_html($date_end); ?>"
        autocomplete="off"
    >
</div>

<div class="ccwp-form-help-text">
    <p>*Required. Productions will not be displayed without opening AND closing dates. Must be in MM/DD/YYYY format.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_show_times">Show Times</label>
    <input type="text" id="ccwp_show_times" name="ccwp_show_times" value="<?php echo esc_html($show_times); ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_ticket_url">URL for Online Ticket Sales</label>
    <input type="text" id="ccwp_ticket_url" name="ccwp_ticket_url" value="<?php echo esc_html($ticket_url); ?>">
</div>

<div class="ccwp-form-help-text">
    <p>The "Default Tickets Url" will be used if no value is provided here.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_venue">Venue</label>
    <input type="text" id="ccwp_venue" name="ccwp_venue" value="<?php echo esc_html($venue); ?>">
</div>

<div class="ccwp-form-help-text">
    <p>Where the show was performed.</p>
</div>
