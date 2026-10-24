<?php

namespace App\Livewire\Site;

use App\Models\Project;
use App\Support\RichText;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.site')]
class ProjectShow extends Component
{
    #[Locked]
    public Project $project;

    public function mount(Project $project): void
    {
        abort_unless($project->is_published, 404);
        $this->project = $project;
    }

    public function render(): View
    {
        return view('livewire.site.project-show', [
            'related' => Project::query()->published()->whereKeyNot($this->project->id)->limit(3)->get(),
        ])->title($this->project->title.' — '.site('site_name'))->layoutData([
            'description' => RichText::plain($this->project->description) ?: collect([__($this->project->category), $this->project->location, $this->project->year])->filter()->implode(' · '),
            'image' => $this->project->coverUrl(),
            'ogType' => 'article',
        ]);
    }
}
