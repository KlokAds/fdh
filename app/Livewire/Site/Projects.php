<?php

namespace App\Livewire\Site;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.site')]
class Projects extends Component
{
    #[Url(as: 'type')]
    public string $category = '';

    public function filter(string $category): void
    {
        $this->category = in_array($category, Project::CATEGORIES, true) ? $category : '';
    }

    public function render(): View
    {
        $category = in_array($this->category, Project::CATEGORIES, true) ? $this->category : '';

        return view('livewire.site.projects', [
            'projects' => Project::query()->published()->when($category !== '', fn ($q) => $q->where('category', $category))->get(),
            'categories' => Project::query()->where('is_published', true)->distinct()->orderBy('category')->pluck('category'),
            'active' => $category,
        ])->title(__('Our Projects').' — '.site('site_name'))->layoutData(['description' => __('A selection of homes, buildings and interiors we have designed and supervised.')]);
    }
}
