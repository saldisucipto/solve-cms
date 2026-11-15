<?php

use App\Models\ChatConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
| The visitor channel is authorised by the anonymous session token issued when
| the chat starts. Agents with chat access may also listen for support purposes.
*/
Broadcast::channel('chat.visitor.{token}', function ($user, string $token) {
    $sessionToken = request()->session()->get('chat_visitor_token');

    if (is_string($sessionToken) && hash_equals($sessionToken, $token)) {
        return true;
    }

    return $user !== null && $user->hasPermission('chat.view');
});

/*
| The inbox channel carries new conversations and message notifications for
| every agent, so it requires the chat.view permission.
*/
Broadcast::channel('chat.inbox', function ($user) {
    return $user !== null && $user->hasPermission('chat.view');
});

/*
| A single conversation channel is only open to agents who may see that
| conversation: assigners, the assigned agent, or anyone when unassigned.
*/
Broadcast::channel('chat.conversation.{conversationId}', function ($user, int $conversationId) {
    if ($user === null || ! $user->hasPermission('chat.view')) {
        return false;
    }

    $conversation = ChatConversation::query()->find($conversationId);

    if ($conversation === null) {
        return false;
    }

    if ($user->hasPermission('chat.assign')) {
        return true;
    }

    return $conversation->assigned_to === null || $conversation->assigned_to === $user->id;
});
