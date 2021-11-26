<?php

namespace CurtainCall\Support;

class Str
{
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
     * @return string
     */
    public static function camel(string $value): string
    {
        return lcfirst(static::studly($value));
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
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public function snake(string $value, string $delimiter = '_'): string
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));

        return static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
    }

    /**
     * Strip the protocol from a url
     *
     * @param string $url
     * @return string
     */
    public static function stripHttp(string $url): string
    {
        return preg_replace('#^https?://#', '', $url);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function title(string $value): string
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
