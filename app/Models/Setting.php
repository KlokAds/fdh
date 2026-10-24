<?php

namespace App\Models;

use App\Support\SiteContent;
use Database\Factories\SettingFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Raw stored values merged over registry defaults. Translatable fields are arrays keyed by locale.
     *
     * @return array<string, string|array<string, string>>
     */
    public static function raw(): array
    {
        return Cache::rememberForever('site-settings', function (): array {
            $stored = self::query()->pluck('value', 'key')->all();
            $values = [];

            foreach (SiteContent::fields() as $key => $field) {
                $default = $field['default'];
                $value = $stored[$key] ?? null;

                if (! empty($field['translatable'])) {
                    $decoded = is_string($value) ? json_decode($value, true) : null;
                    $values[$key] = array_merge($default, array_filter(is_array($decoded) ? $decoded : [], fn ($v): bool => is_string($v) && $v !== ''));
                } else {
                    $values[$key] = $value === null ? ($field['type'] === 'image' ? '' : $default) : (string) $value;
                }
            }

            return $values;
        });
    }

    /**
     * All settings resolved for one locale (falls back to English).
     *
     * @return array<string, string>
     */
    public static function forLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return array_map(
            fn (string|array $value): string => is_array($value) ? ($value[$locale] ?? $value['en'] ?? '') : $value,
            self::raw(),
        );
    }

    public static function read(string $key, ?string $locale = null): string
    {
        return self::forLocale($locale)[$key] ?? '';
    }

    /**
     * Decrypted value of a secret setting (API tokens), or an empty string.
     */
    public static function secret(string $key): string
    {
        $value = self::raw()[$key] ?? '';

        if (! is_string($value) || $value === '') {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return '';
        }
    }

    public static function imageUrl(string $key, string $fallback): string
    {
        $path = self::raw()[$key] ?? '';

        if (is_string($path) && $path !== '') {
            return Storage::disk('public')->url($path);
        }

        // Bundled images change with releases; the version keeps CDNs from serving an old copy.
        $file = public_path($fallback);

        return asset($fallback).(is_file($file) ? '?v='.filemtime($file) : '');
    }

    /**
     * @param  array<string, string|array<string, string>|null>  $values
     */
    public static function write(array $values): void
    {
        $fields = SiteContent::fields();

        foreach ($values as $key => $value) {
            if (! isset($fields[$key])) {
                continue;
            }

            self::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value],
            );
        }

        Cache::forget('site-settings');
    }
}
