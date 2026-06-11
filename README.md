# INaAI Competition 2026 — Full-stack Developer Boilerplate

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | Next.js 14 (App Router) |
| Backend | Laravel 11 |
| Queue + Cache | Redis 7 |
| Database | PostgreSQL 16 |
| Container | Docker Compose |

## Services

```
inaai_frontend   → http://localhost:3000   (Next.js)
inaai_backend    → http://localhost:8000   (Laravel API)
inaai_worker     → background queue worker
inaai_postgres   → localhost:5432
inaai_redis      → localhost:6379
```

## Quick Start

### 1. Clone & setup backend

```bash
# Install Laravel (jika belum ada folder backend/vendor)
cd backend
composer install   # atau lewati, Docker yang handle

# Copy env
cp .env.example .env
```

### 2. Setup frontend

```bash
cd frontend
npx create-next-app@latest . --typescript --tailwind --app --src-dir --import-alias "@/*"
```

### 3. Jalankan semua service

```bash
# Dari root folder
docker compose up --build

# Pertama kali, generate app key + migrate
docker exec inaai_backend php artisan key:generate
docker exec inaai_backend php artisan migrate
```

### 4. Verifikasi

```bash
# Backend health check
curl http://localhost:8000/api/health

# Frontend
open http://localhost:3000

start http://localhost:3000
```

## Struktur Folder

```
inaai-boilerplate/
├── docker-compose.yml
├── backend/                  # Laravel app
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Jobs/             # Queue jobs (event-driven)
│   │   ├── Events/           # Laravel events
│   │   ├── Listeners/        # Event listeners
│   │   └── Models/
│   ├── docker/
│   │   ├── nginx.conf
│   │   └── supervisord.conf
│   └── Dockerfile
├── frontend/                 # Next.js app
│   ├── src/
│   │   ├── app/              # App Router pages
│   │   ├── components/
│   │   └── lib/              # API client, utils
│   └── Dockerfile
└── README.md
```

## Event-Driven Pattern (Must Have #3)

Laravel sudah include event/listener system. Contoh flow:

```
User action (HTTP request)
  → Controller dispatch Event
  → Event masuk Redis Queue
  → Worker service consume Job
  → Hasil update database / broadcast
```

Implementasi di `backend/app/Events/` dan `backend/app/Jobs/`.

## Race Condition Handling (Must Have #4)

PostgreSQL + Laravel mendukung:
- `lockForUpdate()` → pessimistic locking
- `optimisticLock` via version column
- Database transactions dengan `DB::transaction()`

## Open-source Boilerplates Digunakan

- [Laravel](https://laravel.com) — PHP framework
- [create-next-app](https://nextjs.org/docs/app/api-reference/cli/create-next-app) — Next.js scaffolding
- Redis official Docker image
- PostgreSQL official Docker image
