<?php

namespace App\Providers;

use App\Contracts\Repositories\ActivityLogRepositoryInterface;
use App\Contracts\Repositories\CommentRepositoryInterface;
use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Repositories\MediaRepositoryInterface;
use App\Contracts\Repositories\OverdueNotificationRepositoryInterface;
use App\Contracts\Repositories\ProjectRepositoryInterface;
use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\OverdueTaskNotification;
use App\Policies\MediaPolicy;
use App\Policies\OverdueTaskNotificationPolicy;
use App\Repositories\EloquentActivityLogRepository;
use App\Repositories\EloquentCommentRepository;
use App\Repositories\EloquentDashboardRepository;
use App\Repositories\EloquentMediaRepository;
use App\Repositories\EloquentOverdueNotificationRepository;
use App\Repositories\EloquentProjectRepository;
use App\Repositories\EloquentTaskRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ActivityLogRepositoryInterface::class, EloquentActivityLogRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, EloquentDashboardRepository::class);
        $this->app->bind(MediaRepositoryInterface::class, EloquentMediaRepository::class);
        $this->app->bind(OverdueNotificationRepositoryInterface::class, EloquentOverdueNotificationRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, EloquentTaskRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(OverdueTaskNotification::class, OverdueTaskNotificationPolicy::class);
    }
}
