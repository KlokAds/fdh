<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Support\ImageStore;
use App\Support\SampleContent;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use RuntimeException;

#[Signature('fdh:import-content {manifest : JSON file listing settings, team members and projects; photo paths are relative to it} {--keep-samples : Leave the sample projects and photos in place}')]
#[Description('Add prepared content (team, projects with photos, settings) in one go. Safe to run again: team members are matched by English name and projects by slug, so nothing is duplicated.')]
class ImportContent extends Command
{
    private string $directory = '';

    public function handle(ImageStore $images, SampleContent $samples): int
    {
        $manifest = realpath((string) $this->argument('manifest'));
        $data = $manifest && is_file($manifest) ? json_decode((string) file_get_contents($manifest), true) : null;

        if (! is_array($data)) {
            $this->error('The manifest was not found or is not valid JSON.');

            return self::FAILURE;
        }

        $this->directory = dirname($manifest);

        if ($data['settings'] ?? []) {
            Setting::write($data['settings']);
            $this->info(count($data['settings']).' setting(s) saved.');
        }

        foreach ($data['images'] ?? [] as $key => $file) {
            $old = Setting::raw()[$key] ?? null;
            Setting::write([$key => $images->storeFromPath($this->photo($file), 'site')]);
            $samples->forgetImage($key);
            $images->delete(is_string($old) ? $old : null);
            $this->info("Photo set: {$key}");
        }

        foreach ($data['team'] ?? [] as $row) {
            $member = TeamMember::query()->where('name->en', $row['name']['en'])->first() ?? new TeamMember(['is_active' => true]);
            foreach (['name', 'designation', 'bio'] as $field) {
                if (isset($row[$field])) {
                    $member->setTranslations($field, $row[$field]);
                }
            }
            $member->fill(Arr::only($row, ['phone', 'email', 'sort', 'is_lead']));
            if (isset($row['photo'])) {
                $images->delete($member->photo);
                $member->photo = $images->storeFromPath($this->photo($row['photo']), 'team', 800);
            }
            $member->save();
            $this->info('Team: '.$row['name']['en']);
        }

        foreach ($data['projects'] ?? [] as $index => $row) {
            $project = Project::query()->where('slug', $row['slug'])->first() ?? new Project(['slug' => $row['slug']]);
            $images->delete($project->cover_image);
            foreach ($project->gallery ?? [] as $old) {
                $images->delete($old);
            }
            $project->setTranslations('title', $row['title']);
            $project->setTranslations('description', $row['description'] ?? []);
            $project->fill([
                'category' => $row['category'],
                'status' => $row['status'] ?? 'design',
                'cover_image' => $images->storeFromPath($this->photo($row['cover']), 'projects'),
                'gallery' => array_map(fn (string $file): string => $images->storeFromPath($this->photo($file), 'projects'), $row['gallery'] ?? []),
                'is_featured' => (bool) ($row['featured'] ?? false),
                'is_published' => true,
                'is_sample' => false,
                'sort' => $row['sort'] ?? $index + 1,
            ])->save();
            $this->info('Project: '.$row['slug']);
        }

        if (($data['projects'] ?? []) && ! $this->option('keep-samples')) {
            $this->info('Sample items removed: '.$samples->remove());
        }

        ActivityLog::record('content.imported', 'Imported prepared content from '.basename($manifest));

        return self::SUCCESS;
    }

    /**
     * A photo named in the manifest, which must sit inside the manifest's own folder.
     */
    private function photo(string $file): string
    {
        $path = realpath($this->directory.'/'.$file);

        if (! $path || ! is_file($path) || ! str_starts_with($path, $this->directory.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException("Photo not found in the manifest's folder: {$file}");
        }

        return $path;
    }
}
