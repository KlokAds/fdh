<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\DiscardsUploads;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Support\ImageStore;
use App\Support\RichText;
use App\Support\SampleContent;
use App\Support\SiteContent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('Website content & settings')]
class Content extends AdminComponent
{
    use DiscardsUploads, WithFileUploads;

    #[Url]
    public string $tab = '';

    /** @var array<string, mixed> */
    public array $values = [];

    /** @var array<string, TemporaryUploadedFile|null> */
    public array $uploads = [];

    /**
     * New values typed for secret fields (API tokens). Saved secrets are never sent to the browser.
     *
     * @var array<string, string>
     */
    public array $secrets = [];

    public function mount(): void
    {
        abort_if($this->allowedGroups() === [], 403);

        if (! array_key_exists($this->tab, $this->allowedGroups())) {
            $this->tab = array_key_first($this->allowedGroups());
        }

        $this->values = $this->editableValues();
    }

    /**
     * Tabs the current user may edit.
     *
     * @return array<string, array{label: string, permission: string, fields: array<string, array<string, mixed>>}>
     */
    public function allowedGroups(): array
    {
        return array_filter(SiteContent::groups(), fn (array $group): bool => auth()->user()->can($group['permission']));
    }

    public function save(ImageStore $images): void
    {
        $groups = $this->allowedGroups();
        abort_unless(isset($groups[$this->tab]), 403);

        $fields = $groups[$this->tab]['fields'];
        $locales = array_keys(config('fdh.locales'));
        $rules = [];
        $attributes = [];

        foreach ($fields as $key => $field) {
            if (! empty($field['translatable'])) {
                foreach ($locales as $index => $locale) {
                    $rules["values.{$key}.{$locale}"] = ($index === 0 && ($field['default']['en'] ?? '') !== '' ? 'required' : 'nullable').'|string|max:'.match ($field['type']) {
                        'rich' => 60000, 'textarea' => 3000, default => 255
                    };
                    $attributes["values.{$key}.{$locale}"] = $field['label']." ({$locale})";
                }
            } elseif ($field['type'] === 'secret') {
                $rules["secrets.{$key}"] = 'nullable|string|max:1000';
                $attributes["secrets.{$key}"] = $field['label'];
            } elseif ($field['type'] === 'image') {
                $rules["uploads.{$key}"] = array_merge(['nullable'], ImageStore::rules(64, 64));
                $attributes["uploads.{$key}"] = $field['label'];
            } else {
                $rules["values.{$key}"] = match ($field['type']) {
                    'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
                    'select' => ['required', Rule::in(array_keys($field['options']))],
                    default => $field['rules'] ?? 'nullable|string|max:255',
                };
                $attributes["values.{$key}"] = $field['label'];
            }
        }

        $this->validate($rules, [], $attributes);

        $toSave = [];
        foreach ($fields as $key => $field) {
            if (! empty($field['translatable'])) {
                $toSave[$key] = array_filter(
                    array_map(fn ($value): string => $field['type'] === 'rich' ? RichText::clean((string) $value) : trim(strip_tags((string) $value)), array_intersect_key((array) ($this->values[$key] ?? []), array_flip($locales))),
                    fn (string $value): bool => $value !== '',
                );
            } elseif ($field['type'] === 'secret') {
                if (trim($this->secrets[$key] ?? '') !== '') {
                    $toSave[$key] = Crypt::encryptString(trim($this->secrets[$key]));
                }
            } elseif ($field['type'] === 'image') {
                if (! empty($this->uploads[$key])) {
                    $images->delete(self::storedImage($key));
                    $toSave[$key] = $images->store($this->uploads[$key], 'site', $key === 'logo' ? 600 : 1600);
                    app(SampleContent::class)->forgetImage($key);
                }
            } else {
                $toSave[$key] = trim((string) ($this->values[$key] ?? ''));
            }
        }

        Setting::write($toSave);
        $this->values = $this->editableValues();
        $this->uploads = [];
        $this->secrets = [];

        ActivityLog::record('content.updated', 'Updated: '.$groups[$this->tab]['label']);
        session()->flash('status', $groups[$this->tab]['label'].' saved.');
    }

    public function removeImage(string $key, ImageStore $images): void
    {
        $groups = $this->allowedGroups();
        abort_unless(isset($groups[$this->tab]['fields'][$key]) && $groups[$this->tab]['fields'][$key]['type'] === 'image', 403);

        $images->delete(self::storedImage($key));
        Setting::write([$key => '']);
        app(SampleContent::class)->forgetImage($key);
        $this->values = $this->editableValues();
        $this->toast('Image removed — the website uses its default now.');
    }

    /**
     * Path of the image currently saved for a setting (read on the server, never from the form).
     */
    private static function storedImage(string $key): ?string
    {
        $path = Setting::raw()[$key] ?? null;

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function clearSecret(string $key): void
    {
        $groups = $this->allowedGroups();
        abort_unless(($groups[$this->tab]['fields'][$key]['type'] ?? null) === 'secret', 403);

        Setting::write([$key => '']);
        ActivityLog::record('content.updated', "Removed secret: {$key}");
        $this->toast('Saved value cleared.');
    }

    /**
     * Setting values safe to expose to the editor: secret fields are blanked out.
     *
     * @return array<string, mixed>
     */
    private function editableValues(): array
    {
        $values = Setting::raw();

        foreach (SiteContent::fields() as $key => $field) {
            if ($field['type'] === 'secret') {
                $values[$key] = '';
            }
        }

        return $values;
    }

    public function render(): View
    {
        $groups = $this->allowedGroups();

        return view('livewire.admin.content', [
            'groups' => $groups,
            'fields' => $groups[$this->tab]['fields'] ?? [],
            'samples' => app(SampleContent::class),
            'savedSecrets' => array_map(fn ($value): bool => is_string($value) && $value !== '', array_intersect_key(Setting::raw(), array_filter(SiteContent::fields(), fn (array $field): bool => $field['type'] === 'secret'))),
        ]);
    }
}
