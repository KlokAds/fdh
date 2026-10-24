<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Placeholder photos and projects that make a fresh site look complete. Everything added here is marked,
 * shown with a "Sample" badge in the admin, replaced automatically when real photos are uploaded, and
 * removable in one click. Photos: database/seeders/samples (Unsplash License).
 */
class SampleContent
{
    /** Settings row that remembers which image settings still hold a sample photo. */
    private const MARKER = 'sample_images';

    /**
     * Image settings filled with a sample photo: setting key => file in database/seeders/samples.
     *
     * @var array<string, string>
     */
    private const IMAGES = ['hero_image' => 'hero.jpg', 'about_image' => 'blueprint.jpg'];

    public function __construct(private ImageStore $images) {}

    public static function directory(): string
    {
        return database_path('seeders/samples');
    }

    /**
     * Add the sample photos and projects. Never overwrites a real photo or a real project.
     */
    public function install(): void
    {
        $raw = Setting::raw();
        $marked = $this->markedImages();

        foreach (self::IMAGES as $key => $file) {
            if (($raw[$key] ?? '') === '' && is_file(self::directory().'/'.$file)) {
                Setting::write([$key => $this->images->storeFromPath(self::directory().'/'.$file, 'site', 1800)]);
                $marked[] = $key;
            }
        }
        $this->saveMarkedImages($marked);

        foreach ($this->projects() as $index => $project) {
            if (Project::query()->where('slug', $project['slug'])->exists()) {
                continue;
            }

            Project::query()->create([
                'slug' => $project['slug'],
                'title' => $project['title'],
                'category' => $project['category'],
                'description' => $project['description'],
                'cover_image' => $this->images->storeFromPath(self::directory().'/'.$project['image'], 'projects'),
                'gallery' => array_map(fn (string $file): string => $this->images->storeFromPath(self::directory().'/'.$file, 'projects'), $project['gallery'] ?? []),
                'is_featured' => $index === 0,
                'is_published' => true,
                'is_sample' => true,
                'sort' => 100 + $index,
            ]);
        }
    }

    public function present(): bool
    {
        return Project::query()->where('is_sample', true)->exists() || $this->markedImages() !== [];
    }

    /**
     * Delete every sample project and photo; real content is untouched.
     */
    public function remove(): int
    {
        $removed = 0;

        foreach (Project::query()->where('is_sample', true)->get() as $project) {
            $this->images->delete($project->cover_image);
            foreach ($project->gallery ?? [] as $path) {
                $this->images->delete($path);
            }
            $project->delete();
            $removed++;
        }

        $raw = Setting::raw();
        foreach ($this->markedImages() as $key) {
            $this->images->delete(is_string($raw[$key] ?? null) ? $raw[$key] : null);
            Setting::write([$key => '']);
            $removed++;
        }
        $this->saveMarkedImages([]);

        ActivityLog::record('samples.removed', "Removed {$removed} sample item(s)");

        return $removed;
    }

    /**
     * Called when an admin uploads a real image for a setting: it is no longer a sample.
     */
    public function forgetImage(string $key): void
    {
        $this->saveMarkedImages(array_values(array_diff($this->markedImages(), [$key])));
    }

    public function isSampleImage(string $key): bool
    {
        return in_array($key, $this->markedImages(), true);
    }

    /**
     * @return list<string>
     */
    private function markedImages(): array
    {
        $value = Setting::query()->whereKey(self::MARKER)->value('value');
        $decoded = is_string($value) ? json_decode($value, true) : null;

        return is_array($decoded) ? array_values(array_unique(array_filter($decoded, 'is_string'))) : [];
    }

    /**
     * @param  list<string>  $keys
     */
    private function saveMarkedImages(array $keys): void
    {
        Setting::query()->updateOrCreate(['key' => self::MARKER], ['value' => json_encode(array_values(array_unique($keys)))]);
        Cache::forget('site-settings');
    }

    /**
     * Building types FDH designs, described by the work involved — no client names, places or dates.
     *
     * @return list<array<string, mixed>>
     */
    private function projects(): array
    {
        return [
            [
                'slug' => 'sample-two-storey-family-home',
                'image' => 'residential.jpg',
                'gallery' => ['blueprint.jpg'],
                'category' => 'Residential',
                'title' => ['en' => 'Two-storey family home', 'bn' => 'দোতলা পারিবারিক বাড়ি'],
                'description' => [
                    'en' => "A complete drawing set for a two-storey home: floor plans, elevations, structural design to BNBC, and electrical and plumbing layouts.\n\nThe design balances open living spaces on the ground floor with private bedrooms upstairs, with cost estimates prepared before construction began.",
                    'bn' => "দোতলা বাড়ির সম্পূর্ণ নকশা: ফ্লোর প্ল্যান, এলিভেশন, বিএনবিসি অনুযায়ী স্ট্রাকচারাল ডিজাইন এবং ইলেকট্রিক্যাল ও প্লাম্বিং লেআউট।\n\nনিচতলায় খোলামেলা বসার জায়গা আর উপরতলায় শোবার ঘর — নির্মাণের আগেই খরচের হিসাব প্রস্তুত।",
                ],
            ],
            [
                'slug' => 'sample-duplex-villa',
                'image' => 'duplex.jpg',
                'category' => 'Duplex & Villa',
                'title' => ['en' => 'Modern duplex villa', 'bn' => 'আধুনিক ডুপ্লেক্স ভিলা'],
                'description' => [
                    'en' => 'Architectural and structural design for a duplex with large openings, cantilevered slabs and a landscaped front court.',
                    'bn' => 'বড় জানালা, ক্যান্টিলিভার স্ল্যাব ও সামনের বাগানসহ একটি ডুপ্লেক্সের আর্কিটেকচারাল ও স্ট্রাকচারাল ডিজাইন।',
                ],
            ],
            [
                'slug' => 'sample-apartment-building',
                'image' => 'apartment.jpg',
                'category' => 'Apartment',
                'title' => ['en' => 'Multi-storey apartment building', 'bn' => 'বহুতল অ্যাপার্টমেন্ট ভবন'],
                'description' => [
                    'en' => 'Structural design, approval drawings and construction supervision for a residential apartment building with balconies on every floor.',
                    'bn' => 'প্রতি তলায় বারান্দাসহ একটি আবাসিক অ্যাপার্টমেন্ট ভবনের স্ট্রাকচারাল ডিজাইন, অনুমোদন নকশা ও নির্মাণ তত্ত্বাবধান।',
                ],
            ],
            [
                'slug' => 'sample-commercial-building',
                'image' => 'commercial.jpg',
                'category' => 'Commercial',
                'title' => ['en' => 'Commercial office building', 'bn' => 'বাণিজ্যিক অফিস ভবন'],
                'description' => [
                    'en' => 'Planning and structural design for a commercial building with open office floors and a glazed facade.',
                    'bn' => 'খোলা অফিস ফ্লোর ও কাঁচের সম্মুখভাগসহ একটি বাণিজ্যিক ভবনের পরিকল্পনা ও স্ট্রাকচারাল ডিজাইন।',
                ],
            ],
            [
                'slug' => 'sample-living-room-interior',
                'image' => 'interior.jpg',
                'category' => 'Interior',
                'title' => ['en' => 'Living room interior', 'bn' => 'লিভিং রুম ইন্টেরিয়র'],
                'description' => [
                    'en' => 'Interior layout, lighting and furniture planning for a calm, practical living room.',
                    'bn' => 'একটি শান্ত ও ব্যবহারবান্ধব লিভিং রুমের লেআউট, আলো ও আসবাবের পরিকল্পনা।',
                ],
            ],
            [
                'slug' => 'sample-construction-supervision',
                'image' => 'construction.jpg',
                'gallery' => ['blueprint.jpg'],
                'category' => 'Construction',
                'title' => ['en' => 'RCC frame construction supervision', 'bn' => 'আরসিসি ফ্রেম নির্মাণ তত্ত্বাবধান'],
                'description' => [
                    'en' => 'On-site supervision of a reinforced concrete frame: checking reinforcement before every casting, concrete quality and the work schedule.',
                    'bn' => 'আরসিসি ফ্রেমের সাইট তত্ত্বাবধান: প্রতিটি ঢালাইয়ের আগে রড যাচাই, কংক্রিটের মান ও কাজের সময়সূচি দেখা।',
                ],
            ],
        ];
    }
}
