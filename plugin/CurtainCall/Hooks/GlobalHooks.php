<?php

namespace CurtainCall\Hooks;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;

class GlobalHooks
{
    /**
     * Register the cast/crew custom post type
     *
     * @return void
     */
    public function createCastAndCrewPostType(): void
    {
        register_post_type('ccwp_cast_and_crew', CastAndCrew::getConfig());
        flush_rewrite_rules();
    }

    /**
     * Register the production custom post type
     *
     * @return void
     */
    public function createProductionPostType(): void
    {
        register_post_type('ccwp_production', Production::getConfig());
        flush_rewrite_rules();
    }

    /**
     * Create the production custom post type taxonomies
     *
     * @return void
     */
    public function createProductionSeasonsTaxonomy(): void
    {
        register_taxonomy('ccwp_production_seasons', array('ccwp_production'), Production::getSeasonsTaxonomyConfig());
        flush_rewrite_rules();
    }

    /**
     *
     */
    {
    }
}

