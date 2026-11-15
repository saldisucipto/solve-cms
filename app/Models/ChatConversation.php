<?php

namespace App\Models;

use Database\Factories\ChatConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $token
 * @property string $visitor_name
 * @property string|null $visitor_email
 * @property string $visitor_token
 * @property string|null $subject
 * @property string $status
 * @property int|null $assigned_to
 * @property int $unread_for_agents
 * @property int $unread_for_visitor
 * @property string|null $visitor_ip
 * @property Carbon|null $visitor_last_seen_at
 * @property Carbon|null $last_message_at
 * @property Carbon|null $closed_at
 */
#[Fillable([
    'token', 'visitor_name', 'visitor_email', 'visitor_token', 'subject', 'status',
    'assigned_to', 'unread_for_agents', 'unread_for_visitor', 'visitor_ip',
    'visitor_last_seen_at', 'last_message_at', 'closed_at',
])]
class ChatConversation extends Model
{
    /** @use HasFactory<ChatConversationFactory> */
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_CLOSED = 'closed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unread_for_agents' => 'integer',
            'unread_for_visitor' => 'integer',
            'visitor_last_seen_at' => 'datetime',
            'last_message_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ChatMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isUnassigned(): bool
    {
        return $this->assigned_to === null;
    }

    /**
     * @param  Builder<ChatConversation>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', '!=', self::STATUS_CLOSED);
    }

    /**
     * @param  Builder<ChatConversation>  $query
     */
    public function scopeForAgent(Builder $query, User $user): void
    {
        if ($user->hasPermission('chat.assign')) {
            return;
        }

        $query->where(fn (Builder $inner) => $inner
            ->where('assigned_to', $user->id)
            ->orWhereNull('assigned_to'));
    }
}
