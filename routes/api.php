<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OverdueNotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->middleware('throttle:auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::post('auth/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
            Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
            Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
            Route::apiResource('projects', AdminProjectController::class)->only(['index', 'show', 'update', 'destroy']);
            Route::apiResource('tasks', AdminTaskController::class)->only(['index', 'show', 'update', 'destroy']);
            Route::apiResource('tags', AdminTagController::class)->only(['index', 'show', 'update', 'destroy']);
        });

        Route::get('dashboard', DashboardController::class);
        Route::get('activity-logs', [ActivityLogController::class, 'index']);
        Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show']);
        Route::get('notifications', [OverdueNotificationController::class, 'index']);
        Route::get('notifications/{notification}', [OverdueNotificationController::class, 'show']);
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('tags', TagController::class);

        Route::put('projects/{project}/tags/{tag}', [TagController::class, 'attachToProject']);
        Route::delete('projects/{project}/tags/{tag}', [TagController::class, 'detachFromProject']);
        Route::put('tasks/{task}/tags/{tag}', [TagController::class, 'attachToTask']);
        Route::delete('tasks/{task}/tags/{tag}', [TagController::class, 'detachFromTask']);

        Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
        Route::post('projects/{project}/tasks', [TaskController::class, 'store']);
        Route::get('tasks/{task}', [TaskController::class, 'show']);
        Route::match(['put', 'patch'], 'tasks/{task}', [TaskController::class, 'update']);
        Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

        Route::get('projects/{project}/comments', [CommentController::class, 'projectIndex']);
        Route::post('projects/{project}/comments', [CommentController::class, 'projectStore']);
        Route::get('tasks/{task}/comments', [CommentController::class, 'taskIndex']);
        Route::post('tasks/{task}/comments', [CommentController::class, 'taskStore']);
        Route::get('comments/{comment}', [CommentController::class, 'show']);
        Route::match(['put', 'patch'], 'comments/{comment}', [CommentController::class, 'update']);
        Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

        Route::get('projects/{project}/media', [MediaController::class, 'projectIndex']);
        Route::post('projects/{project}/media', [MediaController::class, 'projectStore']);
        Route::get('tasks/{task}/media', [MediaController::class, 'taskIndex']);
        Route::post('tasks/{task}/media', [MediaController::class, 'taskStore']);
        Route::get('comments/{comment}/media', [MediaController::class, 'commentIndex']);
        Route::post('comments/{comment}/media', [MediaController::class, 'commentStore']);
        Route::get('media/{media}', [MediaController::class, 'show']);
        Route::patch('media/{media}', [MediaController::class, 'update']);
        Route::delete('media/{media}', [MediaController::class, 'destroy']);
    });
});
