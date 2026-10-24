<?php

namespace App\Livewire\Site;

use App\Models\Faq;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Home extends Component
{
    public function render(): View
    {
        return view('livewire.site.home', [
            'services' => Service::query()->active()->get(),
            'projects' => Project::query()->published()->limit(6)->get(),
            'members' => TeamMember::query()->active()->limit(4)->get(),
            'testimonials' => Testimonial::query()->active()->limit(6)->get(),
            'faqs' => Faq::query()->active()->limit(5)->get(),
        ])->title(site('meta_title'))->layoutData(['heroPreload' => Setting::raw()['hero_image'] ?? null]);
    }
}
