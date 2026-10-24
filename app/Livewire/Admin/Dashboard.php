<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Service;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;

#[Title('Dashboard')]
class Dashboard extends AdminComponent
{
    public function render(): View
    {
        $user = auth()->user();
        $canSeeInquiries = $user->can('inquiries.view');

        return view('livewire.admin.dashboard', [
            'canSeeInquiries' => $canSeeInquiries,
            'newInquiries' => $canSeeInquiries ? Inquiry::query()->whereNull('read_at')->count() : null,
            'myInquiries' => $canSeeInquiries ? Inquiry::query()->where('assigned_to', $user->id)->whereNotIn('status', ['closed', 'spam'])->count() : null,
            'totalInquiries' => $canSeeInquiries ? Inquiry::query()->count() : null,
            'projects' => Project::query()->count(),
            'services' => Service::query()->where('is_active', true)->count(),
            'team' => TeamMember::query()->where('is_active', true)->count(),
            'latest' => $canSeeInquiries ? Inquiry::query()->latest()->limit(6)->get() : collect(),
            'failedLogins' => $user->can('logs.view')
                ? ActivityLog::query()->whereIn('action', ['login.failed', '2fa.failed', 'login.locked'])->where('created_at', '>=', now()->subDay())->count()
                : null,
        ]);
    }
}
