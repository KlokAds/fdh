<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\EditsTranslations;
use App\Models\ActivityLog;
use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;

#[Title('FAQs')]
class Faqs extends AdminComponent
{
    use EditsTranslations;

    protected ?string $permission = 'faqs.manage';

    private const TRANSLATABLE = ['question', 'answer'];

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    /** @var array<string, mixed> */
    public array $form = [];

    public int $sort = 0;

    public bool $is_active = true;

    public function mount(): void
    {
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->sort = (int) Faq::query()->max('sort') + 1;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $faq = Faq::query()->findOrFail($id);
        $this->resetForm();
        $this->editingId = $faq->id;
        $this->form = $this->translationsFrom($faq, self::TRANSLATABLE);
        $this->sort = $faq->sort;
        $this->is_active = $faq->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate(array_merge(
            $this->translationRules('question', true, 255),
            $this->translationRules('answer', true, 3000),
            ['sort' => 'integer|min:0|max:999', 'is_active' => 'boolean'],
        ), [], array_merge($this->translationAttributes('question', 'Question'), $this->translationAttributes('answer', 'Answer')));

        $faq = $this->editingId ? Faq::query()->findOrFail($this->editingId) : new Faq;
        foreach (self::TRANSLATABLE as $field) {
            $faq->setTranslations($field, $this->cleanTranslations($this->form[$field] ?? []));
        }
        $faq->fill(['sort' => $this->sort, 'is_active' => $this->is_active])->save();

        ActivityLog::record('faq.saved', 'Saved FAQ #'.$faq->id);
        $this->resetForm();
        session()->flash('status', 'FAQ saved.');
    }

    public function delete(int $id): void
    {
        Faq::query()->findOrFail($id)->delete();
        ActivityLog::record('faq.deleted', "Deleted FAQ #{$id}");
        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'showForm', 'sort', 'is_active');
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.admin.faqs', [
            'faqs' => Faq::query()->orderBy('sort')->orderBy('id')->get(),
        ]);
    }
}
