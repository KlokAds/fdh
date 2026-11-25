<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Support\MediaLibrary;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use RuntimeException;

#[Title('Media library')]
class Media extends AdminComponent
{
    protected ?string $permission = 'media.manage';

    #[Url]
    public string $show = 'all';

    #[Url]
    public string $folder = '';

    public function delete(string $path, MediaLibrary $library): void
    {
        try {
            $library->delete($path);
        } catch (RuntimeException $e) {
            $this->addError('media', $e->getMessage());

            return;
        }

        ActivityLog::record('media.deleted', "Deleted unused photo {$path}");
        session()->flash('status', 'Photo deleted.');
    }

    public function deleteAllUnused(MediaLibrary $library): void
    {
        $deleted = 0;

        foreach ($library->files() as $file) {
            if ($file['usedBy'] === []) {
                $library->delete($file['path']);
                $deleted++;
            }
        }

        ActivityLog::record('media.cleaned', "Deleted {$deleted} unused photo(s)");
        session()->flash('status', $deleted ? "Deleted {$deleted} unused photo(s)." : 'There were no unused photos.');
    }

    public function render(MediaLibrary $library): View
    {
        $all = collect($library->files());
        $files = $all
            ->when($this->folder !== '' && array_key_exists($this->folder, MediaLibrary::FOLDERS), fn ($files) => $files->where('folder', $this->folder))
            ->when($this->show === 'used', fn ($files) => $files->filter(fn (array $file): bool => $file['usedBy'] !== []))
            ->when($this->show === 'unused', fn ($files) => $files->filter(fn (array $file): bool => $file['usedBy'] === []))
            ->values();

        return view('livewire.admin.media', [
            'files' => $files,
            'folders' => MediaLibrary::FOLDERS,
            'totalCount' => $all->count(),
            'totalSize' => $all->sum('size'),
            'unusedCount' => $all->filter(fn (array $file): bool => $file['usedBy'] === [])->count(),
            'unusedSize' => $all->filter(fn (array $file): bool => $file['usedBy'] === [])->sum('size'),
        ]);
    }
}
