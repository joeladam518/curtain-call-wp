<?php

namespace CurtainCallWP\Hooks;

use CurtainCallWP\PostTypes\CastAndCrew;
use CurtainCallWP\PostTypes\Production;

/**
 * Class CurtainCallController
 * @package CurtainCallWP\Controllers
 */
class CurtainCallHooks
{
    /**
     * Register the production custom post type.
     * @return void
     */
    public function createProductionPostType()
    {
        register_post_type('ccwp_production', Production::getConfig());
        flush_rewrite_rules();
    }
    
    /**
     * Register the cast/crew custom post type.
     * @return void
     */
    public function createCastAndCrewPostType()
    {
        register_post_type('ccwp_cast_and_crew', CastAndCrew::getConfig());
        flush_rewrite_rules();
    }
    
    /**
     * Create the production custom post type taxonomies.
     * @return void
     */
    public function createProductionSeasonsTaxonomy()
    {
        register_taxonomy('ccwp_production_seasons', array('ccwp_production'), Production::getSeasonsTaxonomyConfig());
        flush_rewrite_rules();
    }
    
    /**
     * View rewrite rules (for debugging)
     * @param array $rules
     * @return array
     */
    public function filterRewriteRulesArray($rules)
    {
        //pr($rules,1);
        return $rules;
    }
}

