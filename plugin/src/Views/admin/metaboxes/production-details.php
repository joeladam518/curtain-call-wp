<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;
/**
 * @var string  $wp_nonce
 * @var WP_Post $post
 * @var string  $name
 * @var string  $date_start
 * @var string  $date_end
 * @var string  $show_times
 * @var string  $ticket_url
 * @var string  $venue
 * @var string  $press
 */
?>

<?php echo $wp_nonce; ?>

<div class="ccwp-form-group">
    <label for="ccwp_production_name">Production Name</label>
    <input type="text" id="ccwp_production_name" name="ccwp_production_name" value="<?php echo $name; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_date_start"><strong>Production Dates - Opened*</strong></label>
    <input type="text" class="ccwp_datepicker_input" id="ccwp_date_start" name="ccwp_date_start" value="<?php echo $date_start ?>" autocomplete="off">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_date_end"><strong>Production Dates - Closed*</strong></label>
    <input type="text" class="ccwp_datepicker_input" id="ccwp_date_end" name="ccwp_date_end" value="<?php echo $date_end; ?>" autocomplete="off">
</div>

<div class="ccwp-form-help-text">
    <p>*Required. Productions will not be displayed without opening AND closing dates. Must be in MM/DD/YYYY format.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_show_times">Show Times</label>
    <input type="text" id="ccwp_show_times" name="ccwp_show_times" value="<?php echo $show_times; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_ticket_url">URL for Online Ticket Sales</label>
    <input type="text" id="ccwp_ticket_url" name="ccwp_ticket_url" value="<?php echo $ticket_url; ?>">
</div>

<div class="ccwp-form-help-text">
    <p>Defaults to rutheckerdhall.com/events if left blank.
</div>

<div class="ccwp-form-group">
    <label for="ccwp_venue">Venue</label>
    <input type="text" id="ccwp_venue" name="ccwp_venue" value="<?php echo $venue; ?>">
</div>

<div class="ccwp-form-help-text">
    <p>Where the show was performed.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_press">Press Highlights</label>
    <textarea id="ccwp_press" name="ccwp_press"><?php echo $press; ?></textarea>
</div>
