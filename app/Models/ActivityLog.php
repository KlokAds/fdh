<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'action', 'description', 'ip', 'user_agent'])]
class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a security-relevant event with the current request's IP and user agent.
     */
    public static function record(string $action, ?string $description = null, ?int $userId = null): void
    {
        $request = request();

        self::query()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description !== null ? Str::limit($description, 250) : null,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250),
        ]);
    }
}
