<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewCommentSubmitted;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteSettingSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(SiteSettingSeeder::class);
});

function roleUser(string $roleName): User
{
    $role = Role::where('name', $roleName)->firstOrFail();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->roles()->attach($role);

    return $user;
}

test('a new comment notifies moderators', function () {
    $editor = roleUser('editor');
    $post = Post::factory()->published()->create(['slug' => 'notify-comment', 'user_id' => null]);

    $this->post("/blog/{$post->slug}/comments", [
        'author_name' => 'Visitor',
        'author_email' => 'visitor@example.com',
        'content' => 'A new comment to moderate.',
    ]);

    $types = $editor->notifications()->get()->pluck('data.type');

    expect($types)->toContain('comment');
});

test('a comment does not notify users without moderation rights', function () {
    $agent = roleUser('support-agent');
    $post = Post::factory()->published()->create(['slug' => 'no-notify', 'user_id' => null]);

    $this->post("/blog/{$post->slug}/comments", [
        'author_name' => 'Visitor',
        'author_email' => 'visitor@example.com',
        'content' => 'Another comment.',
    ]);

    expect($agent->notifications()->count())->toBe(0);
});

test('publishing a post notifies users who can view posts', function () {
    $admin = roleUser('administrator');

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Freshly Published',
        'slug' => '',
        'content' => '<p>Body</p>',
        'status' => 'published',
        'is_featured' => false,
        'category_ids' => [],
        'tag_ids' => [],
    ]);

    $types = $admin->notifications()->get()->pluck('data.type');

    expect($types)->toContain('content');
});

test('a draft does not notify anyone', function () {
    $admin = roleUser('administrator');

    $this->actingAs($admin)->post('/admin/posts', [
        'title' => 'Still A Draft',
        'slug' => '',
        'status' => 'draft',
        'is_featured' => false,
        'category_ids' => [],
        'tag_ids' => [],
    ]);

    expect($admin->notifications()->count())->toBe(0);
});

test('a user can see their notifications page', function () {
    $user = roleUser('administrator');

    $user->notify(new NewCommentSubmitted(
        Comment::factory()->create(['post_id' => Post::factory()->create()->id])
    ));

    $this->actingAs($user)
        ->get('/admin/notifications')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Notifications/Index')
            ->where('unread', 1));
});

test('a notification can be marked as read', function () {
    $user = roleUser('administrator');
    $user->notify(new NewCommentSubmitted(
        Comment::factory()->create(['post_id' => Post::factory()->create()->id])
    ));

    $notification = $user->notifications()->firstOrFail();

    $this->actingAs($user)
        ->post("/admin/notifications/{$notification->id}/read")
        ->assertRedirect();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('a user cannot mark another user notification as read', function () {
    $owner = roleUser('administrator');
    $other = roleUser('administrator');

    $owner->notify(new NewCommentSubmitted(
        Comment::factory()->create(['post_id' => Post::factory()->create()->id])
    ));

    $notification = $owner->notifications()->firstOrFail();

    $this->actingAs($other)
        ->post("/admin/notifications/{$notification->id}/read")
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

test('all notifications can be marked as read at once', function () {
    $user = roleUser('administrator');

    foreach (range(1, 3) as $index) {
        $user->notify(new NewCommentSubmitted(
            Comment::factory()->create(['post_id' => Post::factory()->create()->id])
        ));
    }

    expect($user->unreadNotifications()->count())->toBe(3);

    $this->actingAs($user)
        ->post('/admin/notifications/read-all')
        ->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0);
});

test('the notification feed is shared with the admin layout', function () {
    $user = roleUser('administrator');

    $user->notify(new NewCommentSubmitted(
        Comment::factory()->create(['post_id' => Post::factory()->create()->id])
    ));

    $this->actingAs($user)
        ->get('/admin')
        ->assertInertia(fn ($page) => $page
            ->where('notificationFeed.unread', 1)
            ->has('notificationFeed.items', 1));
});

test('guests cannot access the notifications page', function () {
    $this->get('/admin/notifications')->assertRedirect('/login');
});
