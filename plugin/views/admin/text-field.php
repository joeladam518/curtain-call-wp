<?php

declare(strict_types=1);

if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die();
}

/**
 * @var string $id
 * @var string $name
 * @var list<string> $classes
 * @var string|null $placeholder
 * @var bool $required
 * @var bool $readonly
 * @var string|null $value
 * @var string|null $helpText
 */
?>

<div class="ccwp-input-wrap">
    <input
        type="text"
        class="<?php echo esc_attr(implode(' ', $classes)); ?>"
        name="<?php echo esc_attr($name); ?>"
        id="<?php echo esc_attr($id); ?>"
        <?php if ($required): ?>required<?php endif; ?>
        <?php if ($readonly): ?>readonly<?php endif; ?>
        <?php if ($placeholder): ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php endif; ?>
        <?php if (!is_null($value)): ?>value="<?php echo esc_attr($value); ?>"<?php endif; ?>
    >
    <?php if ($helpText): ?>
        <div class="ccwp-form-help-text">
            <p><?php echo esc_html($helpText); ?></p>
        </div>
    <?php endif; ?>
</div>
