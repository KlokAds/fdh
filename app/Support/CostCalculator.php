<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Approximate construction cost for the public calculator. Every rate comes from the admin settings.
 */
class CostCalculator
{
    /**
     * Square feet in one unit of land, as measured in Bangladesh.
     *
     * @var array<string, float>
     */
    public const LAND_UNITS = ['katha' => 720.0, 'shotok' => 435.6, 'sqft' => 1.0];

    public const LEVELS = ['standard', 'premium', 'luxury'];

    /**
     * Finishing levels that have a rate set, mapped to their rate per sq ft.
     *
     * @return array<string, int>
     */
    public static function rates(): array
    {
        $rates = [];
        foreach (self::LEVELS as $level) {
            $rate = (int) Setting::read("calc_rate_{$level}", 'en');
            if ($rate > 0) {
                $rates[$level] = $rate;
            }
        }

        return $rates;
    }

    /**
     * The calculator is shown only when the admin turned it on and entered at least one rate.
     */
    public static function isAvailable(): bool
    {
        return Setting::read('calc_enabled', 'en') === 'yes' && self::rates() !== [];
    }

    /**
     * @return array{land_sqft: int, floor_sqft: int, total_sqft: int, rate: int, estimate: int, low: int, high: int}|null
     */
    public static function estimate(float $landSize, string $unit, int $floors, string $level): ?array
    {
        $rates = self::rates();

        if ($landSize <= 0 || $floors < 1 || ! isset(self::LAND_UNITS[$unit], $rates[$level])) {
            return null;
        }

        $coverage = max(20, min(100, (int) Setting::read('calc_coverage', 'en'))) / 100;
        $range = max(0, min(50, (int) Setting::read('calc_range', 'en'))) / 100;

        $landSqft = $landSize * self::LAND_UNITS[$unit];
        $floorSqft = $landSqft * $coverage;
        $totalSqft = $floorSqft * $floors;
        $estimate = $totalSqft * $rates[$level];

        return [
            'land_sqft' => (int) round($landSqft),
            'floor_sqft' => (int) round($floorSqft),
            'total_sqft' => (int) round($totalSqft),
            'rate' => $rates[$level],
            'estimate' => (int) round($estimate),
            'low' => (int) round($estimate * (1 - $range)),
            'high' => (int) round($estimate * (1 + $range)),
        ];
    }

    /**
     * Taka in the way people say it in Bangladesh: "৳ 45.6 lakh" / "৳ ৪৫.৬ লাখ", "৳ 1.25 crore".
     */
    public static function taka(int $amount, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $bn = $locale === 'bn';

        // Decide the unit after rounding, so 99.96 lakh reads as 1 crore rather than "100 lakh".
        if (round($amount / 100_000, 1) >= 100) {
            $number = rtrim(rtrim(number_format($amount / 10_000_000, 2, '.', ''), '0'), '.');
            $text = $number.' '.($bn ? 'কোটি' : 'crore');
        } elseif ($amount >= 100_000) {
            $number = rtrim(rtrim(number_format($amount / 100_000, 1, '.', ''), '0'), '.');
            $text = $number.' '.($bn ? 'লাখ' : 'lakh');
        } else {
            $text = self::groupIndian($amount);
        }

        return '৳ '.($bn ? self::bnDigits($text) : $text);
    }

    /**
     * 1234567 → 12,34,567 (South Asian digit grouping).
     */
    public static function groupIndian(int $number): string
    {
        $digits = (string) abs($number);
        if (strlen($digits) <= 3) {
            return $digits;
        }

        $last3 = substr($digits, -3);
        $rest = substr($digits, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);

        return $rest.','.$last3;
    }

    public static function number(int $number, ?string $locale = null): string
    {
        $text = self::groupIndian($number);

        return ($locale ?? app()->getLocale()) === 'bn' ? self::bnDigits($text) : $text;
    }

    public static function bnDigits(string $text): string
    {
        return strtr($text, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
}
