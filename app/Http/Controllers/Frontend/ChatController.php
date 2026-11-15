<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\Chat\ChatService;
use App\Services\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const SESSION_KEY = 'chat_visitor_token';

    public function __construct(
        private readonly ChatService $chat,
        private readonly SiteSettings $settings,
    ) {}

    /**
     * Current chat state for the visitor widget.
     */
    public function show(Request $request): JsonResponse
    {
        if (! $this->settings->bool('chat_enabled', true)) {
            return response()->json(['enabled' => false]);
        }

        $conversation = $this->conversation($request);

        return response()->json([
            'enabled' => true,
            'token' => $this->visitorToken($request),
            'welcome_message' => $this->settings->get('chat_welcome_message'),
            'requires_email' => $this->settings->bool('chat_require_email'),
            'conversation' => $conversation === null ? null : [
                'token' => $conversation->token,
                'status' => $conversation->status,
                'unread' => $conversation->unread_for_visitor,
            ],
            'messages' => $conversation === null ? [] : $this->messages($conversation),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        abort_unless($this->settings->bool('chat_enabled', true), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $conversation = $this->chat->start(
            $this->visitorToken($request),
            $validated['name'],
            $validated['email'] ?? null,
            $request->ip(),
        );

        return response()->json([
            'conversation' => [
                'token' => $conversation->token,
                'status' => $conversation->status,
            ],
            'messages' => $this->messages($conversation),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($this->settings->bool('chat_enabled', true), 404);

        $conversation = $this->conversation($request);

        abort_if($conversation === null, 404, 'Start a conversation first.');

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $message = $this->chat->addVisitorMessage($conversation, $validated['body']);

        return response()->json([
            'message' => $this->presentMessage($message),
        ], 201);
    }

    public function read(Request $request): JsonResponse
    {
        $conversation = $this->conversation($request);

        if ($conversation !== null) {
            $this->chat->markReadForVisitor($conversation);
        }

        return response()->json(['ok' => true]);
    }

    private function visitorToken(Request $request): string
    {
        $token = $request->session()->get(self::SESSION_KEY);

        if (! is_string($token) || $token === '') {
            $token = (string) Str::uuid();
            $request->session()->put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Resolve the conversation that belongs to this visitor's session only.
     */
    private function conversation(Request $request): ?ChatConversation
    {
        $token = $request->session()->get(self::SESSION_KEY);

        if (! is_string($token) || $token === '') {
            return null;
        }

        return $this->chat->conversationForVisitor($token);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function messages(ChatConversation $conversation): array
    {
        return $conversation->messages()
            ->with('user:id,name')
            ->get()
            ->map(fn (ChatMessage $message): array => $this->presentMessage($message))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function presentMessage(ChatMessage $message): array
    {
        $author = $message->user?->name;

        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'body' => e($message->body),
            'author' => $message->isFromVisitor() ? 'You' : ($author ?? 'Support'),
            'created_human' => $message->created_at?->format('H:i'),
        ];
    }
}
