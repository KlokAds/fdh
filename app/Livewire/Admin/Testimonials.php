<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\EditsTranslations;
use App\Models\ActivityLog;
use App\Models\Testimonial;
use App\Support\ImageStore;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('Testimonials')]
class Testimonials extends AdminComponent
{
    use EditsTranslations, WithFileUploads;

    protected ?string $permission = 'testimonials.manage';

    private const TRANSLATABLE = ['subtitle', 'quote'];

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $name = '';

    public int $rating = 5;

    public int $sort = 0;

    public bool $is_active = true;

    /** @var TemporaryUploadedFile|null */
    public $photo = null;

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
        $testimonial = Testimonial::query()->findOrFail($id);
        $this->resetForm();
        $this->editingId = $testimonial->id;
        $this->form = $this->translationsFrom($testimonial, self::TRANSLATABLE);
        $this->name = $testimonial->name;
        $this->rating = $testimonial->rating;
        $this->sort = $testimonial->sort;
        $this->is_active = $testimonial->is_active;
        $this->showForm = true;
    }

    public function save(ImageStore $images): void
    {
        $this->validate(array_merge(
            $this->translationRules('subtitle', false, 120),
            $this->translationRules('quote', true, 1000),
            [
                'name' => 'required|string|max:100',
                'rating' => 'required|integer|between:1,5',
                'sort' => 'integer|min:0|max:999',
                'is_active' => 'boolean',
                'photo' => array_merge(['nullable'], ImageStore::rules()),
            ],
        ), [], $this->translationAttributes('quote', 'Review'));

        $testimonial = $this->editingId ? Testimonial::query()->findOrFail($this->editingId) : new Testimonial;
        foreach (self::TRANSLATABLE as $field) {
            $testimonial->setTranslations($field, $this->cleanTranslations($this->form[$field] ?? []));
        }

        if ($this->photo) {
            $images->delete($testimonial->photo);
            $testimonial->photo = $images->store($this->photo, 'testimonials', 400);
        }

        $testimonial->fill([
            'name' => strip_tags($this->name),
            'rating' => $this->rating,
            'sort' => $this->sort,
            'is_active' => $this->is_active,
        ])->save();

        ActivityLog::record('testimonial.saved', 'Saved testimonial from '.$testimonial->name);
        $this->resetForm();
        session()->flash('status', 'Testimonial saved.');
    }

    public function delete(int $id, ImageStore $images): void
    {
        $testimonial = Testimonial::query()->findOrFail($id);
        $images->delete($testimonial->photo);
        ActivityLog::record('testimonial.deleted', 'Deleted testimonial from '.$testimonial->name);
        $testimonial->delete();
        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'showForm', 'name', 'rating', 'sort', 'is_active', 'photo');
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.admin.testimonials', [
            'testimonials' => Testimonial::query()->orderBy('sort')->latest('id')->get(),
        ]);
    }
}
