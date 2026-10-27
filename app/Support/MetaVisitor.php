<?php

namespace App\Support;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * A website visitor in the shape Meta matches on. Everything personal is SHA-256 hashed on the
 * server; raw names, numbers and addresses never leave it.
 */
class MetaVisitor
{
    /** First-party visitor id, sent hashed as external_id by both the pixel and the server. */
    public const VISITOR_COOKIE = 'fdh_vid';

    private const SESSION_KEY = 'meta.match';

    /**
     * Cities and districts as Meta expects them (lowercase, no spaces), with the spellings visitors type.
     *
     * @var array<string, list<string>>
     */
    private const CITIES = [
        'rangpur' => ['rangpur', 'রংপুর'],
        'dhaka' => ['dhaka', 'ঢাকা'],
        'dinajpur' => ['dinajpur', 'দিনাজপুর'],
        'gaibandha' => ['gaibandha', 'গাইবান্ধা'],
        'kurigram' => ['kurigram', 'কুড়িগ্রাম', 'কুড়িগ্রাম'],
        'lalmonirhat' => ['lalmonirhat', 'লালমনিরহাট'],
        'nilphamari' => ['nilphamari', 'নীলফামারী'],
        'saidpur' => ['saidpur', 'সৈয়দপুর'],
        'thakurgaon' => ['thakurgaon', 'ঠাকুরগাঁও'],
        'panchagarh' => ['panchagarh', 'পঞ্চগড়', 'পঞ্চগড়'],
        'bogura' => ['bogura', 'bogra', 'বগুড়া', 'বগুড়া'],
        'rajshahi' => ['rajshahi', 'রাজশাহী'],
        'gazipur' => ['gazipur', 'গাজীপুর'],
        'narayanganj' => ['narayanganj', 'নারায়ণগঞ্জ', 'নারায়ণগঞ্জ'],
        'mymensingh' => ['mymensingh', 'ময়মনসিংহ', 'ময়মনসিংহ'],
        'chattogram' => ['chattogram', 'chittagong', 'চট্টগ্রাম'],
        'cumilla' => ['cumilla', 'comilla', 'কুমিল্লা'],
        'sylhet' => ['sylhet', 'সিলেট'],
        'khulna' => ['khulna', 'খুলনা'],
        'barishal' => ['barishal', 'barisal', 'বরিশাল'],
    ];

    public static function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    public static function newVisitorId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function visitorId(Request $request): ?string
    {
        $id = (string) $request->cookie(self::VISITOR_COOKIE);

        return preg_match('/^[a-f0-9]{32}$/', $id) ? $id : null;
    }

    /**
     * Hash what an inquiry tells us about the visitor and remember it for the rest of the visit,
     * so the pages they open afterwards match as well as the inquiry itself.
     *
     * @return array<string, string>
     */
    public static function rememberInquiry(Inquiry $inquiry): array
    {
        $words = preg_split('/\s+/u', mb_strtolower(trim($inquiry->name)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $city = self::city($inquiry->location);

        $match = array_filter([
            'ph' => self::hash(MetaConversions::normalizePhone($inquiry->phone)),
            'em' => $inquiry->email ? self::hash(mb_strtolower(trim($inquiry->email))) : null,
            'fn' => $words ? self::hash($words[0]) : null,
            'ln' => count($words) > 1 ? self::hash(end($words)) : null,
            'ct' => $city ? self::hash($city) : null,
        ]);

        session()->put(self::SESSION_KEY, $match);

        return $match;
    }

    /**
     * user_data for a Conversions API event.
     *
     * @return array<string, mixed>
     */
    public static function userData(Request $request): array
    {
        $visitorId = self::visitorId($request);

        return array_filter([
            ...array_map(fn (string $hash): array => [$hash], (array) session(self::SESSION_KEY, [])),
            'country' => [self::hash('bd')],
            'external_id' => $visitorId ? [self::hash($visitorId)] : null,
            'client_ip_address' => $request->ip(),
            'client_user_agent' => Str::limit((string) $request->userAgent(), 400, '') ?: null,
            'fbp' => self::fbCookie($request, '_fbp'),
            'fbc' => self::fbCookie($request, '_fbc'),
        ]);
    }

    /**
     * Advanced matching for the browser pixel: the same hashed values the server sends.
     *
     * @return array<string, string>
     */
    public static function browserMatch(Request $request): array
    {
        $visitorId = self::visitorId($request);

        return array_filter([
            ...(array) session(self::SESSION_KEY, []),
            'country' => self::hash('bd'),
            'external_id' => $visitorId ? self::hash($visitorId) : null,
        ]);
    }

    /**
     * Add an event for the current page; the pixel and the Conversions API both send it, with one id.
     *
     * @param  array<string, string>  $customData
     */
    public static function queue(string $eventName, array $customData = []): void
    {
        $request = request();

        if (! $request->attributes->has('meta_events')) {
            return;
        }

        $request->attributes->set('meta_events', [
            ...$request->attributes->get('meta_events'),
            ['name' => $eventName, 'id' => self::eventId($eventName), 'custom' => array_filter($customData)],
        ]);
    }

    public static function eventId(string $eventName): string
    {
        return Str::lower(Str::substr($eventName, 0, 2)).'-'.Str::lower(Str::random(20));
    }

    public static function city(?string $location): ?string
    {
        $text = mb_strtolower((string) $location);

        foreach (self::CITIES as $city => $spellings) {
            foreach ($spellings as $spelling) {
                if ($text !== '' && str_contains($text, $spelling)) {
                    return $city;
                }
            }
        }

        return null;
    }

    public static function isBot(Request $request): bool
    {
        return (bool) preg_match('/bot|crawl|spider|slurp|lighthouse|headless|pagespeed|preview|facebookexternalhit|whatsapp/i', (string) $request->userAgent());
    }

    private static function fbCookie(Request $request, string $name): ?string
    {
        $value = (string) $request->cookie($name);

        return strlen($value) <= 500 && preg_match('/^fb\.\d\.\d{10,13}\.[A-Za-z0-9_\-.]+$/', $value) ? $value : null;
    }
}
