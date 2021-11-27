<?php

namespace CurtainCall\LifeCycle;

use CurtainCall\PostTypes\CastAndCrew;
use CurtainCall\PostTypes\Production;

class Deactivator implements LifeCycleHook
{
    public static function run(): void
    {
        unregister_post_type(CastAndCrew::POST_TYPE);
        unregister_post_type(Production::POST_TYPE);
        flush_rewrite_rules(false);
    }
}
