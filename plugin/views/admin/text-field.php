<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;
/**
 * @var string $id
 * @var string $name
 * @var array $classes
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
        class="<?php echo implode(' ', $classes); ?>"
        name="<?php echo $name; ?>"
        id="<?php echo $id; ?>"
        <?php if ($required) : ?>required<?php endif; ?>
        <?php if ($readonly) : ?>readonly<?php endif; ?>
        <?php if ($placeholder) : ?>placeholder="<?php echo $placeholder; ?>"<?php endif; ?>
        <?php if (!is_null($value)) : ?>value="<?php echo $value; ?>"<?php endif; ?>
    >
    <?php if ($helpText) : ?>
        <div class="ccwp-form-help-text">
            <p><?php echo $helpText; ?></p>
        </div>
    <?php endif; ?>
</div>
