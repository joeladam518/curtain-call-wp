<?php

declare(strict_types=1);

namespace CurtainCall\LifeCycle;

interface LifeCycleHook
{
    public static function run(): void;
}
