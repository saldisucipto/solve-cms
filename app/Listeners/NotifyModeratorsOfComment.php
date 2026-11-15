<?php

namespace App\Listeners;

use App\Events\CommentSubmitted;
use App\Notifications\NewCommentSubmitted;
use App\Services\Notifications\Notifier;

class NotifyModeratorsOfComment
{
    public function __construct(private readonly Notifier $notifier) {}

    public function handle(CommentSubmitted $event): void
    {
        $this->notifier->toPermission('comments.moderate', new NewCommentSubmitted($event->comment));
    }
}
