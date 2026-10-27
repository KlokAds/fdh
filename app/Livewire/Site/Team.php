<?php

namespace App\Livewire\Site;

use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Team extends Component
{
    public function render(): View
    {
        return view('livewire.site.team', [
            'members' => TeamMember::query()->active()->get(),
        ])->title(__('Our Team').' — '.site('site_name'));
    }
}
