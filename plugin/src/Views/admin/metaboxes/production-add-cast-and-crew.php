<?php if (!defined('ABSPATH') || !defined('CCWP_PLUGIN_PATH')) die;
/**
 * @var string $wp_nonce
 * @var WP_Post $post
 * @var array $metabox
 * @var array $all_cast_and_crew_members
 * @var array $cast_members
 * @var array $crew_members
 */
?>

<?php echo $wp_nonce; ?>

<!-- Add Cast -->
<div class="ccwp-add-castcrew-to-production-wrap">
    <div class="ccwp-select-wrap">
        <label for="ccwp-add-cast-to-production-select">Add Cast: </label>
        <select id="ccwp-add-cast-to-production-select" class="ccwp-admin-dropdown-field">
            <option value="0">Select Cast</option>
            <?php foreach ($all_cast_and_crew_members as $castcrew_member): ?>
            <option value="<?php echo $castcrew_member['ID']; ?>"><?php echo $castcrew_member['post_title']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="button" id="ccwp-add-cast-to-production-button">Add Cast</button>
</div>

<div id="ccwp-production-cast-wrap">
    <?php if (!empty($cast_members) && is_array($cast_members)): ?>
        <?php foreach($cast_members as $index => $cast_member): ?>
            <div class="form-group ccwp-production-castcrew-form-group" id="ccwp-production-cast-member-<?php echo $cast_member['ID']; ?>">
                <input
                    type="hidden"
                    name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][cast_and_crew_id]"
                    value="<?php echo $cast_member['ID']; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][production_id]"
                    value="<?php echo $post->ID; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][type]"
                    value="cast"
                >
                <div class="row">
                    <div class="col">
                        <span class="label">Name</span>
                        <div class="ccwp-castcrew-name"><?php echo $cast_member['post_title']; ?></div>
                    </div>
                    <div class="col">
                        <label for="ccwp-production-cast-role-<?php echo $index ?>">Role</label>
                        <input
                            type="text"
                            id="ccwp-production-cast-role-<?php echo $index ?>"
                            name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][role]"
                            placeholder="role" value="<?php echo $cast_member['ccwp_role']; ?>"
                        >
                    </div>
                    <div class="col">
                        <label for="ccwp-production-cast-custom-order-<?php echo $index ?>">Billing</label>
                        <input
                            type="text"
                            id="ccwp-production-cast-custom-order-<?php echo $index ?>"
                            name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][custom_order]"
                            placeholder="custom order"
                            value="<?php echo $cast_member['ccwp_custom_order']; ?>"
                        >
                    </div>
                    <div class="col">
                        <button
                            type="button"
                            class="ccwp-remove-castcrew-from-production"
                            data-target="ccwp-production-cast-member-<?php echo $cast_member['ID']; ?>"
                        >Delete</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Crew -->
<div class="ccwp-add-castcrew-to-production-wrap" style="margin-top: 25px;">
    <div class="ccwp-select-wrap">
        <label for="ccwp-add-crew-to-production-select">Add Crew: </label>
        <select id="ccwp-add-crew-to-production-select" class="ccwp-admin-dropdown-field">
            <option value="0">Select Crew</option>
            <?php foreach ($all_castcrew_members as $castcrew_member): ?>
            <option value="<?php echo $castcrew_member['ID']; ?>"><?php echo $castcrew_member['post_title']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="button" id="ccwp-add-crew-to-production-button">Add Crew</button>
</div>

<div id="ccwp-production-crew-wrap">
    <?php if ( ! empty($crew_members) && is_array($crew_members)): ?>
        <?php foreach($crew_members as $index => $crew_member): ?>
            <div class="form-group ccwp-production-castcrew-form-group" id="ccwp-production-crew-member-<?php echo $crew_member['ID']; ?>">
                <input
                    type="hidden"
                    name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][cast_and_crew_id]"
                    value="<?php echo $crew_member['ID']; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][production_id]"
                    value="<?php echo $post->ID; ?>"
                >
                <input
                    type="hidden"
                    name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][type]"
                    value="crew"
                >
                <div class="row">
                    <div class="col">
                        <span class="label">Name</span>
                        <div class="ccwp-castcrew-name">
                            <?php echo $crew_member['post_title']; ?>
                        </div>
                    </div>
                    <div class="col">
                        <label for="ccwp-production-crew-role-<?php echo $index ?>">Role</label>
                        <input
                            type="text"
                            id="ccwp-production-crew-role-<?php echo $index ?>"
                            name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][role]"
                            placeholder="role" value="<?php echo $crew_member['ccwp_role']; ?>"
                        >
                    </div>
                    <div class="col">
                        <label for="ccwp-production-crew-role-<?php echo $index ?>">Billing</label>
                        <input
                            type="text"
                            name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][custom_order]"
                            placeholder="custom order"
                            value="<?php echo $crew_member['ccwp_custom_order']; ?>"
                        >
                    </div>
                    <div class="col">
                        <button
                            type="button"
                            class="ccwp-remove-castcrew-from-production"
                            data-target="ccwp-production-crew-member-<?php echo $crew_member['ID']; ?>"
                        >Delete</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
