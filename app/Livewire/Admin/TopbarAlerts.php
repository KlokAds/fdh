<?php

namespace App\Livewire\Admin;

use App\Models\Inquiry;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Bell in the admin top bar: unread inquiries, refreshed every minute.
 */
class TopbarAlerts extends Component
{
    public function render(): View
    {
        $user = auth()->user();
        // Nothing is shown until the account has finished its security setup.
        $canView = $user !== null && ! $user->needsSecuritySetup() && $user->can('inquiries.view');

        return view('livewire.admin.topbar-alerts', [
            'canView' => $canView,
            'unreadCount' => $canView ? Inquiry::query()->whereNull('read_at')->count() : 0,
            'latest' => $canView ? Inquiry::query()->whereNull('read_at')->latest('id')->limit(5)->get(['id', 'name', 'service', 'created_at']) : collect(),
        ]);
    }
}
