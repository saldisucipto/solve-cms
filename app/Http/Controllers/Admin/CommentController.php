<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyCommentRequest;
use App\Models\Comment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Comment::class);

        $status = $request->string('status')->value();
        $search = $request->string('search')->value();

        $comments = Comment::query()
            ->with(['post:id,title,slug', 'replies' => fn ($query) => $query->with('user:id,name')])
            ->when($status, fn ($query, string $value) => $query->where('status', $value))
            ->when($search, fn ($query, string $term) => $query->where(fn ($inner) => $inner
                ->where('content', 'like', "%{$term}%")
                ->orWhere('author_name', 'like', "%{$term}%")
                ->orWhere('author_email', 'like', "%{$term}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Comment $comment): array => [
                'id' => $comment->id,
                'author_name' => $comment->author_name,
                'author_email' => $comment->author_email,
                'author_website' => $comment->author_website,
                'content' => $comment->content,
                'status' => $comment->status,
                'post' => $comment->post === null ? null : [
                    'id' => $comment->post->id,
                    'title' => $comment->post->title,
                    'slug' => $comment->post->slug,
                ],
                'replies' => $comment->replies->map(fn (Comment $reply): array => [
                    'id' => $reply->id,
                    'author_name' => $reply->author_name,
                    'content' => $reply->content,
                    'status' => $reply->status,
                    'created_at' => $reply->created_at?->diffForHumans(),
                ])->all(),
                'created_at' => $comment->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'counts' => [
                'all' => Comment::count(),
                'pending' => Comment::where('status', Comment::STATUS_PENDING)->count(),
                'approved' => Comment::where('status', Comment::STATUS_APPROVED)->count(),
                'spam' => Comment::where('status', Comment::STATUS_SPAM)->count(),
                'rejected' => Comment::where('status', Comment::STATUS_REJECTED)->count(),
            ],
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function updateStatus(Request $request, Comment $comment): RedirectResponse
    {
        Gate::authorize('moderate', $comment);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                Comment::STATUS_PENDING,
                Comment::STATUS_APPROVED,
                Comment::STATUS_SPAM,
                Comment::STATUS_REJECTED,
            ])],
        ]);

        $comment->update([
            'status' => $data['status'],
            'approved_at' => $data['status'] === Comment::STATUS_APPROVED ? now() : null,
        ]);

        $this->activity->log('comment.moderated', "Marked comment as {$data['status']}", $comment);

        return back()->with('success', 'Comment status updated.');
    }

    public function reply(ReplyCommentRequest $request, Comment $comment): RedirectResponse
    {
        $reply = Comment::create([
            'post_id' => $comment->post_id,
            'parent_id' => $comment->id,
            'user_id' => $request->user()->id,
            'author_name' => $request->user()->name,
            'author_email' => $request->user()->email,
            'content' => $request->validated()['content'],
            'status' => Comment::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $this->activity->log('comment.replied', 'Replied to a comment', $reply);

        return back()->with('success', 'Reply posted.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        $this->activity->log('comment.deleted', 'Deleted a comment');

        return back()->with('success', 'Comment deleted.');
    }
}
