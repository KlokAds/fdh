<?php

namespace App\Support;

use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Meta Pixel + Conversions API. The browser pixel and this server event share one event_id,
 * so Meta counts each inquiry once even when both arrive.
 */
class MetaConversions
{
    public static function pixelId(): string
    {
        $id = Setting::read('meta_pixel_id', 'en');

        return preg_match('/^\d{10,20}$/', $id) ? $id : '';
    }

    public function isConfigured(): bool
    {
        return self::pixelId() !== '' && Setting::secret('meta_capi_token') !== '';
    }

    /**
     * @param  array{ip: ?string, user_agent: ?string, fbp: ?string, fbc: ?string, url: string}  $context
     */
    public function lead(Inquiry $inquiry, string $eventId, array $context): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $event = [
            'event_name' => 'Lead',
            'event_time' => $inquiry->created_at->getTimestamp(),
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => $context['url'],
            'user_data' => array_filter([
                'ph' => [self::hash(self::normalizePhone($inquiry->phone))],
                'em' => $inquiry->email ? [self::hash(mb_strtolower(trim($inquiry->email)))] : null,
                'fn' => [self::hash(mb_strtolower(trim(strtok($inquiry->name, ' ') ?: $inquiry->name)))],
                'country' => [self::hash('bd')],
                'client_ip_address' => $context['ip'],
                'client_user_agent' => $context['user_agent'],
                'fbp' => $context['fbp'],
                'fbc' => $context['fbc'],
            ]),
            'custom_data' => array_filter([
                'content_name' => $inquiry->service,
                'content_category' => 'Inquiry',
            ]),
        ];

        $payload = array_filter([
            'data' => [$event],
            'test_event_code' => Setting::read('meta_test_code', 'en') ?: null,
        ]);

        try {
            $response = Http::timeout(8)
                ->asJson()
                ->post(sprintf('https://graph.facebook.com/%s/%s/events?access_token=%s', config('fdh.meta_graph_version'), self::pixelId(), urlencode(Setting::secret('meta_capi_token'))), $payload);

            if ($response->failed()) {
                Log::warning('Meta Conversions API rejected the Lead event: '.$response->body());

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Meta Conversions API request failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Bangladeshi mobile numbers in E.164 digits without "+", as Meta expects: 8801XXXXXXXXX.
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '01')) {
            return '88'.$digits;
        }

        return $digits;
    }

    private static function hash(string $value): string
    {
        return hash('sha256', $value);
    }
}
