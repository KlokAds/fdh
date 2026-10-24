<?php

namespace App\Support;

use App\Models\Faq;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Every uploaded website photo, and where each one is used. Photos in use can never be deleted.
 */
class MediaLibrary
{
    /** Folders on the public disk that hold admin uploads. */
    public const FOLDERS = ['site' => 'Website', 'projects' => 'Projects', 'team' => 'Team', 'testimonials' => 'Testimonials', 'editor' => 'Text editor'];

    /**
     * @return list<array{path: string, url: string, folder: string, size: int, modified: int, usedBy: list<string>}>
     */
    public function files(): array
    {
        $disk = Storage::disk('public');
        $usage = $this->usage();
        $files = [];

        foreach (array_keys(self::FOLDERS) as $folder) {
            foreach ($disk->allFiles($folder) as $path) {
                if (! preg_match('/\.(webp|jpe?g|png|gif)$/i', $path)) {
                    continue;
                }

                $files[] = [
                    'path' => $path,
                    'url' => $disk->url($path),
                    'folder' => $folder,
                    'size' => $disk->size($path),
                    'modified' => $disk->lastModified($path),
                    'usedBy' => $usage[$path] ?? [],
                ];
            }
        }

        usort($files, fn (array $a, array $b): int => $b['modified'] <=> $a['modified']);

        return $files;
    }

    /**
     * Map of stored path => human labels of every place that shows it.
     *
     * @return array<string, list<string>>
     */
    public function usage(): array
    {
        $usage = [];
        $settings = Setting::raw();

        foreach (SiteContent::fields() as $key => $field) {
            if ($field['type'] === 'image' && ! empty($settings[$key]) && is_string($settings[$key])) {
                $usage[$settings[$key]][] = 'Settings · '.strtok($field['label'], '(—');
            }
        }

        foreach (SiteContent::fields() as $key => $field) {
            if ($field['type'] === 'rich') {
                foreach ((array) ($settings[$key] ?? []) as $html) {
                    foreach (RichText::imagePaths((string) $html) as $path) {
                        $usage[$path][] = 'Settings · '.$field['label'];
                    }
                }
            }
        }

        foreach (Service::query()->get(['id', 'title', 'description']) as $service) {
            foreach ($service->getTranslations('description') as $html) {
                foreach (RichText::imagePaths($html) as $path) {
                    $usage[$path][] = 'Service text · '.$service->getTranslation('title', 'en');
                }
            }
        }

        foreach (Faq::query()->get(['id', 'question', 'answer']) as $faq) {
            foreach ($faq->getTranslations('answer') as $html) {
                foreach (RichText::imagePaths($html) as $path) {
                    $usage[$path][] = 'FAQ · '.$faq->getTranslation('question', 'en');
                }
            }
        }

        foreach (Project::query()->get(['id', 'title', 'cover_image', 'gallery', 'description']) as $project) {
            $title = $project->getTranslation('title', 'en');
            foreach ($project->getTranslations('description') as $html) {
                foreach (RichText::imagePaths($html) as $path) {
                    $usage[$path][] = "Project text · {$title}";
                }
            }
            if ($project->cover_image) {
                $usage[$project->cover_image][] = "Project cover · {$title}";
            }
            foreach ($project->gallery ?? [] as $path) {
                $usage[$path][] = "Project gallery · {$title}";
            }
        }

        foreach (TeamMember::query()->whereNotNull('photo')->get(['id', 'name', 'photo']) as $member) {
            $usage[$member->photo][] = 'Team · '.$member->getTranslation('name', 'en');
        }

        foreach (Testimonial::query()->whereNotNull('photo')->get(['id', 'name', 'photo']) as $testimonial) {
            $usage[$testimonial->photo][] = 'Testimonial · '.$testimonial->name;
        }

        return array_map(fn (array $labels): array => array_values(array_unique(array_map('trim', $labels))), $usage);
    }

    public function isUsed(string $path): bool
    {
        return array_key_exists($path, $this->usage());
    }

    /**
     * Delete one unused photo. Refuses anything in use or outside the upload folders.
     */
    public function delete(string $path): void
    {
        $folder = strtok($path, '/');

        if (str_contains($path, '..') || ! array_key_exists((string) $folder, self::FOLDERS) || ! Storage::disk('public')->exists($path)) {
            throw new RuntimeException('That file is not in the media library.');
        }

        if ($this->isUsed($path)) {
            throw new RuntimeException('This photo is used on the website and cannot be deleted.');
        }

        Storage::disk('public')->delete($path);
    }
}
