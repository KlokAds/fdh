<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * A comma-separated list of up to 5 valid email addresses.
 */
class EmailList implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $emails = self::parse((string) $value);

        if (count($emails) > 5) {
            $fail('Enter at most 5 email addresses.');

            return;
        }

        foreach ($emails as $email) {
            if (Validator::make(['email' => $email], ['email' => 'email'])->fails()) {
                $fail("“{$email}” is not a valid email address.");

                return;
            }
        }
    }

    /**
     * @return list<string>
     */
    public static function parse(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
