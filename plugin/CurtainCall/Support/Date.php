<?php

declare(strict_types=1);

namespace CurtainCall\Support;

use Carbon\CarbonImmutable as Carbon;
use Carbon\Exceptions\InvalidFormatException;

class Date
{
    protected static ?string $todayCache = null;

    /**
     * Reformat a date string
     *
     * @param string|null $from
     * @param string $to
     * @param null $default
     *
     * @return string|null
     */
    public static function reformat(?string $from, string $to = 'Y-m-d', $default = null): ?string
    {
        $date = static::toCarbon($from);

        return $date ? $date->format($to) : $default;
    }

    /**
     * Convert a string to a carbon object. (Don't throw an exception)
     *
     * @param string|null $date
     * @return Carbon|null
     */
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

    /**
     * Get Today's date in yyyy-mm-dd format
     *
     * @return string
     */
    public static function today(): string
    {
        return static::$todayCache ??= Carbon::now()->toDateString();
    }
}
