<?php
/**
 * @var string $wp_nonce
 * @var WP_Post $post
 * @var array $metabox
 * @var array $all_cast_crew_names
 * @var array|CastAndCrew[] $cast_members
 * @var array|CastAndCrew[] $crew_members
 */
if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) {
    die;
}

use CurtainCall\Models\CastAndCrew;

echo $wp_nonce;
?>

<!-- Add Cast -->
<div class="ccwp-production-castcrew-select-wrap">
    <div class="ccwp-select-form-group">
        <label for="ccwp-production-cast-select">Add Cast: </label>
        <select id="ccwp-production-cast-select" class="ccwp-admin-select-box" style="width: 250px;">
            <option value="0">Select Cast</option>
            <?php foreach ($all_cast_crew_names as $post_id => $castcrew_name) : ?>
            <option value="<?php echo $post_id; ?>"><?php echo $castcrew_name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="button" class="button ccwp-production-castcrew-add-btn" id="ccwp-production-cast-add-btn">
        Add Cast
    </button>
</div>

<div id="ccwp-production-cast-wrap">
    <?php if (!empty($cast_members) && is_array($cast_members)) : ?>
        <div class="ccwp-row label-row">
            <div class="ccwp-col name-col">Name</div>
            <div class="ccwp-col role-col">Role</div>
            <div class="ccwp-col billing-col">Billing</div>
            <div class="ccwp-col action-col">&nbsp;</div>
        </div>
        <?php foreach ($cast_members as $cast_member) : ?>
            <div class="form-group ccwp-production-castcrew-form-group" id="ccwp-production-cast-<?php echo $cast_member->ID; ?>">
                <input
                    type="hidden"
                    name="ccwp_add_cast_to_production[<?php echo $cast_member->ID; ?>][cast_and_crew_id]"
                    value="<?php echo $cast_member->ID; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_cast_to_production[<?php echo $cast_member->ID; ?>][production_id]"
                    value="<?php echo $post->ID; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_cast_to_production[<?php echo $cast_member->ID; ?>][type]"
                    value="cast"
                >
                <div class="ccwp-row">
                    <div class="ccwp-col name-col">
                        <div class="ccwp-castcrew-name"><?php echo $cast_member->post_title; ?></div>
                    </div>
                    <div class="ccwp-col role-col">
                        <input
                            type="text"
                            id="ccwp-production-cast-role-<?php echo $cast_member->ID; ?>"
                            name="ccwp_add_cast_to_production[<?php echo $cast_member->ID; ?>][role]"
                            placeholder="role"
                            value="<?php echo $cast_member->ccwp_join->role; ?>"
                        >
                    </div>
                    <div class="ccwp-col billing-col">
                        <input
                            type="text"
                            id="ccwp-production-cast-custom-order-<?php echo $cast_member->ID; ?>"
                            name="ccwp_add_cast_to_production[<?php echo $cast_member->ID; ?>][custom_order]"
                            placeholder="custom order"
                            value="<?php echo $cast_member->ccwp_join->custom_order; ?>"
                        >
                    </div>
                    <div class="ccwp-col action-col">
                        <button
                            type="button"
                            class="button ccwp-production-castcrew-remove-btn"
                            data-target="ccwp-production-cast-<?php echo $cast_member->ID; ?>"
                        >Delete</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Crew -->
<div class="ccwp-production-castcrew-select-wrap" style="margin-top: 25px;">
    <div class="ccwp-select-form-group">
        <label for="ccwp-production-crew-select">Add Crew: </label>
        <select id="ccwp-production-crew-select" class="ccwp-admin-select-box" style="width: 250px;">
            <option value="0">Select Crew</option>
            <?php foreach ($all_cast_crew_names as $post_id => $castcrew_name) : ?>
            <option value="<?php echo $post_id; ?>"><?php echo $castcrew_name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="button ccwp-production-castcrew-add-btn" type="button" id="ccwp-production-crew-add-btn">Add Crew</button>
</div>

<div id="ccwp-production-crew-wrap">
    <?php if (!empty($crew_members) && is_array($crew_members)) : ?>
        <div class="ccwp-row label-row">
            <div class="ccwp-col name-col">Name</div>
            <div class="ccwp-col role-col">Role</div>
            <div class="ccwp-col billing-col">Billing</div>
            <div class="ccwp-col action-col">&nbsp;</div>
        </div>

        <?php foreach ($crew_members as $crew_member) : ?>
            <div class="form-group ccwp-production-castcrew-form-group" id="ccwp-production-crew-<?php echo $crew_member->ID; ?>">
                <input
                    type="hidden"
                    name="ccwp_add_crew_to_production[<?php echo $crew_member->ID; ?>][cast_and_crew_id]"
                    value="<?php echo $crew_member->ID; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_crew_to_production[<?php echo $crew_member->ID; ?>][production_id]"
                    value="<?php echo $post->ID; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_crew_to_production[<?php echo $crew_member->ID; ?>][type]"
                    value="crew"
                >
                <div class="ccwp-row">
                    <div class="ccwp-col name-col">
                        <div class="ccwp-castcrew-name"><?php echo $crew_member->post_title; ?></div>
                    </div>
                    <div class="ccwp-col role-col">
                        <input
                            type="text"
                            id="ccwp-production-crew-role-<?php echo $crew_member->ID; ?>"
                            name="ccwp_add_crew_to_production[<?php echo $crew_member->ID; ?>][role]"
                            placeholder="role"
                            value="<?php echo $crew_member->ccwp_join->role; ?>"
                        >
                    </div>
                    <div class="ccwp-col billing-col">
                        <input
                            type="text"
                            id="ccwp-production-crew-custom-order-<?php echo $crew_member->ID; ?>"
                            name="ccwp_add_crew_to_production[<?php echo $crew_member->ID; ?>][custom_order]"
                            placeholder="custom order"
                            value="<?php echo $crew_member->ccwp_join->custom_order; ?>"
                        >
                    </div>
                    <div class="ccwp-col action-col">
                        <button
                            type="button"
                            class="button ccwp-production-castcrew-remove-btn"
                            data-target="ccwp-production-crew-<?php echo $crew_member->ID; ?>"
                        >Delete</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
