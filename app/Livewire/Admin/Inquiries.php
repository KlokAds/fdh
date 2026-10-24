<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Title('Inquiries')]
class Inquiries extends AdminComponent
{
    use WithPagination;

    protected ?string $permission = 'inquiries.view';

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    #[Locked]
    public ?int $openId = null;

    public string $note = '';

    public string $assignee = '';

    /**
     * Supports links from the notification email: /inquiries?open=ID
     */
    public function mount(): void
    {
        $id = request()->integer('open');

        if ($id > 0 && Inquiry::query()->whereKey($id)->exists()) {
            $this->open($id);
        }
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function open(int $id): void
    {
        $inquiry = Inquiry::query()->findOrFail($id);
        $this->openId = $inquiry->id;
        $this->note = (string) $inquiry->note;
        $this->assignee = (string) $inquiry->assigned_to;
        $inquiry->markAsRead('admin');

        if ($inquiry->status === 'new' && auth()->user()->can('inquiries.manage')) {
            $inquiry->update(['status' => 'contacted']);
        }
    }

    public function close(): void
    {
        $this->openId = null;
        $this->reset('note', 'assignee');
    }

    public function setStatus(string $status): void
    {
        $this->authorizeAbility('inquiries.manage');
        validator(['status' => $status], ['status' => ['required', Rule::in(array_keys(Inquiry::STATUSES))]])->validate();

        Inquiry::query()->findOrFail($this->openId)->update(['status' => $status]);
    }

    public function saveNote(): void
    {
        $this->authorizeAbility('inquiries.manage');
        $this->validate([
            'note' => 'nullable|string|max:2000',
            'assignee' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        Inquiry::query()->findOrFail($this->openId)->update([
            'note' => strip_tags($this->note),
            'assigned_to' => $this->assignee !== '' ? (int) $this->assignee : null,
        ]);
        session()->flash('status', 'Saved.');
    }

    public function delete(): void
    {
        $this->authorizeAbility('inquiries.delete');
        $inquiry = Inquiry::query()->findOrFail($this->openId);
        ActivityLog::record('inquiry.deleted', "Deleted inquiry #{$inquiry->id} from {$inquiry->name}");
        $inquiry->delete();
        $this->close();
    }

    public function render(): View
    {
        $inquiries = Inquiry::query()
            ->with('assignee:id,name')
            ->when(array_key_exists($this->status, Inquiry::STATUSES), fn ($q) => $q->where('status', $this->status))
            ->when($this->status === 'mine', fn ($q) => $q->where('assigned_to', auth()->id()))
            ->when($this->status === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when(trim($this->search) !== '', function ($q): void {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim($this->search)).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('location', 'like', $term));
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.inquiries', [
            'inquiries' => $inquiries,
            'current' => $this->openId ? Inquiry::query()->with('assignee:id,name')->find($this->openId) : null,
            'staff' => User::query()->where('is_active', true)->where('is_hidden', false)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
