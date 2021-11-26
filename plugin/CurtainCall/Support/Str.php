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

    public static function camel(string $value): string
    {
        return lcfirst(static::studly($value));
    }

    public static function lower(string $value): string
    {
        if (!function_exists('mb_strtolower')) {
            return strtolower($value);
        }

        return mb_strtolower($value, 'UTF-8');
    }

    public static function slug($value, $separator = '-'): string
    {
        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $value = preg_replace( '![' . preg_quote($flip) . ']+!u', $separator, $value);

        // Replace @ with the word 'at'
        $value = str_replace('@', $separator . 'at' . $separator, $value);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $value = preg_replace( '![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', static::lower($value));

        // Replace all separator characters and whitespace by a single separator
        $value = preg_replace( '![' . preg_quote($separator) . '\s]+!u', $separator, $value);

        return trim($value, $separator);
    }

    public function snake($value, $delimiter = '_'): string
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));

        return static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
    }

    public static function stripHttp(string $url): string
    {
        return preg_replace('#^https?://#', '', $url);
    }

    public static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    public static function title(string $value): string
    {
        if (!function_exists('mb_convert_case')) {
            return ucwords(static::lower($value));
        }

        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    public static function upper(string $value): string
    {
        if (!function_exists('mb_strtoupper')) {
            return strtoupper($value);
        }

        return mb_strtoupper($value, 'UTF-8');
    }
}
