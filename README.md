# Task Management API

A clean Laravel 11 REST API for managing personal projects and tasks. It includes token authentication, authorization, searchable task lists, dashboard statistics, comments, media attachments, activity logs, and queued background work.

## What is included?

- Laravel Sanctum authentication: register, login, and logout
- User-owned projects with full CRUD operations
- Project tasks with status, priority, due dates, search, filters, and pagination
- Dashboard statistics, including pending and overdue tasks
- Polymorphic comments for projects and tasks
- Secure media attachments powered by Spatie Media Library
- Queued activity logging
- Form Requests, API Resources, Policies, Services, and Repositories
- Consistent JSON responses and API error handling
- Factories, queued sample-data seeders, and feature tests
- Versioned endpoints under `/api/v1`

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+ or SQLite
- PHP extensions required by Laravel

## Quick start

```bash
git clone <repository-url>
cd Task_management
composer install
cp .env.example .env
php artisan key:generate
```

Create a database, update the database variables in `.env`, then run:

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000/api/v1
```

> Keep `APP_URL` equal to the public application URL. Spatie Media Library uses it when generating attachment URLs.

## Environment setup

MySQL example:

```dotenv
APP_NAME="Task Management API"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
```

Mail credentials are intentionally excluded from the repository. Add the credentials for your own mail provider only to `.env`; never commit them.

## Run background jobs

Activity logs, sample-data seeding, and notifications use Laravel queues. Keep a worker running during development:

```bash
php artisan queue:work --tries=3
```

To load sample data, dispatch the queued seeder and let the worker process it:

```bash
php artisan db:seed
```

For scheduled jobs, run this locally in a separate terminal:

```bash
php artisan schedule:work
```

## Authentication

Register or log in to receive a Sanctum token. Send it with protected requests:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

Example login request:

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "developer@example.com",
  "password": "password",
  "device_name": "Postman"
}
```

## Main endpoints

| Module | Method and endpoint | Purpose |
|---|---|---|
| Auth | `POST /auth/register` | Create an account |
| Auth | `POST /auth/login` | Create an access token |
| Auth | `POST /auth/logout` | Revoke the current token |
| Dashboard | `GET /dashboard` | Return the user's statistics |
| Projects | `GET/POST /projects` | List or create projects |
| Projects | `GET/PUT/PATCH/DELETE /projects/{project}` | Manage one project |
| Tasks | `GET/POST /projects/{project}/tasks` | List or create project tasks |
| Tasks | `GET/PUT/PATCH/DELETE /tasks/{task}` | Manage one task |
| Comments | `GET/POST /projects/{project}/comments` | Project comments |
| Comments | `GET/POST /tasks/{task}/comments` | Task comments |
| Comments | `GET/PUT/PATCH/DELETE /comments/{comment}` | Manage one comment |
| Media | `GET/POST /projects/{project}/media` | Project attachments |
| Media | `GET/POST /tasks/{task}/media` | Task attachments |
| Media | `GET/POST /comments/{comment}/media` | Comment attachments |
| Media | `GET/PATCH/DELETE /media/{media}` | View, rename, or delete media |
| Activity | `GET /activity-logs` | List the user's activity |
| Activity | `GET /activity-logs/{activityLog}` | View one activity entry |
| Notifications | `GET /notifications` | List notifications and mark the returned page as seen |
| Notifications | `GET /notifications/{notification}` | View one notification and mark it as seen |

All paths in the table are relative to `/api/v1`.

Task listing supports these optional query parameters:

```text
status=todo|in_progress|done
priority=low|medium|high
search=title keywords
per_page=15
```

## Response contract

Every endpoint follows the same predictable shape:

```json
{
  "success": true,
  "message": "Request completed successfully.",
  "data": {}
}
```

Errors use the same contract and include `errors` when validation details are available. Empty data is returned as `[]`.

## Architecture at a glance

```text
HTTP Request
    ↓
Form Request (validation)
    ↓
Controller (HTTP orchestration + authorization)
    ↓
Service (business rules)
    ↓
Repository Interface → Eloquent Repository
    ↓
Models / Database
    ↓
API Resource → Consistent JSON response
```

- **Policies** prevent users from accessing projects, tasks, comments, media, or logs they do not own.
- **Services** contain business logic and keep controllers small.
- **Repository interfaces** separate data access from business logic.
- **API Resources** control what is exposed to clients.
- **Jobs** move non-blocking work away from the request lifecycle.

## Media storage

Attachments are isolated by owner model and record:

```text
storage/app/public/projects/{project_id}/{media_id}/
storage/app/public/tasks/{task_id}/{media_id}/
storage/app/public/comments/{comment_id}/{media_id}/
```

Run `php artisan storage:link` once so public URLs can serve these files.

## Tests and code style

```bash
php artisan test
./vendor/bin/pint --test
```

The feature suite covers authentication, ownership policies, projects, tasks, filters, dashboard statistics, comments, media, activity logs, pagination, validation, rate limiting, and queued seeders.

## Postman

Import the collection from:

```text
postman/Task-Management-API.postman_collection.json
```

Set `base_url` to `http://127.0.0.1:8000`. The login request stores the returned token for authenticated requests.

## Useful production commands

```bash
php artisan migrate --force
php artisan optimize
php artisan queue:work --tries=3
```

In production, run the queue worker under a process monitor and execute `php artisan schedule:run` every minute through cron.

## License

This project is available under the MIT License.
