<?php

namespace App\Livewire\Site;

use App\Models\Service;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Services extends Component
{
    public function render(): View
    {
        return view('livewire.site.services', [
            'services' => Service::query()->active()->get(),
        ])->title(__('Our Services').' — '.site('site_name'))->layoutData(['description' => site('services_sub')]);
    }
}
