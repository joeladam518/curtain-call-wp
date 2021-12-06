<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;

// show error/update messages
settings_errors('ccwp_messages');
?>

<div class="wrap ccwp-settings-page">
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
    </h1>
    <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting
        settings_fields('ccwp-settings');

        // output setting sections and their fields
        do_settings_sections('ccwp');

        // output save settings button
        submit_button('Save');
        ?>
    </form>
</div>
