<?php

namespace App\Models;

use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'designation', 'bio', 'photo', 'phone', 'email', 'facebook', 'linkedin', 'sort', 'is_active'])]
class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory, HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'designation', 'bio'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort' => 'integer'];
    }

    /**
     * @param  Builder<TeamMember>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }

    public function initials(): string
    {
        $name = trim(preg_replace('/^(Engr\.|ইঞ্জি\.)\s*/u', '', (string) $this->getTranslation('name', 'en')) ?? '');

        return mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1));
    }
}
