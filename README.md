# Task Management API

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![API](https://img.shields.io/badge/API-v1-2563EB)](#api-reference)
[![Tests](https://img.shields.io/badge/tests-42%20passed-16A34A)](#testing)

A versioned REST API for managing projects and tasks, built as a Laravel mid-level technical assessment. The project combines a user-owned workspace with a separate administrator cycle, consistent API responses, queued activity tracking, overdue notifications, polymorphic comments, tags, and media attachments.

## Highlights

- Laravel 11 and PHP 8.2+
- Laravel Sanctum token authentication
- Separate User and Admin API cycles
- Project and task CRUD with ownership policies
- Task status/priority filters, title search, and pagination
- User and platform dashboards
- Polymorphic comments, tags, media, and activity subjects
- Spatie Media Library with isolated storage paths
- Queued activity logs and sample-data seeding
- Scheduled overdue-task notifications
- Repository and Service layers
- Form Requests, API Resources, Enums, Policies, and Soft Deletes
- Versioned endpoints under `/api/v1`
- Postman collection with 63 documented requests
- 42 automated tests with 236 assertions

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL 5.7+ / MySQL 8+
- Required Laravel PHP extensions

## Installation

```bash
git clone https://github.com/kariemibrahiem/task_manager.git
cd task_manager
composer install
```

Create the environment file:

```bash
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Create a database, update `.env`, then initialize the application:

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

The local API base URL is:

```text
http://127.0.0.1:8000/api/v1
```

## Environment

Recommended local configuration:

```dotenv
APP_NAME="Task Management API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Africa/Cairo

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
```

`APP_URL` must match the URL serving Laravel because media URLs are generated from it.

Mail credentials are intentionally excluded from source control. Add provider credentials only to the local `.env` file and never commit them.

## Authentication and Roles

Public registration always creates an active account with the `user` role. A role cannot be supplied through the registration payload.

Supported roles:

```text
admin
user
```

Supported account statuses:

```text
active
suspended
```

Create the first administrator securely from the command line:

```bash
php artisan admin:create
```

Send the Sanctum token with protected requests:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

Login example:

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "Password123",
  "device_name": "Postman"
}
```

Suspending or deleting a user revokes their tokens. The application also prevents administrators from removing their own access or removing the last administrator.

## API Response Contract

Every successful endpoint uses the same response shape:

```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {}
}
```

Validation and other errors preserve the same structure:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": [],
  "errors": {
    "title": ["The title field is required."]
  }
}
```

Ownership failures return a clear reason, such as:

```json
{
  "success": false,
  "message": "This tag does not belong to your account.",
  "data": []
}
```

Paginated endpoints return only the required pagination metadata:

```json
{
  "items": [],
  "pagination": {
    "total": 0,
    "current_page": 1,
    "per_page": 15,
    "next_page": null,
    "prev_page": null,
    "from": null,
    "last_page_url": "http://127.0.0.1:8000/api/v1/projects?page=1"
  }
}
```

## API Reference

All paths below are relative to `/api/v1`.

### Authentication

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/auth/register` | Register an active user and create a token |
| `POST` | `/auth/login` | Authenticate an active account |
| `POST` | `/auth/logout` | Revoke the current device token |

### User Dashboard

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/dashboard` | User-owned project/task statistics and unread notification count |

Dashboard fields:

```text
total_projects
active_projects
total_tasks
completed_tasks
pending_tasks
overdue_tasks
unread_notifications
```

### Projects

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/projects` | List the authenticated user's projects |
| `POST` | `/projects` | Create a project |
| `GET` | `/projects/{project}` | View a project with relations |
| `PUT/PATCH` | `/projects/{project}` | Update an owned project |
| `DELETE` | `/projects/{project}` | Soft-delete an owned project |

Project status values:

```text
active
completed
archived
```

### Tasks

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/projects/{project}/tasks` | List and filter project tasks |
| `POST` | `/projects/{project}/tasks` | Create a task |
| `GET` | `/tasks/{task}` | View a task |
| `PUT/PATCH` | `/tasks/{task}` | Update a task |
| `DELETE` | `/tasks/{task}` | Soft-delete a task |

Optional task-list filters:

```text
status=todo|in_progress|done
priority=low|medium|high
search=title keywords
per_page=1..100
```

`completed_at` is synchronized automatically when task status changes to or from `done`.

### Comments

| Method | Endpoint | Description |
|---|---|---|
| `GET/POST` | `/projects/{project}/comments` | List or create project comments |
| `GET/POST` | `/tasks/{task}/comments` | List or create task comments |
| `GET` | `/comments/{comment}` | View a comment |
| `PUT/PATCH` | `/comments/{comment}` | Update the author's comment |
| `DELETE` | `/comments/{comment}` | Soft-delete the author's comment |

Comments use a polymorphic relation so the same table supports projects and tasks.

### Tags

| Method | Endpoint | Description |
|---|---|---|
| `GET/POST` | `/tags` | List or create user-owned tags |
| `GET` | `/tags/{tag}` | View a tag and usage counts |
| `PUT/PATCH` | `/tags/{tag}` | Update a tag |
| `DELETE` | `/tags/{tag}` | Detach and soft-delete a tag |
| `PUT/DELETE` | `/projects/{project}/tags/{tag}` | Attach or detach a project tag |
| `PUT/DELETE` | `/tasks/{task}/tags/{tag}` | Attach or detach a task tag |

Tag color is optional and uses `#RRGGBB`. Names are unique per user, and slugs are generated automatically. A tag cannot be attached across user accounts.

### Media

| Method | Endpoint | Description |
|---|---|---|
| `GET/POST` | `/projects/{project}/media` | List or upload project media |
| `GET/POST` | `/tasks/{task}/media` | List or upload task media |
| `GET/POST` | `/comments/{comment}/media` | List or upload comment media |
| `GET` | `/media/{media}` | View media metadata and URL |
| `PATCH` | `/media/{media}` | Rename media display name |
| `DELETE` | `/media/{media}` | Delete media record and files |

Uploads use `multipart/form-data` with a required `file` and optional `name`. Maximum file size is 10 MB.

Allowed extensions:

```text
jpg, jpeg, png, webp, pdf, txt, csv, doc, docx, xls, xlsx
```

### Activity Logs

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/activity-logs` | List current-user activity, optionally filtered by event |
| `GET` | `/activity-logs/{activityLog}` | View one owned activity entry |

Activity creation is dispatched to the `activity-logs` queue with retry and overlap protection.

### Overdue Notifications

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/notifications` | List notifications and mark the returned page as seen |
| `GET` | `/notifications/{notification}` | View and mark one notification as seen |

Accessing a notification sets:

```text
seen = 1
seen_at = current timestamp
```

The user dashboard reports notifications where `seen = 0` as `unread_notifications`.

## Administrator API

Admin routes require an authenticated, active account with role `admin`.

| Module | Method | Endpoint | Filters / Purpose |
|---|---|---|---|
| Dashboard | `GET` | `/admin/dashboard` | Platform-wide statistics |
| Users | `GET` | `/admin/users` | `role`, `status`, `search`, `per_page` |
| Users | `GET` | `/admin/users/{user}` | View role, status, and counts |
| Users | `PATCH` | `/admin/users/{user}` | Update `role` and/or `status` |
| Users | `DELETE` | `/admin/users/{user}` | Revoke tokens and soft-delete |
| Projects | `GET` | `/admin/projects` | `user_id`, `status`, `search`, `per_page` |
| Projects | `GET/PUT/PATCH/DELETE` | `/admin/projects/{project}` | Manage any project |
| Tasks | `GET` | `/admin/tasks` | `user_id`, `project_id`, `status`, `priority`, `overdue`, `search`, `per_page` |
| Tasks | `GET/PUT/PATCH/DELETE` | `/admin/tasks/{task}` | Manage any task |
| Tags | `GET` | `/admin/tags` | `user_id`, `search`, `per_page` |
| Tags | `GET/PUT/PATCH/DELETE` | `/admin/tags/{tag}` | Manage any tag |

Admin activity is recorded through the same queued activity-log cycle. User endpoints remain ownership-scoped even for administrators; platform-wide management must use `/api/v1/admin/*`.

## Database Design

Main relations:

```text
User
|- hasMany Projects
|- hasMany Comments
|- hasMany Tags
|- hasMany Activity Logs
`- hasMany Overdue Task Notifications

Project
|- belongsTo User
|- hasMany Tasks
|- morphMany Comments
|- morphMany Media
|- morphMany Activity Logs
`- morphToMany Tags

Task
|- belongsTo Project
|- morphMany Comments
|- morphMany Media
|- morphMany Activity Logs
|- morphToMany Tags
`- hasMany Overdue Task Notifications

Comment
|- belongsTo User
|- morphTo Project or Task
`- morphMany Media
```

Primary application tables:

```text
users
projects
tasks
comments
tags
taggables
media
activity_logs
overdue_task_notifications
personal_access_tokens
jobs / failed_jobs / job_batches
```

Projects, tasks, comments, tags, and users support soft deletion where appropriate.

## Architecture

```text
HTTP Request
    -> Middleware (Sanctum, active account, admin when required)
    -> Form Request validation
    -> Controller (HTTP orchestration and authorization)
    -> Service (business rules and transactions)
    -> Repository Interface
    -> Eloquent Repository
    -> Models / Database
    -> API Resource
    -> ApiTrait consistent JSON response
```

- **Controllers** stay focused on HTTP concerns.
- **Services** own business rules, transactions, and queued activity dispatch.
- **Repositories** encapsulate Eloquent queries and pagination.
- **Policies** enforce ownership for projects, tasks, comments, tags, media, logs, and notifications.
- **Resources** expose only intended API fields.
- **Enums** centralize project status, task status/priority, and user role/status values.
- **Jobs** handle work that should not delay the request cycle.

## Queue, Scheduler, and Seeders

Background queues used by the project:

```text
activity-logs
notifications
seeding
default
```

Run a worker locally:

```bash
php artisan queue:work --queue=activity-logs,notifications,seeding,default --tries=3
```

The database seeder dispatches an idempotent sample-data job:

```bash
php artisan db:seed
```

Keep the worker running so the `seeding` queue is processed.

Overdue-task detection is scheduled hourly. Run the local scheduler in a separate terminal:

```bash
php artisan schedule:work
```

Production cron should execute:

```cron
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

Queue jobs use retry/backoff and overlap protection where needed. Duplicate overdue notifications and duplicate sample seeding are prevented.

## Media Storage

Spatie Media Library stores each model type in an isolated directory:

```text
storage/app/public/projects/{project_id}/{media_id}/
storage/app/public/tasks/{task_id}/{media_id}/
storage/app/public/comments/{comment_id}/{media_id}/
```

Create the public symlink once:

```bash
php artisan storage:link
```

## Postman

Import:

```text
postman/Task-Management-API.postman_collection.json
```

The collection contains two top-level cycles:

```text
User  -> 44 requests using {{user_token}}
Admin -> 19 requests using {{admin_token}}
```

Set:

```text
base_url = http://127.0.0.1:8000
```

User Login and Admin Login save separate tokens automatically. Every request includes documentation for required fields, optional fields, enum values, filters, and path variables.

## Testing

Run all automated tests:

```bash
php artisan test
```

Current suite:

```text
42 tests passed
236 assertions
```

Coverage includes:

- authentication, logout, validation, and rate limiting
- user/admin middleware and suspended accounts
- projects, tasks, filters, search, and pagination
- comments, tags, media, and ownership isolation
- dashboard and unread notification statistics
- activity logs and overdue notifications
- admin management and last-admin protection
- queued idempotent seeders

Check formatting:

```bash
vendor/bin/pint --test
```

On Windows:

```powershell
vendor\bin\pint --test
```

## Production Checklist

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan queue:work --queue=activity-logs,notifications,seeding,default --tries=3
```

Also:

- set `APP_ENV=production` and `APP_DEBUG=false`
- use a strong application key and database credentials
- configure a real mail provider only in `.env`
- run queue workers under Supervisor or another process monitor
- configure the scheduler cron entry
- serve the application over HTTPS
- rotate development credentials before deployment

## Repository

GitHub: [kariemibrahiem/task_manager](https://github.com/kariemibrahiem/task_manager)

## License

This project is available under the MIT License.
