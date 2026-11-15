<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('is_featured');
            $table->index('published_at');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_unread_index');
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropIndex(['last_message_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_unread_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['published_at']);
        });
    }
};
