<?php

declare(strict_types=1);

namespace CurtainCall\Support;

class Str
{
    /**
     * @return array|string[]
     */
    public static function alphabet(): array
    {
        return [
            'A','B','C','D','E','F','G','H','I',
            'J','K','L','M','N','O','P','Q','R',
            'S','T','U','V','W','X','Y','Z'
        ];
    }

    /**
     * @param string $value
     * @param string $case
     * @return string
     */
    public static function firstLetter(string $value, string $case = ''): string
    {
        $letter = static::substr($value, 0, 1);

        switch (static::lower($case)) {
            case 'upper':
            case 'uppercase':
                return static::upper($letter);
            case 'lower':
            case 'lowercase':
                return static::lower($letter);
            default:
                return $letter;
        }
    }

    /**
     * @param string $value
     * @return string
     */
    public static function lower(string $value): string
    {
        if (!function_exists('mb_strtolower')) {
            return strtolower($value);
        }

        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Strips extra spaces and replaces them with a single space
     *
     * @param string $subject
     * @return string
     */
    public static function stripExtraSpaces(string $subject)
    {
        return preg_replace('~\s\s+~', ' ', $subject);
    }

    /**
     * Strip the protocol from a url
     *
     * @param string $url
     * @return string
     */
    public static function stripHttp(string $url)
    {
        return preg_replace('#^https?://#', '', $url);
    }

    /**
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @return false|string
     */
    public static function substr(string $string, int $start, ?int $length = null)
    {
        if (!function_exists('mb_substr')) {
            return substr($string, $start, $length);
        }

        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * @param string $value
     * @return string
     */
    public static function title(string $value)
    {
        if (!function_exists('mb_convert_case')) {
            return ucwords(static::lower($value));
        }

        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @param string $value
     * @return string
     */
    public static function upper(string $value): string
    {
        if (!function_exists('mb_strtoupper')) {
            return strtoupper($value);
        }

        return mb_strtoupper($value, 'UTF-8');
    }
}
