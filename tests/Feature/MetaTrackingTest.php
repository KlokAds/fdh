<?php

namespace Tests\Feature;

use App\Livewire\Admin\Content;
use App\Livewire\Site\InquiryForm;
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
}
