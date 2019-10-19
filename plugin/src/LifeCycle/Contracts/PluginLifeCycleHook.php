<?php

namespace CurtainCallWP\LifeCycle\Contracts;

interface PluginLifeCycleHook
{
    public static function run(): void;
}