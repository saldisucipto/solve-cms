<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatAgent;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chat,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('chat.view');

        $user = $request->user();
        $status = $request->string('status')->value();
        $search = $request->string('search')->value();
        $selectedId = $request->integer('conversation') ?: null;

        $conversations = ChatConversation::query()
            ->forAgent($user)
            ->with('agent:id,name')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('visitor_name', 'like', "%{$search}%")
                ->orWhere('visitor_email', 'like', "%{$search}%")))
            ->orderByDesc('last_message_at')
            ->limit(100)
            ->get()
            ->map(fn (ChatConversation $conversation): array => [
                'id' => $conversation->id,
                'token' => $conversation->token,
                'visitor_name' => $conversation->visitor_name,
                'visitor_email' => $conversation->visitor_email,
                'status' => $conversation->status,
                'agent' => $conversation->agent?->name,
                'assigned_to' => $conversation->assigned_to,
                'unread' => $conversation->unread_for_agents,
                'last_message_at' => $conversation->last_message_at?->diffForHumans(),
                'online' => $conversation->visitor_last_seen_at !== null
                    && $conversation->visitor_last_seen_at->gt(now()->subMinutes(5)),
            ]);

        $selected = null;

        if ($selectedId !== null) {
            $selectedConversation = ChatConversation::query()
                ->forAgent($user)
                ->with('agent:id,name')
                ->find($selectedId);

            if ($selectedConversation !== null) {
                $this->chat->markReadForAgent($selectedConversation);

                $selected = [
                    'id' => $selectedConversation->id,
                    'token' => $selectedConversation->token,
                    'visitor_name' => $selectedConversation->visitor_name,
                    'visitor_email' => $selectedConversation->visitor_email,
                    'visitor_ip' => $selectedConversation->visitor_ip,
                    'status' => $selectedConversation->status,
                    'assigned_to' => $selectedConversation->assigned_to,
                    'agent' => $selectedConversation->agent?->name,
                    'messages' => $selectedConversation->messages()
                        ->with('user:id,name')
                        ->get()
                        ->map(fn (ChatMessage $message): array => $this->presentMessage($message))
                        ->all(),
                ];
            }
        }

        return Inertia::render('Admin/Chat/Index', [
            'conversations' => $conversations,
            'selected' => $selected,
            'agents' => User::query()
                ->where(function ($query): void {
                    $query->whereHas('roles', fn ($role) => $role->whereIn('name', ['super-admin', 'administrator', 'support-agent']));
                })
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $agent): array => [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'online' => ChatAgent::query()->where('user_id', $agent->id)->where('is_online', true)->exists(),
                ]),
            'counts' => [
                'all' => ChatConversation::forAgent($user)->count(),
                'open' => ChatConversation::forAgent($user)->where('status', ChatConversation::STATUS_OPEN)->count(),
                'pending' => ChatConversation::forAgent($user)->where('status', ChatConversation::STATUS_PENDING)->count(),
                'assigned' => ChatConversation::forAgent($user)->where('status', ChatConversation::STATUS_ASSIGNED)->count(),
                'closed' => ChatConversation::forAgent($user)->where('status', ChatConversation::STATUS_CLOSED)->count(),
                'unread' => ChatConversation::forAgent($user)->sum('unread_for_agents'),
            ],
            'filters' => ['status' => $status, 'search' => $search],
            'can' => [
                'reply' => $user->hasPermission('chat.reply'),
                'assign' => $user->hasPermission('chat.assign'),
            ],
        ]);
    }

    public function reply(Request $request, ChatConversation $conversation): RedirectResponse|JsonResponse
    {
        Gate::authorize('chat.reply');

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $message = $this->chat->addAgentMessage($conversation, $request->user(), $validated['body']);

        $this->activity->log('chat.replied', "Replied to {$conversation->visitor_name}", $conversation);

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->presentMessage($message)], 201);
        }

        return back()->with('success', 'Reply sent.');
    }

    public function assign(Request $request, ChatConversation $conversation): RedirectResponse
    {
        Gate::authorize('chat.assign');

        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $agent = isset($validated['user_id'])
            ? User::query()->whereKey($validated['user_id'])->first()
            : null;

        $this->chat->assign($conversation, $agent);

        $this->activity->log(
            'chat.assigned',
            $agent === null
                ? "Unassigned {$conversation->visitor_name}"
                : "Assigned {$conversation->visitor_name} to {$agent->name}",
            $conversation,
        );

        return back()->with('success', 'Conversation updated.');
    }

    public function status(Request $request, ChatConversation $conversation): RedirectResponse
    {
        Gate::authorize('chat.reply');

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                ChatConversation::STATUS_OPEN,
                ChatConversation::STATUS_PENDING,
                ChatConversation::STATUS_ASSIGNED,
                ChatConversation::STATUS_CLOSED,
            ])],
        ]);

        $this->chat->setStatus($conversation, $validated['status']);

        $this->activity->log('chat.status', "Set {$conversation->visitor_name} to {$validated['status']}", $conversation);

        return back()->with('success', 'Conversation status updated.');
    }

    /**
     * Lightweight polling endpoint used when real-time is unavailable.
     */
    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        Gate::authorize('chat.view');

        $user = $request->user();

        abort_unless(
            $user->hasPermission('chat.assign')
                || $conversation->assigned_to === null
                || $conversation->assigned_to === $user->id,
            403,
        );

        $after = $request->integer('after');

        return response()->json([
            'messages' => $conversation->messages()
                ->with('user:id,name')
                ->when($after > 0, fn ($query) => $query->where('id', '>', $after))
                ->get()
                ->map(fn (ChatMessage $message): array => $this->presentMessage($message))
                ->all(),
            'status' => $conversation->status,
            'assigned_to' => $conversation->assigned_to,
        ]);
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
            'body' => $message->body,
            'author' => $author ?? $message->conversation->visitor_name,
            'created_human' => $message->created_at?->format('M j, H:i'),
        ];
    }
}
