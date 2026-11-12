<?php

namespace App\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'phone', 'email', 'service', 'location', 'message', 'locale', 'status', 'note', 'assigned_to', 'ip', 'user_agent'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    /** @var array<string, string> */
    public const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed', 'spam' => 'Spam'];

    /** @var array<string, string> */
    public const READ_VIA = ['email_open' => 'opened the email', 'email_link' => 'email button', 'admin' => 'admin panel'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'emailed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Record the first time anyone saw this inquiry, and how.
     */
    /**
     * The request number the visitor sees on screen and in the auto-reply, e.g. FDH-00012.
     */
    public function reference(): string
    {
        return 'FDH-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function markAsRead(string $via): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now(), 'read_via' => $via])->save();
        }
    }
}
