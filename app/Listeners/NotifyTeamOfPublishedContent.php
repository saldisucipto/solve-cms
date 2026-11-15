<?php

namespace App\Listeners;

use App\Events\ContentPublished as ContentPublishedEvent;
use App\Notifications\ContentPublished as ContentPublishedNotification;
use App\Services\Notifications\Notifier;

class NotifyTeamOfPublishedContent
{
    public function __construct(private readonly Notifier $notifier) {}

    public function handle(ContentPublishedEvent $event): void
    {
        $this->notifier->toPermission('posts.view', new ContentPublishedNotification($event->post));
    }
}
