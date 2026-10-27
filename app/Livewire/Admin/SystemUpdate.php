<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Support\Updater;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Throwable;

#[Title('System update')]
class SystemUpdate extends AdminComponent
{
    protected ?string $permission = 'system.update';

    /** @var list<string> */
    public array $log = [];

    public ?string $error = null;

    public function boot(): void
    {
        parent::boot();

        // Updates are the developer's job: only the hidden support account sees this page.
        abort_unless(auth()->user()->is_hidden, 404);
    }

    public function check(Updater $updater): void
    {
        $this->reset('log', 'error');
        $latest = $updater->latest(fresh: true);
        $current = $updater->current();

        match (true) {
            $latest === null => $this->toast('Could not reach GitHub. Try again in a minute.', 'error'),
            ($current['sha'] ?? null) === $latest['sha'] => $this->toast('Checked — the website is up to date.'),
            default => $this->toast('A new version is ready to install.', 'info'),
        };
    }

    public function install(Updater $updater): void
    {
        $this->reset('log', 'error');
        $before = $updater->current()['sha'] ?? 'unknown';

        try {
            $this->log = $updater->update();
            $after = $updater->current()['sha'] ?? 'unknown';
            ActivityLog::record('system.updated', 'Website updated from '.substr($before, 0, 7).' to '.substr($after, 0, 7));
            $this->toast('Website updated to '.substr($after, 0, 7).'.');
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            ActivityLog::record('system.update_failed', $e->getMessage());
            $this->toast('Update failed: '.$e->getMessage(), 'error');
        }
    }

    public function render(Updater $updater): View
    {
        $current = $updater->current();
        $latest = $updater->isSourceCheckout() ? null : $updater->latest();
        $behind = $current && $latest && $current['sha'] !== $latest['sha'];

        return view('livewire.admin.system-update', [
            'sourceCheckout' => $updater->isSourceCheckout(),
            'current' => $current,
            'latest' => $latest,
            'behind' => $behind || ($current === null && $latest !== null),
            'changes' => $behind ? $updater->changes($current['sha'], $latest['sha']) : [],
            'repo' => $updater->repository(),
            'branch' => $updater->branch(),
        ]);
    }
}
