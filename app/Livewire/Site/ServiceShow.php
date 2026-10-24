<?php

namespace App\Livewire\Site;

use App\Models\Service;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.site')]
class ServiceShow extends Component
{
    #[Locked]
    public Service $service;

    public function mount(Service $service): void
    {
        abort_unless($service->is_active, 404);
        $this->service = $service;
    }

    public function render(): View
    {
        return view('livewire.site.service-show', [
            'others' => Service::query()->active()->whereKeyNot($this->service->id)->get(),
        ])->title($this->service->title.' — '.site('site_name'))->layoutData(['description' => $this->service->summary]);
    }
}
