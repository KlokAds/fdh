<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\EditsTranslations;
use App\Livewire\Admin\Concerns\PaginatesWithinRange;
use App\Models\ActivityLog;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Services')]
class Services extends AdminComponent
{
    use EditsTranslations, PaginatesWithinRange, WithPagination;

    protected ?string $permission = 'services.manage';

    private const TRANSLATABLE = ['title', 'summary', 'description', 'features'];

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $slug = '';

    public string $icon = 'building';

    public int $sort = 0;

    public bool $is_active = true;

    public function mount(): void
    {
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->sort = (int) Service::query()->max('sort') + 1;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $service = Service::query()->findOrFail($id);
        $this->resetForm();
        $this->editingId = $service->id;
        $this->form = $this->translationsFrom($service, self::TRANSLATABLE);
        $this->slug = $service->slug;
        $this->icon = $service->icon;
        $this->sort = $service->sort;
        $this->is_active = $service->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->slug = Str::slug($this->slug !== '' ? $this->slug : (string) ($this->form['title']['en'] ?? ''));

        $this->validate(array_merge(
            $this->translationRules('title', true, 120),
            $this->translationRules('summary', true, 300),
            $this->translationRules('description', false, 60000),
            $this->translationRules('features', false, 500),
            [
                'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('services', 'slug')->ignore($this->editingId)],
                'icon' => ['required', Rule::in(Service::ICONS)],
                'sort' => 'integer|min:0|max:999',
                'is_active' => 'boolean',
            ],
        ), [], array_merge($this->translationAttributes('title', 'Title'), $this->translationAttributes('summary', 'Summary')));

        $service = $this->editingId ? Service::query()->findOrFail($this->editingId) : new Service;
        foreach (self::TRANSLATABLE as $field) {
            $service->setTranslations($field, $this->cleanTranslations($this->form[$field] ?? [], rich: $field === 'description'));
        }
        $service->fill(['slug' => $this->slug, 'icon' => $this->icon, 'sort' => $this->sort, 'is_active' => $this->is_active])->save();

        ActivityLog::record('service.saved', 'Saved service: '.$service->getTranslation('title', 'en'));
        $this->resetForm();
        session()->flash('status', 'Service saved.');
    }

    public function toggle(int $id): void
    {
        $service = Service::query()->findOrFail($id);
        $service->update(['is_active' => ! $service->is_active]);
    }

    public function delete(int $id): void
    {
        $service = Service::query()->findOrFail($id);
        ActivityLog::record('service.deleted', 'Deleted service: '.$service->getTranslation('title', 'en'));
        $service->delete();
        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'showForm', 'slug', 'icon', 'sort', 'is_active');
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.admin.services', [
            'services' => $this->paginateWithinRange(Service::query()->orderBy('sort')->orderBy('id'), 15),
            'icons' => Service::ICONS,
        ]);
    }
}
