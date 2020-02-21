<?php

namespace CurtainCallWP\Hooks;

use CurtainCallWP\PostTypes\CastAndCrew;
use CurtainCallWP\PostTypes\Production;

/**
 * Class CurtainCallController
 * @package CurtainCallWP\Controllers
 */
class GlobalHookController
{
    /**
     * Register the production custom post type.
     * @return void
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
     * Create the production custom post type taxonomies.
     * @return void
     */
    public function createProductionSeasonsTaxonomy()
    {
        $args = Production::getSeasonsTaxonomyConfig();
        register_taxonomy('ccwp_production_seasons', array('ccwp_production'), $args);
        flush_rewrite_rules();
    }
    
    /**
     * Register the cast/crew custom post type.
     * @return void
     */
    public function createCastAndCrewPostType()
    {
        $args = CastAndCrew::getConfig();
        register_post_type('ccwp_cast_and_crew', $args);
        flush_rewrite_rules();
    }
}

