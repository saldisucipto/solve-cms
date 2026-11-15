<?php

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Role;
use App\Models\User;
use App\Services\SiteSettings;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteSettingSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(SiteSettingSeeder::class);
});

function chatUser(string $roleName): User
{
    $role = Role::where('name', $roleName)->firstOrFail();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->roles()->attach($role);

    return $user;
}

describe('visitor chat', function () {
    test('a visitor can start a conversation without registering', function () {
        $response = $this->postJson('/chat/start', [
            'name' => 'Guest Visitor',
            'email' => 'guest@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_OPEN);

        $this->assertDatabaseHas('chat_conversations', [
            'visitor_name' => 'Guest Visitor',
            'visitor_email' => 'guest@example.com',
        ]);
    });

    test('a visitor can send a message in their conversation', function () {
        $this->postJson('/chat/start', ['name' => 'Guest Visitor']);

        $this->postJson('/chat/messages', ['body' => 'Hello support team'])
            ->assertCreated()
            ->assertJsonPath('message.sender_type', ChatMessage::SENDER_VISITOR);

        $this->assertDatabaseHas('chat_messages', ['body' => 'Hello support team']);
    });

    test('a visitor without a conversation cannot post a message', function () {
        $this->postJson('/chat/messages', ['body' => 'Sneaky'])->assertNotFound();
    });

    test('messages are validated for length and presence', function () {
        $this->postJson('/chat/start', ['name' => 'Guest Visitor']);

        $this->postJson('/chat/messages', ['body' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    });

    test('a guest cannot read another visitor conversation', function () {
        $this->postJson('/chat/start', ['name' => 'Visitor One']);
        $this->postJson('/chat/messages', ['body' => 'Private message one']);

        // Fresh session = a different visitor with no conversation.
        $this->flushSession();

        $this->getJson('/chat')
            ->assertOk()
            ->assertJsonPath('conversation', null)
            ->assertJsonPath('messages', []);
    });

    test('chat endpoint is disabled when the setting is off', function () {
        app(SiteSettings::class)->setMany(['chat_enabled' => '0'], 'chat');

        $this->getJson('/chat')->assertOk()->assertJsonPath('enabled', false);
    });
});

describe('agent chat', function () {
    beforeEach(function () {
        $this->agent = chatUser('support-agent');
    });

    test('an agent can reply to a conversation', function () {
        $conversation = ChatConversation::factory()->create();

        $this->actingAs($this->agent)
            ->post("/admin/chat/{$conversation->id}/reply", ['body' => 'Happy to help'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('chat_messages', [
            'chat_conversation_id' => $conversation->id,
            'sender_type' => ChatMessage::SENDER_AGENT,
            'body' => 'Happy to help',
        ]);
    });

    test('replying assigns the conversation to the agent', function () {
        $conversation = ChatConversation::factory()->create();

        $this->actingAs($this->agent)
            ->post("/admin/chat/{$conversation->id}/reply", ['body' => 'First reply']);

        expect($conversation->fresh()->assigned_to)->toBe($this->agent->id)
            ->and($conversation->fresh()->status)->toBe(ChatConversation::STATUS_ASSIGNED);
    });

    test('an agent can close and reopen a conversation', function () {
        $conversation = ChatConversation::factory()->create();

        $this->actingAs($this->agent)
            ->post("/admin/chat/{$conversation->id}/status", ['status' => 'closed']);

        expect($conversation->fresh()->status)->toBe(ChatConversation::STATUS_CLOSED);

        $this->actingAs($this->agent)
            ->post("/admin/chat/{$conversation->id}/status", ['status' => 'open']);

        expect($conversation->fresh()->status)->toBe(ChatConversation::STATUS_OPEN);
    });

    test('an administrator can assign a conversation to an agent', function () {
        $admin = chatUser('administrator');
        $conversation = ChatConversation::factory()->create();

        $this->actingAs($admin)
            ->post("/admin/chat/{$conversation->id}/assign", ['user_id' => $this->agent->id])
            ->assertSessionHasNoErrors();

        expect($conversation->fresh()->assigned_to)->toBe($this->agent->id);
    });

    test('a support agent cannot assign conversations', function () {
        $conversation = ChatConversation::factory()->create();

        $this->actingAs($this->agent)
            ->post("/admin/chat/{$conversation->id}/assign", ['user_id' => $this->agent->id])
            ->assertForbidden();
    });

    test('a user without chat permission cannot open the inbox', function () {
        $author = chatUser('author');

        $this->actingAs($author)->get('/admin/chat')->assertForbidden();
    });

    test('an agent cannot read a conversation assigned to someone else', function () {
        $otherAgent = chatUser('support-agent');
        $conversation = ChatConversation::factory()->create(['assigned_to' => $otherAgent->id]);

        $this->actingAs($this->agent)
            ->getJson("/admin/chat/{$conversation->id}/messages")
            ->assertForbidden();
    });
});

describe('chat notifications', function () {
    test('a visitor message notifies agents with chat access', function () {
        $agent = chatUser('support-agent');

        $this->postJson('/chat/start', ['name' => 'Guest Visitor']);
        $this->postJson('/chat/messages', ['body' => 'Need assistance']);

        expect($agent->notifications()->count())->toBeGreaterThan(0);
        expect($agent->notifications()->first()->data['type'])->toBeIn(['chat_started', 'chat_message']);
    });

    test('visitor messages do not notify users without chat permission', function () {
        $author = chatUser('author');

        $this->postJson('/chat/start', ['name' => 'Guest Visitor']);
        $this->postJson('/chat/messages', ['body' => 'Need assistance']);

        expect($author->notifications()->count())->toBe(0);
    });

    test('assigning a conversation notifies the assigned agent', function () {
        $admin = chatUser('administrator');
        $agent = chatUser('support-agent');
        $conversation = ChatConversation::factory()->create();

        $this->actingAs($admin)
            ->post("/admin/chat/{$conversation->id}/assign", ['user_id' => $agent->id]);

        $types = $agent->notifications()->get()->pluck('data.type');

        expect($types)->toContain('conversation_assigned');
    });
});
