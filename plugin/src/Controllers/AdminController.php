<?php

namespace CurtainCallWP\Controllers;

use Carbon\CarbonImmutable as Carbon;
use CurtainCallWP\Helpers\CurtainCallHelpers as Helpers;

class AdminController extends CurtainCallController
{
    /**
     *  The ID of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     **/
    private $plugin_name;
    
    /**
     *  The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     **/
    private $version;
    
    /**
     *  Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     *                            *@since  1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }
    
    /**
     *  Register the stylesheets for the admin area.
     *
     * @since    0.0.1
     **/
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         **/
        
        wp_enqueue_style($this->plugin_name . '_datepicker',
            plugin_dir_url(__FILE__) . 'js/ccwp_datepicker/jquery-ui.css', array(), $this->version, 'all');
        
        wp_enqueue_style($this->plugin_name . '_general', plugin_dir_url(__FILE__) . 'css/curtian-call-wp-admin.css',
            array(), $this->version, 'all');
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.0.1
     **/
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         **/
        
        wp_enqueue_script($this->plugin_name . '_datepicker',
            plugin_dir_url(__FILE__) . 'js/ccwp_datepicker/jquery-ui.min.js', array('jquery'), $this->version, true);
        
        wp_enqueue_script($this->plugin_name . '_general', plugin_dir_url(__FILE__) . 'js/curtian-call-wp-admin.js',
            array('jquery', $this->plugin_name . '_datepicker'), $this->version, true);
        
    }
    
    //  ----------------------------------------------------------------------------------------------------------------
    //  Global Functions
    //  ----------------------------------------------------------------------------------------------------------------
    
    public function ccwp_set_post_type_title_on_post_submit(array $data)
    {
        $title_arr = [];
        if ($data['post_type'] === 'ccwp_production') {
            
            if (isset($_POST['ccwp_production_name'])) {
                $title_arr[] = sanitize_text_field($_POST['ccwp_production_name']);
            }
            
            /**
             * TODO: If, attached to a single season (post term), append the Season to the post title.
             *       Else, attach the start_date year to the production title.
             **/
            
            if (isset($_POST['ccwp_date_start'])) {
                if ( ! empty($title_arr)) {
                    $title_arr[] = '-';
                }
                
                $date_start = Carbon::parse(sanitize_text_field($_POST['ccwp_date_start']));
                
                if ($date_start) {
                    $title_arr[] = $date_start->format('Y');
                }
            }
            
            if (empty($title_arr)) {
                $title_arr[] = 'Untitled Curtain Call Production';
            }
            
        } elseif ($data['post_type'] === 'ccwp_cast_and_crew') {
            
            if (isset($_POST['ccwp_name_first'])) {
                $title_arr[] = sanitize_text_field($_POST['ccwp_name_first']);
            }
            
            if (isset($_POST['ccwp_name_last'])) {
                $title_arr[] = sanitize_text_field($_POST['ccwp_name_last']);
            }
            
            if (empty($title_arr)) {
                $title_arr[] = 'Untitled Curtain Call Cast/Crew - ' . $data['ID'];
            }
        }
        
        if ( ! empty($title_arr)) {
            $title = implode(' ', $title_arr);
            $title = preg_replace('~\s\s+~', ' ', $title);
            
            $data['post_name']  = sanitize_title($title);
            $data['post_title'] = $title;
        }
        
        return $data;
    }
    
    //  ----------------------------------------------------------------------------------------------------------------
    //  Production Functions
    //  ----------------------------------------------------------------------------------------------------------------
    
    /**
     *  Register the production custom post type.
     *
     * @since    0.0.1
     **/
    public function create_production_custom_post_type()
    {
        $args = [
            'description'   => 'Displays your theatre company\'s productions and their relevant data',
            'labels'        => [
                'name'               => _x('Productions', 'post type general name'),
                'singular_name'      => _x('Production', 'post type singular name'),
                'add_new'            => _x('Add New', 'Production'),
                'add_new_item'       => __('Add New Production'),
                'edit_item'          => __('Edit Production'),
                'new_item'           => __('New Production'),
                'all_items'          => __('All Productions'),
                'view_item'          => __('View Production'),
                'search_items'       => __('Search productions'),
                'not_found'          => __('No productions found'),
                'not_found_in_trash' => __('No productions found in the Trash'),
                'parent_item_colon'  => '',
                'menu_name'          => 'Productions',
            ],
            'public'        => true,
            'menu_position' => 2.5,
            'supports'      => [
                'title',
                'editor',
                'thumbnail',
            ],
            'taxonomies'    => [
                'ccwp_production_seasons'
            ],
            'has_archive'   => true,
            'rewrite'       => [
                'slug' => 'productions',
            ],
        ];
        register_post_type('ccwp_production', $args);
    }
    
    /**
     *  Create the production custom post type taxonomies.
     *
     * @since    0.0.1
     **/
    public function create_production_custom_taxonomies()
    {
        // Add new taxonomy, make it hierarchical (like categories)
        $args = [
            'hierarchical'      => true,
            'labels'            => [
                'name'              => _x('Seasons', 'taxonomy general name', 'curtain-call-wp'),
                'singular_name'     => _x('Season', 'taxonomy singular name', 'curtain-call-wp'),
                'search_items'      => __('Search Seasons', 'curtain-call-wp'),
                'all_items'         => __('All Seasons', 'curtain-call-wp'),
                'parent_item'       => __('Parent Season', 'curtain-call-wp'),
                'parent_item_colon' => __('Parent Season:', 'curtain-call-wp'),
                'edit_item'         => __('Edit Season', 'curtain-call-wp'),
                'update_item'       => __('Update Season', 'curtain-call-wp'),
                'add_new_item'      => __('Add New Season', 'curtain-call-wp'),
                'new_item_name'     => __('New Season Title', 'curtain-call-wp'),
                'menu_name'         => __('Seasons', 'curtain-call-wp'),
            ],
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [
                'slug' => 'seasons',
            ],
        ];
        
        register_taxonomy('ccwp_production_seasons', array('ccwp_production'), $args);
    }
    
    /**
     *  Creation of all custom fields for the production custom post type
     *
     *  since 0.0.1
     **/
    public function add_custom_meta_boxes_for_production_post_type()
    {
        add_meta_box(
            'ccwp_add_cast_and_crew_to_production', // Unique ID
            __('Add Cast And Crew to Production', 'curtain-call-wp'), // Box title
            array($this, 'ccwp_add_cast_and_crew_to_production_box_html'), // Content callback
            'ccwp_production', // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
        //[ 'example' => 'arguments you can place into the meta_box renderer' ]
        );
        
        add_meta_box(
            'ccwp_production_details', // Unique ID
            __('Production Details', 'curtain-call-wp'), // Box title
            array($this, 'ccwp_production_details_box_html'), // Content callback
            'ccwp_production', // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
        //[ example' => 'arguments you can place into the meta_box renderer' ]
        );
    }
    
    public function ccwp_production_details_box_html($post, $metabox)
    {
        wp_nonce_field(basename(__FILE__), 'ccwp_production_details_box_nonce');
        
        $prod_name        = get_post_meta($post->ID, '_ccwp_production_name', true);
        $prod_date_start  = Carbon::parse(get_post_meta($post->ID, '_ccwp_production_date_start',
            true))->format('m/d/Y');
        $prod_date_end    = Carbon::parse(get_post_meta($post->ID, '_ccwp_production_date_end', true))->format('m/d/Y');
        $prod_show_times  = get_post_meta($post->ID, '_ccwp_production_show_times', true);
        $prod_ticket_url  = get_post_meta($post->ID, '_ccwp_production_ticket_url', true);
        $prod_venue       = get_post_meta($post->ID, '_ccwp_production_venue', true);
        $prod_description = get_post_meta($post->ID, '_ccwp_production_description', true);
        $prod_press       = get_post_meta($post->ID, '_ccwp_production_press', true);
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
        
        <!--
        <div class="ccwp-form-group">
            <label for="description">Production Description</label>
            <textarea id="description" name="description"><?php echo $prod_description; ?></textarea>
        </div>
        -->
        
        <div class="ccwp-form-group">
            <label for="ccwp_press">Press Highlights</label>
            <textarea id="ccwp_press" name="ccwp_press"><?php echo $prod_press; ?></textarea>
        </div>
        <?php
    }
    
    public function ccwp_production_details_save($post_id)
    {
        # Verify meta box nonce
        if (
            !isset($_POST['ccwp_production_details_box_nonce'])
            ||  !wp_verify_nonce($_POST['ccwp_production_details_box_nonce'], basename(__FILE__))
        ) {
            return;
        }
        
        # Return if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        # Check the user's permissions.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        # Store custom fields values
        if (!empty($_REQUEST['ccwp_production_name'])) {
            update_post_meta($post_id, '_ccwp_production_name', sanitize_text_field($_POST['ccwp_production_name']));
        } else {
            delete_post_meta($post_id, '_ccwp_production_name');
        }
        
        if (!empty($_REQUEST['ccwp_date_start'])) {
            $ccwp_date_start = sanitize_text_field($_POST['ccwp_date_start']);
            $ccwp_date_start = Carbon::parse($ccwp_date_start);
            update_post_meta($post_id, '_ccwp_production_date_start', $ccwp_date_start->format('Y-m-d'));
        } else {
            delete_post_meta($post_id, '_ccwp_production_date_start');
        }
        
        if (!empty($_REQUEST['ccwp_date_end'])) {
            $ccwp_date_end = sanitize_text_field($_POST['ccwp_date_end']);
            $ccwp_date_end = Carbon::parse($ccwp_date_end);
            update_post_meta($post_id, '_ccwp_production_date_end', $ccwp_date_end->format('Y-m-d'));
        } else {
            delete_post_meta($post_id, '_ccwp_production_date_end');
        }
        
        if (!empty($_REQUEST['ccwp_show_times'])) {
            update_post_meta($post_id, '_ccwp_production_show_times', sanitize_text_field($_POST['ccwp_show_times']));
        } else {
            delete_post_meta($post_id, '_ccwp_production_show_times');
        }
        
        if (!empty($_REQUEST['ccwp_ticket_url'])) {
            update_post_meta($post_id, '_ccwp_production_ticket_url', sanitize_text_field($_POST['ccwp_ticket_url']));
        } else {
            delete_post_meta($post_id, '_ccwp_production_ticket_url');
        }
        
        if (!empty($_REQUEST['ccwp_venue'])) {
            update_post_meta($post_id, '_ccwp_production_venue', sanitize_text_field($_POST['ccwp_venue']));
        } else {
            delete_post_meta($post_id, '_ccwp_production_venue');
        }
        
        if (!empty($_REQUEST['ccwp_description'])) {
            update_post_meta($post_id, '_ccwp_production_description', sanitize_text_field($_POST['ccwp_description']));
        } else {
            delete_post_meta($post_id, '_ccwp_production_description');
        }
        
        if (!empty($_REQUEST['ccwp_press'])) {
            update_post_meta($post_id, '_ccwp_production_press', sanitize_text_field($_POST['ccwp_press']));
        } else {
            delete_post_meta($post_id, '_ccwp_production_press');
        }
        
        if (empty($title_arr)) {
            $title_arr[] = 'Untitled Curtain Call Production';
        }
    }
    
    /**
     *  On the creation of a production post insert a create a custom production taxonomy for cast and crew.
     **/
    public function on_insert_production_post($post_id, $post, $update)
    {
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        if ($post->post_type !== 'ccwp_production') {
            return;
        }
        
        // Check if the taxonomy exists
        $tax_id = term_exists($post->post_title, 'ccwp_cast_crew_productions', 0);
        
        // Create it if it doesn't exists
        if ( ! $tax_id) {
            $tax_id = wp_insert_term($post->post_title, 'ccwp_cast_crew_productions', array('parent' => 0));
        }
    }
    
    public function ccwp_add_cast_and_crew_to_production_box_html($post, $metabox)
    {
        wp_nonce_field(basename(__FILE__), 'ccwp_add_cast_and_crew_to_production_box_nonce');
        
        // Get all castcrew by id and name
        $all_castcrew_members = Helpers::get_all_cast_and_crew_for_select();
        
        // Get all related cast and crew membery to this production
        $production_castcrew_members = Helpers::get_cast_and_crew([
            'post_id' => $post->ID,
        ]);
        
        // Sort them into cast then crew memebers
        if ( ! empty($production_castcrew_members) && is_array($production_castcrew_members)) {
            $cast_members = array_filter($production_castcrew_members, function ($castcrew_member) {
                return ($castcrew_member['ccwp_type'] == 'cast');
            });
            
            $crew_members = array_filter($production_castcrew_members, function ($castcrew_member) {
                return ($castcrew_member['ccwp_type'] == 'crew');
            });
        }
        ?>
        <div class="ccwp-add-castcrew-to-production-wrap">
            <div class="ccwp-select-wrap">
                <label for="ccwp-add-cast-to-production-select">Add Cast: </label>
                <select id="ccwp-add-cast-to-production-select" class="ccwp-admin-dropdown-field">
                    <option value="0">Select Cast</option>
                    <?php foreach ($all_castcrew_members as $castcrew_member): ?>
                        <option value="<?php echo $castcrew_member['ID']; ?>"><?php echo $castcrew_member['post_title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" id="ccwp-add-cast-to-production-button">Add Cast</button>
        </div>
        
        <div id="ccwp-production-cast-wrap">
            <?php if ( ! empty($cast_members) && is_array($cast_members)): ?>
                <table class="ccwp-admin-field-table">
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Billing</th>
                        <th></th>
                    </tr>
                    <?php foreach ($cast_members as $cast_member): ?>
                        <tr class="form-group ccwp-production-castcrew-form-group"
                            id="ccwp-production-cast-member-<?php echo $cast_member['ID']; ?>">
                            <input type="hidden"
                                   name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][cast_and_crew_id]"
                                   value="<?php echo $cast_member['ID']; ?>">
                            <input type="hidden"
                                   name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][production_id]"
                                   value="<?php echo $post->ID; ?>">
                            <input type="hidden"
                                   name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][type]"
                                   value="cast">
                            <td class="ccwp-castcrew-name"><?php echo $cast_member['post_title']; ?></td>
                            <td><input type="text"
                                       name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][role]"
                                       placeholder="role" value="<?php echo $cast_member['ccwp_role']; ?>"></td>
                            <td><input type="text"
                                       name="ccwp_add_cast_to_production[<?php echo $cast_member['ID']; ?>][custom_order]"
                                       placeholder="custom order"
                                       value="<?php echo $cast_member['ccwp_custom_order']; ?>"></td>
                            <td>
                                <button type="button" class="ccwp-remove-castcrew-from-production"
                                        data-target="ccwp-production-cast-member-<?php echo $cast_member['ID']; ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
        
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
                <table class="ccwp-admin-field-table">
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Billing</th>
                        <th></th>
                    </tr>
                    <?php foreach ($crew_members as $crew_member): ?>
                        <tr class="form-group ccwp-production-castcrew-form-group"
                            id="ccwp-production-crew-member-<?php echo $crew_member['ID']; ?>">
                            <input type="hidden"
                                   name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][cast_and_crew_id]"
                                   value="<?php echo $crew_member['ID']; ?>">
                            <input type="hidden"
                                   name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][production_id]"
                                   value="<?php echo $post->ID; ?>">
                            <input type="hidden"
                                   name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][type]"
                                   value="crew">
                            <td class="ccwp-castcrew-name"><?php echo $crew_member['post_title']; ?></td>
                            <td><input type="text"
                                       name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][role]"
                                       placeholder="role" value="<?php echo $crew_member['ccwp_role']; ?>"></td>
                            <td><input type="text"
                                       name="ccwp_add_crew_to_production[<?php echo $crew_member['ID']; ?>][custom_order]"
                                       placeholder="custom order"
                                       value="<?php echo $crew_member['ccwp_custom_order']; ?>"></td>
                            <td>
                                <button type="button" class="ccwp-remove-castcrew-from-production"
                                        data-target="ccwp-production-crew-member-<?php echo $crew_member['ID']; ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function ccwp_add_cast_and_crew_to_production_save($post_id)
    {
        # Verify meta box nonce
        if (
            ! isset($_POST['ccwp_add_cast_and_crew_to_production_box_nonce'])
            || ! wp_verify_nonce($_POST['ccwp_add_cast_and_crew_to_production_box_nonce'], basename(__FILE__))
        ) {
            return;
        }
        
        # Return if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        # Check the user's permissions.
        if ( ! current_user_can('edit_post', $post_id)) {
            return;
        }
        
        # Store custom fields values
        
        //pr($_POST['ccwp_add_cast_to_production']);
        //pr($_POST['ccwp_add_cast_to_production']);
        
        $production_cast = ! empty($_POST['ccwp_add_cast_to_production']) ? $_POST['ccwp_add_cast_to_production'] : null;
        $production_crew = ! empty($_POST['ccwp_add_crew_to_production']) ? $_POST['ccwp_add_crew_to_production'] : null;
        
        Helpers::save_cast_and_crew_to_production($post_id, $production_cast, 'cast');
        Helpers::save_cast_and_crew_to_production($post_id, $production_crew, 'crew');
    }
    
    //----------------------------------------------------------------------------------------------------------------
    //  Cast And Crew Functions
    //----------------------------------------------------------------------------------------------------------------
    
    /**
     *  Register the cast/crew custom post type.
     *
     * @since    0.0.1
     **/
    public function create_cast_crew_custom_post_type()
    {
        $args = [
            'description'   => 'The Cast and Crew for you productions',
            'labels'        => [
                'name'               => _x('Cast and Crew', 'post type general name'),
                'singular_name'      => _x('Cast or Crew', 'post type singular name'),
                'add_new'            => _x('Add New', 'Cast or Crew'),
                'add_new_item'       => __('Add New cast or crew'),
                'edit_item'          => __('Edit cast or crew'),
                'new_item'           => __('New cast or crew'),
                'all_items'          => __('All cast and crew'),
                'view_item'          => __('View cast and crew'),
                'search_items'       => __('Search cast and crew'),
                'not_found'          => __('No cast or crew found'),
                'not_found_in_trash' => __('No cast or crew found in the Trash'),
                'parent_item_colon'  => '',
                'menu_name'          => 'Cast and Crew',
            ],
            'public'        => true,
            'menu_position' => 2.6,
            'supports'      => [
                'title',
                'editor',
                'thumbnail',
            ],
            'taxonomies'    => [
                'ccwp_cast_crew_productions',
            ],
            'has_archive'   => true,
            'rewrite'       => [
                'slug' => 'cast-and-crew',
            ],
        ];
        
        register_post_type('ccwp_cast_and_crew', $args);
    }
    
    /**
     *  Create the cast/crew custom post type taxonomies.
     *
     * @since    0.0.1
     **/
    public function create_cast_crew_custom_taxonomies()
    {
        // Add new taxonomy, NOT hierarchical (like tags)
        $args = [
            'hierarchical'          => false,
            'labels'                => [
                'name'                       => _x('C&C Productions', 'taxonomy general name', 'curtain-call-wp'),
                'singular_name'              => _x('C&C Production', 'taxonomy singular name', 'curtain-call-wp'),
                'search_items'               => __('Search Productions', 'curtain-call-wp'),
                'popular_items'              => __('Popular Productions', 'curtain-call-wp'),
                'all_items'                  => __('All Productions', 'curtain-call-wp'),
                'parent_item'                => null,
                'parent_item_colon'          => null,
                'edit_item'                  => __('Edit Production', 'curtain-call-wp'),
                'update_item'                => __('Update Production', 'curtain-call-wp'),
                'add_new_item'               => __('Add New Production', 'curtain-call-wp'),
                'new_item_name'              => __('New Production Name', 'curtain-call-wp'),
                'separate_items_with_commas' => __('Separate productions with commas', 'curtain-call-wp'),
                'add_or_remove_items'        => __('Add or remove productions', 'curtain-call-wp'),
                'choose_from_most_used'      => __('Choose from the most used productions', 'curtain-call-wp'),
                'not_found'                  => __('No productions found.', 'curtain-call-wp'),
                'menu_name'                  => __('Productions', 'curtain-call-wp'),
            ],
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => [
                'slug' => 'cast-and-crew-productions',
            ],
        ];
        
        register_taxonomy('ccwp_cast_crew_productions', array('ccwp_cast_and_crew'), $args);
    }
    
    /**
     *  Creation of all custom fields for the production custom post type
     *
     *  since 0.0.1
     **/
    public function add_custom_meta_box_for_cast_and_crew_post_type()
    {
        add_meta_box(
            'ccwp_cast_and_cast_details', // Unique ID
            __('Cast and Crew Details', 'curtain-call-wp'), // Box title
            array($this, 'ccwp_cast_and_crew_details_box_html'), // Content callback
            'ccwp_cast_and_crew', // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
        //[ 'example' => 'arguments you can place into the meta_box renderer' ]
        );
    }
    
    public function ccwp_cast_and_crew_details_box_html($post, $metabox)
    { ?>
        <?php //echo '<pre>'; print_r($post); echo '</pre>';
        ?>
        <?php //echo '<pre>'; print_r($metabox); echo '</pre>';
        ?>
        <?php
        wp_nonce_field(basename(__FILE__), 'ccwp_cast_and_crew_details_box_nonce');
        
        $cast_crew_name_first     = get_post_meta($post->ID, '_ccwp_cast_crew_name_first', true);
        $cast_crew_name_last      = get_post_meta($post->ID, '_ccwp_cast_crew_name_last', true);
        $cast_crew_self_title     = get_post_meta($post->ID, '_ccwp_cast_crew_self_title', true);
        $cast_crew_birthday       = Carbon::parse(get_post_meta($post->ID, '_ccwp_cast_crew_birthday',
            true))->format('m/d/Y');
        $cast_crew_hometown       = get_post_meta($post->ID, '_ccwp_cast_crew_hometown', true);
        $cast_crew_website_link   = get_post_meta($post->ID, '_ccwp_cast_crew_website_link', true);
        $cast_crew_facebook_link  = get_post_meta($post->ID, '_ccwp_cast_crew_facebook_link', true);
        $cast_crew_twitter_link   = get_post_meta($post->ID, '_ccwp_cast_crew_twitter_link', true);
        $cast_crew_instagram_link = get_post_meta($post->ID, '_ccwp_cast_crew_instagram_link', true);
        $cast_crew_fun_fact       = get_post_meta($post->ID, '_ccwp_cast_crew_fun_fact', true);
        ?>
        
        <div class="ccwp-form-group">
            <label for="ccwp_name_first"><strong>First Name*</strong></label>
            <input type="text" id="ccwp_name_first" name="ccwp_name_first" value="<?php echo $cast_crew_name_first; ?>">
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_name_last"><strong>Last Name*</strong></label>
            <input type="name_last" id="ccwp_name_last" name="ccwp_name_last"
                   value="<?php echo $cast_crew_name_last ?>">
        </div>
        <div class="ccwp-form-help-text">
            <p>*Required. These fields are used to auto-generate the post title with the cast or crew member's full
                name.</p>
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_self_title"><strong>Title*</strong></label>
            <input type="self_title" id="ccwp_self_title" name="ccwp_self_title"
                   value="<?php echo $cast_crew_self_title ?>">
        </div>
        <div class="ccwp-form-help-text">
            <p>*Required. If the cast or crew member has many roles across different productions, try to use the one
                they identify with the most. Ex. Director, Actor, Choreographer, etc.</p>
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_birthday">Birthday</label>
            <input type="text" class="ccwp_datepicker_input" id="ccwp_birthday" name="ccwp_birthday"
                   value="<?php echo $cast_crew_birthday; ?>">
        </div>
        <div class="ccwp-form-help-text">
            <p>Must be in MM/DD/YYYY format.</p>
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_hometown">Hometown</label>
            <input type="text" id="ccwp_hometown" name="ccwp_hometown" value="<?php echo $cast_crew_hometown; ?>">
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_website_link">Website Link</label>
            <input type="text" id="ccwp_website_link" name="ccwp_website_link"
                   value="<?php echo $cast_crew_website_link; ?>">
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_facebook_link">Facebook Link</label>
            <input type="text" id="ccwp_facebook_link" name="ccwp_facebook_link"
                   value="<?php echo $cast_crew_facebook_link; ?>">
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_twitter_link">Twitter Link</label>
            <input type="text" id="ccwp_twitter_link" name="ccwp_twitter_link"
                   value="<?php echo $cast_crew_twitter_link; ?>">
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_instagram_link">Instagram Link</label>
            <input type="text" id="ccwp_instagram_link" name="ccwp_instagram_link"
                   value="<?php echo $cast_crew_instagram_link; ?>">
        </div>
        
        <div class="ccwp-form-group">
            <label for="ccwp_fun_fact">Fun Fact</label>
            <input type="text" id="ccwp_fun_fact" name="ccwp_fun_fact" value="<?php echo $cast_crew_fun_fact; ?>">
        </div>
        <div class="ccwp-form-help-text">
            <p>This should be kept to one sentence.</p>
        </div>
    
    <?php }
    
    
    public function ccwp_cast_and_crew_details_save($post_id)
    {
        # Verify meta box nonce
        if (
            ! isset($_POST['ccwp_cast_and_crew_details_box_nonce'])
            || ! wp_verify_nonce($_POST['ccwp_cast_and_crew_details_box_nonce'], basename(__FILE__))
        ) {
            return;
        }
        /*
        # Return if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        */
        # Check the user's permissions.
        if ( ! current_user_can('edit_post', $post_id)) {
            return;
        }
        
        # Store custom fields values
        
        if ( ! empty($_REQUEST['ccwp_name_first'])) {
            // update data
            update_post_meta($post_id, '_ccwp_cast_crew_name_first', sanitize_text_field($_POST['ccwp_name_first']));
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_name_first');
        }
        
        if ( ! empty($_REQUEST['ccwp_name_last'])) {
            // update data
            update_post_meta($post_id, '_ccwp_cast_crew_name_last', sanitize_text_field($_POST['ccwp_name_last']));
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_name_last');
        }
        
        if ( ! empty($_REQUEST['ccwp_self_title'])) {
            // update data
            update_post_meta($post_id, '_ccwp_cast_crew_self_title', sanitize_text_field($_POST['ccwp_self_title']));
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_self_title');
        }
        
        if ( ! empty($_REQUEST['ccwp_birthday'])) {
            // update data
            $ccwp_birthday = sanitize_text_field($_POST['ccwp_birthday']);
            $ccwp_birthday = Carbon::parse($ccwp_birthday);
            update_post_meta($post_id, '_ccwp_cast_crew_birthday', $ccwp_birthday->format('Y-m-d'));
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_birthday');
        }
        
        if ( ! empty($_REQUEST['ccwp_hometown'])) {
            // update data
            update_post_meta($post_id, '_ccwp_cast_crew_hometown', sanitize_text_field($_POST['ccwp_hometown']));
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_hometown');
        }
        
        if ( ! empty($_REQUEST['ccwp_website_link'])) {
            // update data
            $link = Helpers::strip_http($_POST['ccwp_website_link']);
            $link = sanitize_text_field($link);
            update_post_meta($post_id, '_ccwp_cast_crew_website_link', $link);
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_website_link');
        }
        
        if ( ! empty($_REQUEST['ccwp_facebook_link'])) {
            // update data
            $link = Helpers::strip_http($_POST['ccwp_facebook_link']);
            $link = sanitize_text_field($link);
            update_post_meta($post_id, '_ccwp_cast_crew_facebook_link', $link);
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_facebook_link');
        }
        
        if ( ! empty($_REQUEST['ccwp_twitter_link'])) {
            // update data
            $link = Helpers::strip_http($_POST['ccwp_twitter_link']);
            $link = sanitize_text_field($link);
            update_post_meta($post_id, '_ccwp_cast_crew_twitter_link', $link);
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_twitter_link');
        }
        
        if ( ! empty($_REQUEST['ccwp_instagram_link'])) {
            // update data
            $link = Helpers::strip_http($_POST['ccwp_instagram_link']);
            $link = sanitize_text_field($link);
            update_post_meta($post_id, '_ccwp_cast_crew_instagram_link', $link);
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_instagram_link');
        }
        
        if ( ! empty($_REQUEST['ccwp_fun_fact'])) {
            // update data
            update_post_meta($post_id, '_ccwp_cast_crew_fun_fact', sanitize_text_field($_POST['ccwp_fun_fact']));
        } else {
            // delete data
            delete_post_meta($post_id, '_ccwp_cast_crew_fun_fact');
        }
        
        if (empty($title_arr)) {
            $title_arr[] = 'Untitled Curtain Call Cast/Crew';
        }
    }
}