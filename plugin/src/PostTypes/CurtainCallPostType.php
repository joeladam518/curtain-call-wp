<?php

namespace CurtainCallWP\PostTypes;

abstract class CurtainCallPostType
{
    protected static $meta = [];
    abstract public static function getConfig(): array;
}