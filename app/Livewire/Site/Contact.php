<?php

namespace App\Livewire\Site;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.site')]
class Contact extends Component
{
    #[Url]
    public string $service = '';

    public function render(): View
    {
        return view('livewire.site.contact')->title(__('Contact Us').' — '.site('site_name'))->layoutData(['description' => site('contact_sub').' '.site('phone')]);
    }
}
