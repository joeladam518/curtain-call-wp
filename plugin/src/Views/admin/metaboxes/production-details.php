<?php
/**
 * @var string $prod_name
 * @var string $prod_date_start
 * @var string $prod_date_end
 * @var string $prod_show_times
 * @var string $prod_ticket_url
 * @var string $prod_venue
 * @var string $prod_press
 */
if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;
wp_nonce_field(basename(__FILE__), 'ccwp_production_details_box_nonce');
?>

<div class="ccwp-form-group">
    <label for="ccwp_production_name">Production Name</label>
    <input type="text" id="ccwp_production_name" name="ccwp_production_name" value="<?php echo $prod_name; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_date_start"><strong>Production Dates - Opened*</strong></label>
    <input type="text" class="ccwp_datepicker_input" id="ccwp_date_start" name="ccwp_date_start"
           value="<?php echo $prod_date_start ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_date_end"><strong>Production Dates - Closed*</strong></label>
    <input type="text" class="ccwp_datepicker_input" id="ccwp_date_end" name="ccwp_date_end"
           value="<?php echo $prod_date_end; ?>">
</div>

<div class="ccwp-form-help-text">
    <p>*Required. Productions will not be displayed without opening AND closing dates. Must be in MM/DD/YYYY format.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_show_times">Show Times</label>
    <input type="text" id="ccwp_show_times" name="ccwp_show_times" value="<?php echo $prod_show_times; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_ticket_url">URL for Online Ticket Sales</label>
    <input type="text" id="ccwp_ticket_url" name="ccwp_ticket_url" value="<?php echo $prod_ticket_url; ?>">
</div>

<div class="ccwp-form-help-text">
    <p>Defaults to rutheckerdhall.com/events if left blank.
</div>

<div class="ccwp-form-group">
    <label for="ccwp_venue">Venue</label>
    <input type="text" id="ccwp_venue" name="ccwp_venue" value="<?php echo $prod_venue; ?>">
</div>

<div class="ccwp-form-help-text">
    <p>Where the show was performed.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_press">Press Highlights</label>
    <textarea id="ccwp_press" name="ccwp_press"><?php echo $prod_press; ?></textarea>
</div>
