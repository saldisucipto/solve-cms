<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $canViewPosts = $user->hasPermission('posts.view');
        $canModerateComments = $user->hasPermission('comments.moderate');

        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->stats($user),
            'recentPosts' => $canViewPosts ? $this->recentPosts($user) : [],
            'pendingComments' => $canModerateComments ? $this->pendingComments() : [],
            'contentByStatus' => $canViewPosts ? $this->contentByStatus() : null,
            'publishingActivity' => $canViewPosts ? $this->publishingActivity() : [],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function stats(User $user): array
    {
        $canViewPosts = $user->hasPermission('posts.view');
        $canViewPages = $user->hasPermission('pages.view');
        $canViewMedia = $user->hasPermission('media.view');
        $canModerateComments = $user->hasPermission('comments.moderate');
        $canManageTaxonomy = $user->hasPermission('categories.manage');
        $canViewUsers = $user->hasPermission('users.view') && Route::has('admin.users.index');

        $mediaSize = $canViewMedia ? (int) Media::sum('size') : 0;

        return [
            [
                'label' => 'Posts',
                'value' => $canViewPosts ? Post::count() : null,
                'detail' => $canViewPosts ? Post::published()->count().' published' : null,
                'href' => $canViewPosts ? route('admin.posts.index') : null,
                'icon' => 'file-text',
            ],
            [
                'label' => 'Pages',
                'value' => $canViewPages ? Page::count() : null,
                'detail' => $canViewPages ? Page::published()->count().' published' : null,
                'href' => $canViewPages ? route('admin.pages.index') : null,
                'icon' => 'layers',
            ],
            [
                'label' => 'Media',
                'value' => $canViewMedia ? Media::count() : null,
                'detail' => $canViewMedia ? $this->formatBytes($mediaSize) : null,
                'href' => $canViewMedia ? route('admin.media.index') : null,
                'icon' => 'image',
            ],
            [
                'label' => 'Comments',
                'value' => $canModerateComments ? Comment::count() : null,
                'detail' => $canModerateComments ? Comment::where('status', Comment::STATUS_PENDING)->count().' awaiting review' : null,
                'href' => $canModerateComments ? route('admin.comments.index') : null,
                'icon' => 'message-square',
            ],
            [
                'label' => 'Categories',
                'value' => $canManageTaxonomy ? Category::count() : null,
                'detail' => $canManageTaxonomy ? Tag::count().' tags' : null,
                'href' => $canManageTaxonomy ? route('admin.categories.index') : null,
                'icon' => 'folder-tree',
            ],
            [
                'label' => 'Users',
                'value' => $canViewUsers ? User::count() : null,
                'detail' => $canViewUsers ? User::where('status', 'active')->count().' active' : null,
                'href' => $canViewUsers ? route('admin.users.index') : null,
                'icon' => 'users',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function recentPosts(User $user): array
    {
        return Post::query()
            ->with('author:id,name')
            ->when(! $user->hasPermission('posts.update'), fn ($query) => $query->where('user_id', $user->id))
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'author' => $post->author?->name,
                'updated_at' => $post->updated_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function pendingComments(): array
    {
        return Comment::query()
            ->where('status', Comment::STATUS_PENDING)
            ->with('post:id,title,slug')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Comment $comment): array => [
                'id' => $comment->id,
                'author_name' => $comment->author_name,
                'content' => str($comment->content)->limit(120)->value(),
                'post' => $comment->post?->title,
                'created_at' => $comment->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    protected function contentByStatus(): array
    {
        return [
            'published' => Post::where('status', Post::STATUS_PUBLISHED)->count(),
            'draft' => Post::where('status', Post::STATUS_DRAFT)->count(),
            'pending' => Post::where('status', Post::STATUS_PENDING)->count(),
            'scheduled' => Post::where('status', Post::STATUS_SCHEDULED)->count(),
        ];
    }

    /**
     * Posts published per day over the last 14 days.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function publishingActivity(): array
    {
        $start = Carbon::today()->subDays(13);

        $counts = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '>=', $start)
            ->get(['published_at'])
            ->groupBy(fn (Post $post): string => $post->published_at->toDateString())
            ->map->count();

        return collect(range(0, 13))
            ->map(function (int $offset) use ($start, $counts): array {
                $date = $start->copy()->addDays($offset);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'label' => $date->format('M j'),
                    'count' => (int) ($counts[$key] ?? 0),
                ];
            })
            ->all();
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = (float) $bytes;
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return round($size, $index === 0 ? 0 : 1).' '.$units[$index];
    }
}
