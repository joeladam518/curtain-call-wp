<?php

declare(strict_types=1);

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
        unregister_setting('ccwp-settings', 'ccwp_color_link_highlight');
        unregister_setting('ccwp-settings', 'ccwp_color_button_background');
        unregister_setting('ccwp-settings', 'ccwp_color_button_text');
        flush_rewrite_rules(false);
    }
}
