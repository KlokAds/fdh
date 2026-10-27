<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Meta Pixel + Conversions API, sent from this server (no tag manager or third-party relay).
 * The browser pixel and the server send every event with the same event_id, so Meta counts it once.
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
     * Send events for one visitor in a single Conversions API request.
     *
     * @param  array<string, mixed>  $userData  from MetaVisitor::userData()
     * @param  list<array{name: string, id: string, custom?: array<string, string>, time?: int}>  $events
     */
    public function track(array $userData, string $url, array $events): bool
    {
        if (! $this->isConfigured() || $events === []) {
            return false;
        }

        $data = array_map(fn (array $event): array => array_filter([
            'event_name' => $event['name'],
            'event_time' => $event['time'] ?? time(),
            'event_id' => $event['id'],
            'action_source' => 'website',
            'event_source_url' => $url,
            'user_data' => $userData,
            'custom_data' => ($event['custom'] ?? []) ?: null,
        ]), $events);

        $payload = array_filter([
            'data' => $data,
            'test_event_code' => Setting::read('meta_test_code', 'en') ?: null,
        ]);

        try {
            $response = Http::timeout(8)
                ->asJson()
                // The token goes in the body, so it never appears in a logged URL.
                ->post(sprintf('https://graph.facebook.com/%s/%s/events', config('fdh.meta_graph_version'), self::pixelId()), $payload + ['access_token' => Setting::secret('meta_capi_token')]);

            if ($response->failed()) {
                Log::warning('Meta Conversions API rejected '.implode(', ', array_column($events, 'name')).': '.$response->body());

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
}
