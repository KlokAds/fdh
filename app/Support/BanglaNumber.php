<?php

namespace App\Support;

/**
 * Numbers as people read them in Bangladesh: Indian digit grouping (12,34,567) and Bangla digits on Bangla pages.
 */
class BanglaNumber
{
    public static function digits(string $text): string
    {
        return strtr($text, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }

    /**
     * A grouped number, in Bangla digits when the page is in Bangla.
     */
    public static function number(int $number, ?string $locale = null): string
    {
        $text = self::group($number);

        return ($locale ?? app()->getLocale()) === 'bn' ? self::digits($text) : $text;
    }

    public static function group(int $number): string
    {
        $digits = (string) abs($number);
        if (strlen($digits) <= 3) {
            return $digits;
        }

        $last3 = substr($digits, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($digits, 0, -3));

        return $rest.','.$last3;
    }
}
