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

    public function check(Updater $updater): void
    {
        $updater->latest(fresh: true);
        $this->reset('log', 'error');
    }

    public function install(Updater $updater): void
    {
        $this->reset('log', 'error');
        $before = $updater->current()['sha'] ?? 'unknown';

        try {
            $this->log = $updater->update();
            $after = $updater->current()['sha'] ?? 'unknown';
            ActivityLog::record('system.updated', 'Website updated from '.substr($before, 0, 7).' to '.substr($after, 0, 7));
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            ActivityLog::record('system.update_failed', $e->getMessage());
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
