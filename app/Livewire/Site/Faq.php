<?php

namespace App\Livewire\Site;

use App\Models\Faq as FaqModel;
use App\Support\RichText;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Faq extends Component
{
    public function render(): View
    {
        $faqs = FaqModel::query()->active()->get();

        // FAQPage structured data lets Google show the answers directly in search results.
        return view('livewire.site.faq', ['faqs' => $faqs])->title(__('FAQ').' — '.site('site_name'))->layoutData([
            'description' => $faqs->pluck('question')->take(3)->implode(' · '),
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqs->map(fn (FaqModel $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => RichText::plain($faq->answer)],
                ])->values()->all(),
            ],
        ]);
    }
}
