<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\DiscardsUploads;
use App\Livewire\Admin\Concerns\EditsTranslations;
use App\Livewire\Admin\Concerns\PaginatesWithinRange;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Support\ImageStore;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Projects')]
class Projects extends AdminComponent
{
    use DiscardsUploads, EditsTranslations, PaginatesWithinRange, WithFileUploads, WithPagination;

    protected ?string $permission = 'projects.manage';

    private const TRANSLATABLE = ['title', 'location', 'description'];

    /** @var list<string> */
    protected array $uploadFields = ['cover', 'photos'];

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $slug = '';

    public string $category = 'Residential';

    public string $year = '';

    public string $status = 'completed';

    public string $floors = '';

    public string $land_katha = '';

    public string $floor_area_sqft = '';

    public bool $is_featured = false;

    public bool $is_published = true;

    public int $sort = 0;

    /** @var TemporaryUploadedFile|null */
    public $cover = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $photos = [];

    public function mount(): void
    {
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $project = Project::query()->findOrFail($id);
        $this->resetForm();
        $this->editingId = $project->id;
        $this->form = $this->translationsFrom($project, self::TRANSLATABLE);
        $this->slug = $project->slug;
        $this->category = $project->category;
        $this->year = (string) $project->year;
        $this->status = $project->status;
        $this->floors = (string) $project->floors;
        $this->land_katha = $project->land_katha !== null ? rtrim(rtrim((string) $project->land_katha, '0'), '.') : '';
        $this->floor_area_sqft = (string) $project->floor_area_sqft;
        $this->is_featured = $project->is_featured;
        $this->is_published = $project->is_published;
        $this->sort = $project->sort;
        $this->showForm = true;
    }

    public function save(ImageStore $images): void
    {
        $this->slug = Str::slug($this->slug !== '' ? $this->slug : (string) ($this->form['title']['en'] ?? ''));

        $this->validate(array_merge(
            $this->translationRules('title', true, 150),
            $this->translationRules('location', false, 150),
            $this->translationRules('description', false, 60000),
            [
                'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('projects', 'slug')->ignore($this->editingId)],
                'category' => ['required', Rule::in(Project::CATEGORIES)],
                'year' => 'nullable|digits:4',
                'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
                'floors' => 'nullable|integer|min:1|max:60',
                'land_katha' => 'nullable|numeric|min:0.5|max:9999',
                'floor_area_sqft' => 'nullable|integer|min:100|max:5000000',
                'is_featured' => 'boolean',
                'is_published' => 'boolean',
                'sort' => 'integer|min:0|max:999',
                'cover' => array_merge([$this->editingId ? 'nullable' : 'required'], ImageStore::rules()),
                // Up to 12 per upload and 24 in the gallery overall.
                'photos' => 'array|max:'.min(12, max(0, 24 - count($this->editingId ? (Project::query()->find($this->editingId)?->gallery ?? []) : []))),
                'photos.*' => ImageStore::rules(),
            ],
        ), [], array_merge($this->translationAttributes('title', 'Title'), ['cover' => 'cover photo', 'photos.*' => 'gallery photo']));

        $project = $this->editingId ? Project::query()->findOrFail($this->editingId) : new Project;

        foreach (self::TRANSLATABLE as $field) {
            $project->setTranslations($field, $this->cleanTranslations($this->form[$field] ?? [], rich: $field === 'description'));
        }

        if ($this->cover) {
            $images->delete($project->cover_image);
            $project->cover_image = $images->store($this->cover, 'projects');
        }

        // Once an admin has edited a sample project it is theirs: "Remove samples" leaves it alone.
        $project->is_sample = false;

        $gallery = $project->gallery ?? [];
        foreach ($this->photos as $photo) {
            $gallery[] = $images->store($photo, 'projects');
        }

        $project->fill([
            'slug' => $this->slug,
            'category' => $this->category,
            'year' => $this->year !== '' ? $this->year : null,
            'status' => $this->status,
            'floors' => $this->floors !== '' ? (int) $this->floors : null,
            'land_katha' => $this->land_katha !== '' ? $this->land_katha : null,
            'floor_area_sqft' => $this->floor_area_sqft !== '' ? (int) $this->floor_area_sqft : null,
            'is_featured' => $this->is_featured,
            'is_published' => $this->is_published,
            'sort' => $this->sort,
            'gallery' => $gallery,
        ])->save();

        ActivityLog::record('project.saved', 'Saved project: '.$project->getTranslation('title', 'en'));
        $this->resetForm();
        session()->flash('status', 'Project saved.');
    }

    public function removePhoto(int $index, ImageStore $images): void
    {
        $project = Project::query()->findOrFail($this->editingId);
        $gallery = $project->gallery ?? [];

        if (array_key_exists($index, $gallery)) {
            $images->delete($gallery[$index]);
            unset($gallery[$index]);
            $project->update(['gallery' => array_values($gallery)]);
        }
        $this->toast('Photo removed from the gallery.');
    }

    public function delete(int $id, ImageStore $images): void
    {
        $project = Project::query()->findOrFail($id);
        $images->delete($project->cover_image);
        foreach ($project->gallery ?? [] as $path) {
            $images->delete($path);
        }
        ActivityLog::record('project.deleted', 'Deleted project: '.$project->getTranslation('title', 'en'));
        $project->delete();
        $this->resetForm();
        $this->toast('Project deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'showForm', 'slug', 'category', 'year', 'status', 'floors', 'land_katha', 'floor_area_sqft', 'is_featured', 'is_published', 'sort', 'cover', 'photos');
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.admin.projects', [
            'projects' => $this->paginateWithinRange(Project::query()->orderByDesc('is_featured')->orderBy('sort')->latest('id'), 12),
            'current' => $this->editingId ? Project::query()->find($this->editingId) : null,
            'categories' => Project::CATEGORIES,
        ]);
    }
}
