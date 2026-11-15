<?php

namespace App\Providers;

use App\Events\CommentSubmitted;
use App\Events\ContentPublished;
use App\Events\VisitorMessageReceived;
use App\Listeners\NotifyAdminsOfNewUser;
use App\Listeners\NotifyAgentsOfChatMessage;
use App\Listeners\NotifyModeratorsOfComment;
use App\Listeners\NotifyTeamOfPublishedContent;
use App\Services\Theme\ThemeRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            ThemeRegistry::class,
            fn (): ThemeRegistry => new ThemeRegistry(resource_path('themes')),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerEventListeners();
    }

    /**
     * Wire domain events to the listeners that create notifications.
     */
    protected function registerEventListeners(): void
    {
        Event::listen(VisitorMessageReceived::class, NotifyAgentsOfChatMessage::class);
        Event::listen(CommentSubmitted::class, NotifyModeratorsOfComment::class);
        Event::listen(ContentPublished::class, NotifyTeamOfPublishedContent::class);
        Event::listen(Registered::class, NotifyAdminsOfNewUser::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
