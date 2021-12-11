<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;
/**
 * @var string $wp_nonce
 * @var WP_Post $post
 * @var array $metabox
 * @var string $name_first
 * @var string $name_last
 * @var string $self_title
 * @var string $birthday
 * @var string $hometown
 * @var string $website_link
 * @var string $facebook_link
 * @var string $twitter_link
 * @var string $instagram_link
 * @var string $fun_fact
 */

echo $wp_nonce;
?>

<div class="ccwp-form-group">
    <label for="ccwp_name_first"><strong>First Name*</strong></label>
    <input type="text" id="ccwp_name_first" name="ccwp_name_first" value="<?php echo $name_first; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_name_last"><strong>Last Name*</strong></label>
    <input type="text" id="ccwp_name_last" name="ccwp_name_last" value="<?php echo $name_last; ?>">
</div>
<div class="ccwp-form-help-text">
    <p>*Required. These fields are used to auto-generate the post title with the cast or crew member's full
        name.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_self_title"><strong>Title*</strong></label>
    <input type="text" id="ccwp_self_title" name="ccwp_self_title" value="<?php echo $self_title ?>">
</div>
<div class="ccwp-form-help-text">
    <p>*Required. If the cast or crew member has many roles across different productions, try to use the one
        they identify with the most. Ex. Director, Actor, Choreographer, etc.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_birthday">Birthday</label>
    <input type="text" class="ccwp_datepicker_input" id="ccwp_birthday" name="ccwp_birthday"
           value="<?php echo $birthday; ?>" autocomplete="off">
</div>
<div class="ccwp-form-help-text">
    <p>Must be in MM/DD/YYYY format.</p>
</div>

<div class="ccwp-form-group">
    <label for="ccwp_hometown">Hometown</label>
    <input type="text" id="ccwp_hometown" name="ccwp_hometown" value="<?php echo $hometown; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_website_link">Website Link</label>
    <input type="text" id="ccwp_website_link" name="ccwp_website_link"
           value="<?php echo $website_link; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_facebook_link">Facebook Link</label>
    <input type="text" id="ccwp_facebook_link" name="ccwp_facebook_link"
           value="<?php echo $facebook_link; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_twitter_link">Twitter Link</label>
    <input type="text" id="ccwp_twitter_link" name="ccwp_twitter_link"
           value="<?php echo $twitter_link; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_instagram_link">Instagram Link</label>
    <input type="text" id="ccwp_instagram_link" name="ccwp_instagram_link"
           value="<?php echo $instagram_link; ?>">
</div>

<div class="ccwp-form-group">
    <label for="ccwp_fun_fact">Fun Fact</label>
    <input type="text" id="ccwp_fun_fact" name="ccwp_fun_fact" value="<?php echo $fun_fact; ?>">
</div>
<div class="ccwp-form-help-text">
    <p>This should be kept to one sentence.</p>
</div>
