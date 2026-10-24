<?php

namespace App\Livewire\Site;

use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class About extends Component
{
    public function render(): View
    {
        return view('livewire.site.about', [
            'members' => TeamMember::query()->active()->get(),
        ])->title(__('About Us').' — '.site('site_name'))->layoutData(['description' => site('about_lead'), 'image' => Setting::raw()['about_image'] ? Setting::imageUrl('about_image', 'images/fdh-logo.jpg') : null]);
    }
}
