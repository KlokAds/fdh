<?php

namespace App\Livewire\Admin;

use App\Models\PushSubscription;
use App\Support\PushNotifier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

#[Title('Notifications')]
class Notifications extends AdminComponent
{
    protected ?string $permission = 'inquiries.view';

    public ?string $result = null;

    /**
     * Called from the browser after it subscribes, so the device list refreshes.
     */
    #[On('push-changed')]
    public function refreshDevices(): void
    {
        $this->result = null;
    }

    public function sendTest(PushNotifier $notifier): void
    {
        $sent = $notifier->sendToUser(auth()->user(), [
            'title' => 'Test notification',
            'body' => 'Inquiry alerts are working on this device.',
            'url' => route('admin.notifications'),
            'tag' => 'test',
        ]);

        $this->result = $sent > 0
            ? "Sent to {$sent} device(s). It should appear in a few seconds."
            : 'No device received it. Turn on notifications on this device first.';
    }

    public function removeDevice(int $id): void
    {
        PushSubscription::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->toast('Device removed. It will no longer get alerts.');
    }

    public function render(PushNotifier $notifier): View
    {
        return view('livewire.admin.notifications', [
            'configured' => $notifier->isConfigured(),
            'publicKey' => (string) config('fdh.vapid.public_key'),
            'devices' => PushSubscription::query()->where('user_id', auth()->id())->latest()->get(['id', 'device', 'last_used_at', 'created_at']),
        ]);
    }
}
