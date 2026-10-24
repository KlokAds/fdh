<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

#[Fillable(['slug', 'title', 'category', 'location', 'year', 'description', 'cover_image', 'gallery', 'is_featured', 'is_published', 'is_sample', 'sort'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasTranslations;

    /**
     * Category keys stored in the database; labels are translated in the views with __().
     */
    public const CATEGORIES = ['Residential', 'Duplex & Villa', 'Apartment', 'Commercial', 'Interior', 'Renovation', 'Construction'];

    /** @var list<string> */
    public array $translatable = ['title', 'location', 'description'];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'is_sample' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderByDesc('is_featured')->orderBy('sort')->latest('id');
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    /**
     * @return list<string>
     */
    public function galleryUrls(): array
    {
        return array_values(array_map(fn (string $path): string => Storage::disk('public')->url($path), $this->gallery ?? []));
    }
}
