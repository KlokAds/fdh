<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable(['slug', 'title', 'summary', 'description', 'features', 'icon', 'sort', 'is_active'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasTranslations;

    public const ICONS = ['building', 'approval', 'construction', 'survey', 'estimate', 'interior'];

    /** @var list<string> */
    public array $translatable = ['title', 'summary', 'description', 'features'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    /**
     * Features are stored per language as a comma-separated string.
     *
     * @return list<string>
     */
    public function featureList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->features))));
    }
}
