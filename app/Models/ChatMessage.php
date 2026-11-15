<?php

namespace App\Models;

use Database\Factories\ChatMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $chat_conversation_id
 * @property string $sender_type
 * @property int|null $user_id
 * @property string $body
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 */
#[Fillable(['chat_conversation_id', 'sender_type', 'user_id', 'body', 'read_at'])]
class ChatMessage extends Model
{
    /** @use HasFactory<ChatMessageFactory> */
    use HasFactory;

    public const SENDER_VISITOR = 'visitor';

    public const SENDER_AGENT = 'agent';

    public const SENDER_SYSTEM = 'system';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ChatConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromVisitor(): bool
    {
        return $this->sender_type === self::SENDER_VISITOR;
    }
}
