<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Security Log')]
class Activity extends AdminComponent
{
    use WithPagination;

    protected ?string $permission = 'logs.view';

    public function render(): View
    {
        return view('livewire.admin.activity', [
            'logs' => ActivityLog::query()->with('user:id,name')->latest('id')->paginate(30),
        ]);
    }
}
