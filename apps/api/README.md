# YA Academico API

Headless REST API for academic management — part of the **ya-academico** monorepo (Turborepo).

Built with Laravel 13, Sanctum (token auth), Spatie Permissions, and OpenSpout (Excel import).

## Architecture

```
apps/api/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/  ← Versioned controllers
│   │   ├── Requests/Api/V1/     ← Form Request validation
│   │   └── Resources/Api/V1/    ← API Resources (snake→camelCase)
│   ├── Jobs/                     ← Queued import jobs (OpenSpout)
│   └── Models/                   ← Eloquent models
├── bootstrap/app.php             ← Middleware, CORS, exception handler, rate limiter
├── config/                       ← App config (cors, sanctum, permission, etc.)
├── database/
│   ├── migrations/               ← All table schemas
│   └── seeders/                  ← Roles/permissions + demo users
└── routes/api.php                ← All routes under api/v1/
```

## Requirements

- PHP 8.3+
- SQLite (local) / MySQL 8+ or PostgreSQL 16+ (production)
- Composer
- PHP extensions: `pdo_sqlite`, `sqlite3`, `mbstring`, `xml`, `zip`

## Setup

```bash
cd apps/api

# Install dependencies
composer install

# Environment
cp .env.example .env
# Edit .env: set DB_*, APP_KEY already generated

# Ensure SQLite extension is enabled in php.ini:
#   extension=pdo_sqlite
#   extension=sqlite3

# Run migrations and seeders
php artisan migrate:fresh --seed

# Start dev server
php artisan serve
```

### First time setup

If you get `Class "Redis" not found` or connection refused on `127.0.0.1:6379`, ensure the `.env` has `REDIS_CLIENT=predis` (set by default). The `predis/predis` package is installed and handles the Laravel rate limiter without a running Redis server.

## API

All routes are prefixed with `/api/v1/`. Authentication via **Bearer token** (Sanctum).

### Authentication

| Method | Endpoint             | Auth     | Description          |
|--------|----------------------|----------|----------------------|
| POST   | `/auth/login`        | No       | Login → returns token|
| POST   | `/auth/logout`       | Sanctum  | Revoke current token |
| GET    | `/auth/me`           | Sanctum  | Current user + roles |

### Dashboard

| Method | Endpoint                | Auth     | Description             |
|--------|-------------------------|----------|-------------------------|
| GET    | `/dashboard/metrics`    | Sanctum  | Aggregate counts        |

### Courses

| Method | Endpoint                        | Auth     | Description              |
|--------|---------------------------------|----------|--------------------------|
| GET    | `/courses`                      | Sanctum  | List (paginated)         |
| POST   | `/courses`                      | Sanctum  | Create                   |
| GET    | `/courses/{course}`             | Sanctum  | Show                     |
| PUT    | `/courses/{course}`             | Sanctum  | Update                   |
| DELETE | `/courses/{course}`             | Sanctum  | Soft delete              |
| GET    | `/courses/all/active`           | Sanctum  | All active courses       |
| POST   | `/courses/{course}/duplicate`   | Sanctum  | Duplicate with classes   |
| PATCH  | `/courses/{id}/restore`         | Sanctum  | Restore soft-deleted     |
| DELETE | `/courses/{id}/force`           | Sanctum  | Force delete             |

### Students

| Method | Endpoint                              | Auth     | Description              |
|--------|---------------------------------------|----------|--------------------------|
| GET    | `/students`                           | Sanctum  | List (paginated)         |
| POST   | `/students`                           | Sanctum  | Create                   |
| GET    | `/students/{student}`                 | Sanctum  | Show                     |
| PUT    | `/students/{student}`                 | Sanctum  | Update                   |
| DELETE | `/students/{student}`                 | Sanctum  | Soft delete              |
| PATCH  | `/students/{student}/toggle-status`   | Sanctum  | Activate/deactivate      |
| PATCH  | `/students/{id}/restore`              | Sanctum  | Restore soft-deleted     |
| DELETE | `/students/{id}/force`                | Sanctum  | Force delete             |

### Trainers

| Method | Endpoint                              | Auth     | Description              |
|--------|---------------------------------------|----------|--------------------------|
| GET    | `/trainers`                           | Sanctum  | List (paginated)         |
| POST   | `/trainers`                           | Sanctum  | Create                   |
| GET    | `/trainers/{trainer}`                 | Sanctum  | Show                     |
| PUT    | `/trainers/{trainer}`                 | Sanctum  | Update                   |
| DELETE | `/trainers/{trainer}`                 | Sanctum  | Soft delete              |
| PATCH  | `/trainers/{trainer}/toggle-status`   | Sanctum  | Activate/deactivate      |
| PATCH  | `/trainers/{id}/restore`              | Sanctum  | Restore soft-deleted     |
| DELETE | `/trainers/{id}/force`                | Sanctum  | Force delete             |

### School Classes

| Method | Endpoint                               | Auth     | Description              |
|--------|----------------------------------------|----------|--------------------------|
| GET    | `/school-classes`                      | Sanctum  | List (paginated)         |
| POST   | `/school-classes`                      | Sanctum  | Create                   |
| GET    | `/school-classes/{school_class}`       | Sanctum  | Show                     |
| PUT    | `/school-classes/{school_class}`       | Sanctum  | Update                   |
| DELETE | `/school-classes/{school_class}`       | Sanctum  | Soft delete              |
| PATCH  | `/school-classes/{id}/restore`         | Sanctum  | Restore soft-deleted     |
| DELETE | `/school-classes/{id}/force`           | Sanctum  | Force delete             |

### Fees

| Method | Endpoint                  | Auth     | Description              |
|--------|---------------------------|----------|--------------------------|
| GET    | `/fees`                   | Sanctum  | List (paginated)         |
| POST   | `/fees`                   | Sanctum  | Create                   |
| GET    | `/fees/{fee}`             | Sanctum  | Show                     |
| PUT    | `/fees/{fee}`             | Sanctum  | Update                   |
| DELETE | `/fees/{fee}`             | Sanctum  | Soft delete              |
| PATCH  | `/fees/{id}/restore`      | Sanctum  | Restore soft-deleted     |
| DELETE | `/fees/{id}/force`        | Sanctum  | Force delete             |

### Payments

| Method | Endpoint                                    | Auth     | Description              |
|--------|---------------------------------------------|----------|--------------------------|
| GET    | `/payments`                                 | Sanctum  | List (paginated)         |
| POST   | `/payments`                                 | Sanctum  | Create                   |
| GET    | `/payments/{payment}`                       | Sanctum  | Show                     |
| PUT    | `/payments/{payment}`                       | Sanctum  | Update                   |
| DELETE | `/payments/{payment}`                       | Sanctum  | Soft delete              |
| GET    | `/payments/student/{studentId}/records`     | Sanctum  | Payments by student      |
| GET    | `/payments/student/{studentId}/summary`     | Sanctum  | Summary by student       |
| PATCH  | `/payments/{id}/restore`                    | Sanctum  | Restore soft-deleted     |
| DELETE | `/payments/{id}/force`                      | Sanctum  | Force delete             |

### Requests (Pedidos)

| Method | Endpoint                                 | Auth     | Description              |
|--------|------------------------------------------|----------|--------------------------|
| GET    | `/requests`                              | Sanctum  | List (paginated)         |
| POST   | `/requests`                              | Sanctum  | Create                   |
| GET    | `/requests/{request}`                    | Sanctum  | Show                     |
| PUT    | `/requests/{request}`                    | Sanctum  | Update                   |
| DELETE | `/requests/{request}`                    | Sanctum  | Soft delete              |
| POST   | `/requests/{requestEntry}/approve`       | Sanctum  | Approve request          |
| POST   | `/requests/{requestEntry}/deny`          | Sanctum  | Deny request             |
| PATCH  | `/requests/{id}/restore`                 | Sanctum  | Restore soft-deleted     |
| DELETE | `/requests/{id}/force`                   | Sanctum  | Force delete             |

### Users

| Method | Endpoint                          | Auth     | Description              |
|--------|-----------------------------------|----------|--------------------------|
| GET    | `/users`                          | Sanctum  | List (paginated)         |
| POST   | `/users`                          | Sanctum  | Create                   |
| GET    | `/users/{user}`                   | Sanctum  | Show                     |
| PUT    | `/users/{user}`                   | Sanctum  | Update                   |
| DELETE | `/users/{user}`                   | Sanctum  | Soft delete              |
| PATCH  | `/users/{user}/toggle-status`     | Sanctum  | Activate/deactivate      |
| PATCH  | `/users/{id}/restore`             | Sanctum  | Restore soft-deleted     |
| DELETE | `/users/{id}/force`               | Sanctum  | Force delete             |

### Bulk Import (Excel/CSV)

All accept a `file` (xlsx, xls, csv) via multipart/form-data (max 10MB). Processed asynchronously via queue.

| Method | Endpoint                    | Columns (row 1 = header, skipped)                                                    |
|--------|-----------------------------|---------------------------------------------------------------------------------------|
| POST   | `/import/students`          | `student_number`, `name`, `email`, `phone`, `birth_date`, `enrollment_date`           |
| POST   | `/import/trainers`          | `name`, `email`, `phone`, `specialty`                                                  |
| POST   | `/import/school-classes`    | `name`, `course_id`, `shift`, `status`, `start_date`, `end_date`                       |
| POST   | `/import/courses`           | `name`, `description`, `duration_months`, `tuition`, `is_active`                        |
| POST   | `/import/fees`              | `name`, `type`, `amount`, `course_id`, `is_active`                                      |
| POST   | `/import/payments`          | `student_id`, `course_id`, `reference_month`, `amount`, `status`, `method`, `payment_date`, `due_date` |

## Default Users

| Email                         | Password | Role                  |
|-------------------------------|----------|-----------------------|
| admin@ya-academico.com        | password | administrator         |
| secretary@ya-academico.com    | password | secretary             |
| financial@ya-academico.com    | password | financial             |
| coordinator@ya-academico.com  | password | academic_coordinator  |

## Database Schema (Relationships)

```
Course ──hasMany──▶ SchoolClass ──belongsToMany──▶ Trainer
                                ──belongsToMany──▶ Student
       ──hasMany──▶ Fee
       ──hasMany──▶ Payment (also belongsTo Student)

Student ──belongsToMany──▶ SchoolClass
        ──hasMany──▶ Payment
        ──hasMany──▶ RequestEntry

Trainer ──belongsToMany──▶ SchoolClass
```

## Rate Limiting

- 60 requests per minute per IP (applied globally to all `/api/*` routes).

## CORS

Allowed origins (configurable in `config/cors.php`):
- `http://localhost:3000` (admin)
- `http://localhost:3001` (portal)

## API Documentation (Scramble)

OpenAPI 3.1.0 spec auto-generated at `/docs/api.json` when the server is running. Uses Bearer token auth.

```bash
# View the spec
curl http://localhost:8000/docs/api.json
```

## Production Deployment

### 1. Server Requirements

- PHP 8.3+ with extensions: `pdo`, `pdo_mysql` or `pdo_pgsql`, `mbstring`, `xml`, `zip`, `bcmath`
- MySQL 8+ or PostgreSQL 16+
- Composer
- Supervisor (for queue worker)

### 2. Deploy

```bash
cd /var/www/ya-academico-api

composer install --no-dev --optimize-autoloader

# Environment
cp .env.example .env
# Edit .env: set DB_*, APP_ENV=production, APP_DEBUG=false, APP_KEY=...

# Migrate
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache

# Queue worker (via Supervisor)
php artisan queue:work --daemon &
```

### 3. Nginx

```nginx
server {
    listen 80;
    server_name api.ya-academico.com;
    root /var/www/ya-academico-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4. Supervisor (Queue Worker)

```ini
[program:ya-academico-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ya-academico-api/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ya-academico-api/storage/logs/worker.log
stopwaitsecs=3600
```

### 5. Health Check

```
GET /up
```

## Development

```bash
# Start queue worker (needed for imports in local dev)
php artisan queue:work

# Generate API docs
php artisan scramble:generate

# Run tests
php artisan test

# List all routes
php artisan route:list --path=api
```
