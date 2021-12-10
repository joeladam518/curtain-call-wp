<?php

namespace CurtainCall\LifeCycle;

use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\Production;

class Deactivator implements LifeCycleHook
{
    public static function run(): void
    {
        unregister_post_type(CastAndCrew::POST_TYPE);
        unregister_post_type(Production::POST_TYPE);
        unregister_taxonomy(Production::SEASONS_TAXONOMY);
        unregister_setting('ccwp-settings', 'ccwp_default_ticket_url');
        flush_rewrite_rules(false);
    }
}
