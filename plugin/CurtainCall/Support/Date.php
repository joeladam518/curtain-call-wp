<?php

namespace CurtainCall\Support;

use Carbon\CarbonImmutable as Carbon;
use Carbon\Exceptions\InvalidFormatException;

class Date
{
    public static function reformat(?string $from, string $to = 'Y-m-d', $default = null): ?string
    {
        $date = static::toCarbon($from);

        return $date ? $date->format($to) : $default;
    }

    public static function toCarbon(?string $date): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (InvalidFormatException $e) {
            if (defined('CCWP_DEBUG') && CCWP_DEBUG) {
                throw $e;
            } else {
                return null;
            }
        }
    }
}
