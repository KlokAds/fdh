<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Support\PushNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function subscription(string $endpoint = 'https://fcm.googleapis.com/fcm/send/abc123'): array
    {
        return [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QTpQtUbVlUls0VJXg7A8u-Ts1XbjhazAkj7I99e8QcYP7DkM', 'auth' => 'tBHItJI5svbpez7KI4CCXg'],
            'contentEncoding' => 'aes128gcm',
        ];
    }

    public function test_admin_can_save_a_device_and_keys_are_encrypted(): void
    {
        $admin = $this->admin('Sales');

        $this->actingAs($admin)->postJson(route('admin.push.subscribe'), $this->subscription())->assertOk();

        $saved = PushSubscription::query()->firstOrFail();
        $this->assertSame($admin->id, $saved->user_id);
        $this->assertSame('https://fcm.googleapis.com/fcm/send/abc123', $saved->endpoint);
        $this->assertStringNotContainsString('fcm.googleapis.com', $saved->getRawOriginal('endpoint'));
    }

    public function test_one_admin_cannot_take_over_another_admins_device(): void
    {
        $first = $this->admin('Sales');
        $second = $this->admin('Sales');

        $this->actingAs($first)->postJson(route('admin.push.subscribe'), $this->subscription())->assertOk();
        $this->actingAs($second)->postJson(route('admin.push.subscribe'), $this->subscription())->assertStatus(409);

        $this->assertSame($first->id, PushSubscription::query()->firstOrFail()->user_id);
    }

    public function test_only_https_endpoints_and_permitted_admins_are_accepted(): void
    {
        $this->actingAs($this->admin('Sales'))
            ->postJson(route('admin.push.subscribe'), $this->subscription('http://evil.example/push'))
            ->assertUnprocessable();

        $this->actingAs($this->admin('Editor'))
            ->postJson(route('admin.push.subscribe'), $this->subscription())
            ->assertForbidden();
    }

    public function test_guests_cannot_register_devices(): void
    {
        $this->postJson(route('admin.push.subscribe'), $this->subscription())->assertUnauthorized();
        $this->assertSame(0, PushSubscription::query()->count());
    }

    public function test_service_worker_and_manifest_are_served_from_the_admin_path(): void
    {
        $this->get(route('admin.push.worker'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertSee('showNotification', false);

        $this->get(route('admin.manifest'))->assertOk()->assertJsonPath('display', 'standalone');
    }

    public function test_nothing_is_sent_when_push_keys_are_missing(): void
    {
        config(['fdh.vapid.public_key' => null, 'fdh.vapid.private_key' => null]);

        $this->assertFalse(app(PushNotifier::class)->isConfigured());
        $this->assertSame(0, app(PushNotifier::class)->sendToUser($this->admin(), ['title' => 'x', 'body' => 'y', 'url' => '/']));
    }
}
