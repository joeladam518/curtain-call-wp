<?php

namespace CurtainCallWP\LifeCycle;

class Deactivator implements LifeCycleHook
{
    public static function run(): void
    {
        unregister_post_type('ccwp_production');
        unregister_post_type('ccwp_cast_and_crew');
        flush_rewrite_rules(false);
    }
}