<?php

namespace Tests\Feature;

use App\Livewire\Admin\Content;
use App\Livewire\Site\InquiryForm;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class MetaTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_pixel_and_facebook_csp_only_appear_once_a_pixel_id_is_set(): void
    {
        $response = $this->get('/');
        $response->assertDontSee('fbevents.js');
        $this->assertStringNotContainsString('facebook', $response->headers->get('Content-Security-Policy'));

        Setting::write(['meta_pixel_id' => '1234567890123']);

        $response = $this->get('/');
        $response->assertSee('fbevents.js')->assertSee('1234567890123');
        $this->assertStringContainsString('https://connect.facebook.net', $response->headers->get('Content-Security-Policy'));
    }

    public function test_inquiry_sends_a_deduplicated_lead_to_the_conversions_api(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $this->withoutDefer();
        Setting::write([
            'meta_pixel_id' => '1234567890123',
            'meta_capi_token' => Crypt::encryptString('test-token'),
            'notify_email' => '',
        ]);

        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();
        $component->set('name', 'Karim Uddin')->set('phone', '01712-345678')->call('submit')
            ->assertSet('sent', true)
            ->assertDispatched('fdh-lead');

        Http::assertSent(function (Request $request): bool {
            $event = $request['data'][0];

            return str_contains($request->url(), '/1234567890123/events')
                && ! str_contains($request->url(), 'access_token')
                && $request['access_token'] === 'test-token'
                && $event['event_name'] === 'Lead'
                && str_starts_with($event['event_id'], 'lead-')
                && $event['user_data']['ph'] === [hash('sha256', '8801712345678')]
                && $event['user_data']['fn'] === [hash('sha256', 'karim')];
        });
    }

    public function test_no_conversions_call_without_a_token(): void
    {
        Http::fake();
        $this->withoutDefer();
        Setting::write(['meta_pixel_id' => '1234567890123', 'notify_email' => '']);

        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();
        $component->set('name', 'Karim')->set('phone', '01712345678')->call('submit')->assertSet('sent', true);

        Http::assertNothingSent();
    }

    public function test_saved_access_token_is_encrypted_and_never_sent_to_the_browser(): void
    {
        $super = $this->admin();

        $component = Livewire::actingAs($super)->test(Content::class, ['tab' => 'tracking'])
            ->set('values.meta_pixel_id', '1234567890123')
            ->set('secrets.meta_capi_token', 'super-secret-token')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('values.meta_capi_token', '')
            ->assertSet('secrets', []);

        $this->assertStringNotContainsString('super-secret-token', $component->html());
        $this->assertSame('super-secret-token', Setting::secret('meta_capi_token'));
        $this->assertNotSame('super-secret-token', Setting::query()->find('meta_capi_token')->value);

        // Saving again with the field left empty keeps the stored token.
        Livewire::actingAs($super)->test(Content::class, ['tab' => 'tracking'])->call('save');
        $this->assertSame('super-secret-token', Setting::secret('meta_capi_token'));
    }

    public function test_an_ad_click_gets_first_party_cookies_and_a_matching_server_page_view(): void
    {
        $this->configureMeta();

        $response = $this->get('/?fbclid=IwAR0abcDEF123_xyz');

        $fbc = $response->getCookie('_fbc', false)->getValue();
        $visitorId = $response->getCookie('fdh_vid', false)->getValue();
        $this->assertMatchesRegularExpression('/^fb\.1\.\d{13}\.IwAR0abcDEF123_xyz$/', $fbc);
        $this->assertMatchesRegularExpression('/^fb\.1\.\d{13}\.\d+$/', $response->getCookie('_fbp', false)->getValue());
        $this->assertTrue($response->getCookie('fdh_vid', false)->isHttpOnly());

        preg_match('/data-meta-events="([^"]+)"/', $response->getContent(), $match);
        $browserEvents = json_decode(html_entity_decode($match[1]), true);
        $this->assertSame('PageView', $browserEvents[0]['name']);

        Http::assertSent(function (Request $request) use ($browserEvents, $fbc, $visitorId): bool {
            $event = $request['data'][0];

            return $event['event_name'] === 'PageView'
                && $event['event_id'] === $browserEvents[0]['id']
                && $event['user_data']['fbc'] === $fbc
                && $event['user_data']['external_id'] === [hash('sha256', $visitorId)]
                && $event['user_data']['country'] === [hash('sha256', 'bd')];
        });
    }

    public function test_service_pages_send_view_content_with_the_page_view(): void
    {
        $this->configureMeta();
        $service = Service::factory()->create(['title' => ['en' => 'Building Design'], 'is_active' => true]);

        $this->get(route('services.show', $service))->assertOk();

        Http::assertSent(fn (Request $request): bool => array_column($request['data'], 'event_name') === ['PageView', 'ViewContent']
            && $request['data'][1]['custom_data'] === ['content_name' => 'Building Design', 'content_category' => 'Service']);
    }

    public function test_a_lead_carries_every_identifier_and_later_pages_keep_them(): void
    {
        $this->configureMeta();

        $component = Livewire::withCookies(['fdh_vid' => str_repeat('ab', 16), '_fbc' => 'fb.1.1792000000000.IwAR0abc'])->test(InquiryForm::class);
        $this->travel(5)->seconds();
        $component->set('name', 'Karim Uddin Ahmed')->set('phone', '01712345678')->set('email', 'Karim@Example.com')->set('location', 'Mahiganj, Rangpur')
            ->call('submit')->assertSet('sent', true);

        Http::assertSent(function (Request $request): bool {
            $user = $request['data'][0]['user_data'];

            return $request['data'][0]['event_name'] === 'Lead'
                && $user['em'] === [hash('sha256', 'karim@example.com')]
                && $user['fn'] === [hash('sha256', 'karim')]
                && $user['ln'] === [hash('sha256', 'ahmed')]
                && $user['ct'] === [hash('sha256', 'rangpur')]
                && $user['external_id'] === [hash('sha256', str_repeat('ab', 16))]
                && $user['fbc'] === 'fb.1.1792000000000.IwAR0abc';
        });

        // The next page the visitor opens is matched on the same details, in the browser too.
        $this->get('/')->assertSee(hash('sha256', '8801712345678'));
    }

    public function test_call_and_whatsapp_taps_are_sent_as_contact(): void
    {
        $this->configureMeta();

        $this->postJson(route('meta.contact'), ['event_id' => 'co-'.str_repeat('a1', 10), 'channel' => 'whatsapp', 'url' => url('/ct')])->assertNoContent();
        $this->postJson(route('meta.contact'), ['event_id' => 'bad', 'channel' => 'whatsapp', 'url' => url('/')])->assertUnprocessable();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request['data'][0]['event_name'] === 'Contact'
            && $request['data'][0]['event_id'] === 'co-'.str_repeat('a1', 10)
            && $request['data'][0]['custom_data']['content_name'] === 'whatsapp');
    }

    public function test_bots_and_sites_without_a_pixel_are_not_tracked(): void
    {
        Http::fake();
        $this->withoutDefer();

        $this->assertNull($this->get('/?fbclid=IwAR0abcDEF123_xyz')->getCookie('_fbc', false));

        $this->configureMeta();
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')->get('/')->assertOk();

        Http::assertNothingSent();
    }

    private function configureMeta(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $this->withoutDefer();
        Setting::write([
            'meta_pixel_id' => '1234567890123',
            'meta_capi_token' => Crypt::encryptString('test-token'),
            'notify_email' => '',
        ]);
    }
}
