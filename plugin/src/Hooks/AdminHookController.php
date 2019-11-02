<?php

namespace CurtainCallWP\Controllers;

use CurtainCallWP\PostTypes\CastAndCrew;
use CurtainCallWP\PostTypes\Production;
use Carbon\CarbonImmutable as Carbon;
use CurtainCallWP\Helpers\CurtainCallHelpers as Helpers;

/**
 * Class AdminController
 * @package CurtainCallWP\Controllers
 */
class AdminHookController extends CurtainCallHookController
{
    /**
     *  Initialize the class and set its properties.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->assets_url = ccwp_assets_url() . 'admin/';
        $this->assets_path = ccwp_assets_path() . 'admin/';
    }
    
    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueueStyles()
    {
        $handle = $this->plugin_name . '_admin';
        $admin_css_url = $this->assets_url . 'css/curtain-call-wp-admin.css';
        wp_enqueue_style($handle, $admin_css_url, array(), $this->plugin_version, 'all');
    }
    
    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueueScripts()
    {
        $handle = $this->plugin_name . '_admin';
        $admin_js_url = $this->assets_url . 'css/curtain-call-wp-admin.css';
        wp_enqueue_script($handle, $admin_js_url, array('jquery'), $this->plugin_version, true);
    }
    
    //  ----------------------------------------------------------------------------------------------------------------
    //  Global Functions
    //  ----------------------------------------------------------------------------------------------------------------
    
    public function setPostTitleOnPostSave(array $data)
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
                $title_arr[] = 'Untitled Curtain Call Cast/Crew';
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
     */
    public function createProductionPostType()
    {
        $args = Production::getConfig();
        register_post_type('ccwp_production', $args);
        add_rewrite_rule('productions/?$', 'index.php?post_type=ccwp_production', 'top');
        add_rewrite_rule('productions/page/([0-9]{1,})/?$', 'index.php?post_type=ccwp_production&paged=$matches[1]', 'top');
        add_rewrite_rule('productions/([^/]+)/?$', 'index.php?ccwp_production=$matches[1]', 'top');
        flush_rewrite_rules();
    }
    
    /**
     *  Create the production custom post type taxonomies.
     */
    public function createProductionSeasonsTaxonomy()
    {
        $args = Production::getSeasonsTaxonomyConfig();
        register_taxonomy('ccwp_production_seasons', array('ccwp_production'), $args);
        flush_rewrite_rules();
    }
    
    /**
     *  On the creation of a production post insert a create a custom production taxonomy for cast and crew.
     * @param $post_id
     * @param $post
     * @param $update
     */
    public function onInsertProductionPost($post_id, $post, $update)
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
    
    /**
     *  Creation of all custom fields for the production custom post type
     */
    public function addProductionPostMetaBoxes()
    {
        add_meta_box(
            'ccwp_add_cast_and_crew_to_production', // Unique ID
            __('Add Cast And Crew to Production', 'curtain-call-wp'), // Box title
            array($this, 'renderAddCastAndCrewMetaBox'), // Content callback
            'ccwp_production', // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
            //[ 'example' => 'arguments you can place into the meta_box renderer' ]
        );
        
        add_meta_box(
            'ccwp_production_details', // Unique ID
            __('Production Details', 'curtain-call-wp'), // Box title
            array($this, 'renderProductionDetailsMetaBox'), // Content callback
            'ccwp_production', // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
            //[ example' => 'arguments you can place into the meta_box renderer' ]
        );
    }
    
    public function renderAddCastAndCrewMetaBox($post, $metabox)
    {
        $wp_nonce = wp_nonce_field(basename(__FILE__), 'ccwp_add_cast_and_crew_to_production_box_nonce', true, false);
        
        // Get all castcrew by id and name
        $all_cast_and_crew_members = Production::getCastAndCrewForSelectBox();
        
        // Get all related cast and crew members to this production
        $production_cast_and_crew_members = Production::getCastAndCrew($post->ID, 'both', false);
        $cast_members = [];
        $crew_members = [];
        
        // Sort them into cast then crew members
        if (!empty($production_cast_and_crew_members) && is_array($production_cast_and_crew_members)) {
            $cast_members = array_filter($production_cast_and_crew_members, function ($castcrew_member) {
                return ($castcrew_member['ccwp_type'] == 'cast');
            });
            $cast_members = array_values($cast_members);
            
            $crew_members = array_filter($production_cast_and_crew_members, function ($castcrew_member) {
                return ($castcrew_member['ccwp_type'] == 'crew');
            });
            $crew_members = array_values($crew_members);
        }
        
        ccwp_view('admin/metaboxes/production-add-cast-and-crew.php', [
            'wp_nonce'   => $wp_nonce,
            'post'       => $post,
            'metabox'    => $metabox,
            'all_cast_and_crew_members' => $all_cast_and_crew_members,
            'cast_members' => $cast_members,
            'crew_members' => $crew_members,
        ])->render();
    }
    
    public function renderProductionDetailsMetaBox($post, $metabox)
    {
        $wp_nonce = wp_nonce_field(basename(__FILE__), 'ccwp_production_details_box_nonce', true, false);
        $date_start = get_post_meta($post->ID, '_ccwp_production_date_start', true);
        $date_start = Carbon::parse($date_start)->format('m/d/Y');
        $date_end = get_post_meta($post->ID, '_ccwp_production_date_end', true);
        $date_end = Carbon::parse($date_end)->format('m/d/Y');

        ccwp_view('admin/metaboxes/production-details.php', [
            'wp_nonce'   => $wp_nonce,
            'post'       => $post,
            'metabox'    => $metabox,
            'name'       => get_post_meta($post->ID, '_ccwp_production_name', true),
            'date_start' => $date_start,
            'date_end'   => $date_end,
            'show_times' => get_post_meta($post->ID, '_ccwp_production_show_times', true),
            'ticket_url' => get_post_meta($post->ID, '_ccwp_production_ticket_url', true),
            'venue'      => get_post_meta($post->ID, '_ccwp_production_venue', true),
            'press'      => get_post_meta($post->ID, '_ccwp_production_venue', true),
        ])->render();
    }
    
    public function saveProductionPostCastAndCrew($post_id)
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
        $production_cast = ! empty($_POST['ccwp_add_cast_to_production']) ? $_POST['ccwp_add_cast_to_production'] : [];
        $production_crew = ! empty($_POST['ccwp_add_crew_to_production']) ? $_POST['ccwp_add_crew_to_production'] : [];
        
        Production::addCastAndCrew($post_id, 'cast', $production_cast);
        Production::addCastAndCrew($post_id, 'crew', $production_crew);
    }
    
    public function saveProductionPostDetails($post_id)
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
        
        if (!empty($_REQUEST['ccwp_press'])) {
            update_post_meta($post_id, '_ccwp_production_press', sanitize_text_field($_POST['ccwp_press']));
        } else {
            delete_post_meta($post_id, '_ccwp_production_press');
        }
        
        if (empty($title_arr)) {
            $title_arr[] = 'Untitled Curtain Call Production';
        }
    }
    
    //----------------------------------------------------------------------------------------------------------------
    //  Cast And Crew Functions
    //----------------------------------------------------------------------------------------------------------------
    
    /**
     *  Register the cast/crew custom post type.
     *
     * @since    0.0.1
     **/
    public function createCastAndCrewPostType()
    {
        $args = CastAndCrew::getConfig();
        register_post_type('ccwp_cast_and_crew', $args);
        flush_rewrite_rules();
    }
    
    /**
     *  Creation of all custom fields for the production custom post type
     *
     *  since 0.0.1
    **/
    public function addCastAndCrewPostMetaBoxes()
    {
        add_meta_box(
            'ccwp_cast_and_cast_details', // Unique ID
            __('Cast and Crew Details', CCWP_TEXT_DOMAIN), // Box title
            array($this, 'ccwp_cast_and_crew_details_box_html'), // Content callback
            'ccwp_cast_and_crew', // Post type
            'normal', // Context: (normal, side, advanced)
            'high' // Priority: (high, low)
            //[ 'example' => 'arguments you can place into the meta_box renderer' ]
        );
    }
    
    public function ccwp_cast_and_crew_details_box_html($post, $metabox)
    {
        wp_nonce_field(basename(__FILE__), 'ccwp_cast_and_crew_details_box_nonce');
        
        $cast_crew_name_first     = get_post_meta($post->ID, '_ccwp_cast_crew_name_first', true);
        $cast_crew_name_last      = get_post_meta($post->ID, '_ccwp_cast_crew_name_last', true);
        $cast_crew_self_title     = get_post_meta($post->ID, '_ccwp_cast_crew_self_title', true);
        $cast_crew_birthday       = get_post_meta($post->ID, '_ccwp_cast_crew_birthday',true);
        $cast_crew_birthday       = !empty($cast_crew_birthday)
                                    ? Carbon::parse($cast_crew_birthday)->format('m/d/Y')
                                    : '';
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
                   value="<?php echo $cast_crew_birthday; ?>" autocomplete="off">
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
    <?php
    }
    
    
    public function saveCastAndCrewPostDetails($post_id)
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