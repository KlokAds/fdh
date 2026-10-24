<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\DiscardsUploads;
use App\Livewire\Admin\Concerns\EditsTranslations;
use App\Models\ActivityLog;
use App\Models\TeamMember;
use App\Support\ImageStore;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Team members')]
class Team extends AdminComponent
{
    use DiscardsUploads, EditsTranslations, WithFileUploads, WithPagination;

    protected ?string $permission = 'team.manage';

    private const TRANSLATABLE = ['name', 'designation', 'bio'];

    /** @var list<string> */
    protected array $uploadFields = ['photo'];

    #[Locked]
    public ?int $editingId = null;

    public bool $showForm = false;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $phone = '';

    public string $email = '';

    public string $facebook = '';

    public string $linkedin = '';

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
        $this->sort = (int) TeamMember::query()->max('sort') + 1;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $member = TeamMember::query()->findOrFail($id);
        $this->resetForm();
        $this->editingId = $member->id;
        $this->form = $this->translationsFrom($member, self::TRANSLATABLE);
        $this->phone = (string) $member->phone;
        $this->email = (string) $member->email;
        $this->facebook = (string) $member->facebook;
        $this->linkedin = (string) $member->linkedin;
        $this->sort = $member->sort;
        $this->is_active = $member->is_active;
        $this->showForm = true;
    }

    public function save(ImageStore $images): void
    {
        $this->validate(array_merge(
            $this->translationRules('name', true, 100),
            $this->translationRules('designation', true, 120),
            $this->translationRules('bio', false, 1000),
            [
                'phone' => 'nullable|string|max:30',
                'email' => 'nullable|email|max:150',
                'facebook' => 'nullable|url:https|max:255',
                'linkedin' => 'nullable|url:https|max:255',
                'sort' => 'integer|min:0|max:999',
                'is_active' => 'boolean',
                'photo' => array_merge(['nullable'], ImageStore::rules()),
            ],
        ), [], array_merge($this->translationAttributes('name', 'Name'), $this->translationAttributes('designation', 'Designation')));

        $member = $this->editingId ? TeamMember::query()->findOrFail($this->editingId) : new TeamMember;
        foreach (self::TRANSLATABLE as $field) {
            $member->setTranslations($field, $this->cleanTranslations($this->form[$field] ?? []));
        }

        if ($this->photo) {
            $images->delete($member->photo);
            $member->photo = $images->store($this->photo, 'team', 800);
        }

        $member->fill([
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'facebook' => $this->facebook ?: null,
            'linkedin' => $this->linkedin ?: null,
            'sort' => $this->sort,
            'is_active' => $this->is_active,
        ])->save();

        ActivityLog::record('team.saved', 'Saved team member: '.$member->getTranslation('name', 'en'));
        $this->resetForm();
        session()->flash('status', 'Team member saved.');
    }

    public function removePhoto(ImageStore $images): void
    {
        $member = TeamMember::query()->findOrFail($this->editingId);
        $images->delete($member->photo);
        $member->update(['photo' => null]);
    }

    public function delete(int $id, ImageStore $images): void
    {
        $member = TeamMember::query()->findOrFail($id);
        $images->delete($member->photo);
        ActivityLog::record('team.deleted', 'Deleted team member: '.$member->getTranslation('name', 'en'));
        $member->delete();
        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'showForm', 'phone', 'email', 'facebook', 'linkedin', 'sort', 'is_active', 'photo');
        $this->form = $this->blankTranslations(self::TRANSLATABLE);
        $this->resetErrorBag();
    }

    public function render(): View
    {
        return view('livewire.admin.team', [
            'members' => TeamMember::query()->orderBy('sort')->orderBy('id')->paginate(16),
            'current' => $this->editingId ? TeamMember::query()->find($this->editingId) : null,
        ]);
    }
}
