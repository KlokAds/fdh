<?php

namespace App\Livewire\Site;

use App\Models\Faq as FaqModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Faq extends Component
{
    public function render(): View
    {
        return view('livewire.site.faq', [
            'faqs' => FaqModel::query()->active()->get(),
        ])->title(__('FAQ').' — '.site('site_name'));
    }
}
