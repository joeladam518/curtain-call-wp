<?php

declare(strict_types=1);

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

/**
 * @var string $id
 * @var string $name
 * @var string $defaultColor
 * @var string|null $value
 * @var string|null $helpText
 */
?>

<div class="ccwp-input-wrap">
    <input
        type="text"
        class="ccwp-color-picker"
        name="<?php echo esc_attr($name); ?>"
        id="<?php echo esc_attr($id); ?>"
        data-default-color="<?php echo esc_attr($defaultColor); ?>"
        <?php if (!is_null($value) && $value !== ''): ?>value="<?php echo esc_attr($value); ?>"<?php endif; ?>
    >
    <?php if ($helpText): ?>
        <p class="description">
            <?php echo esc_html($helpText); ?>
        </p>
    <?php endif; ?>
</div>
