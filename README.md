# StockFlow — Real-time Stock Tracking with Concurrent Safety

Sistem manajemen inventori full-stack untuk INaAI Competition 2026 (Full-stack Developer Track).
StockFlow mengelola produk, kategori, stok masuk/keluar, riwayat transaksi, dashboard,
notifikasi stok rendah, dan AI-driven analytics — dengan fokus pada **race condition handling**,
**event-driven architecture**, dan **caching** yang bisa dijelaskan baris per baris.

> Filosofi: *Build less, understand more.* Setiap keputusan teknis punya alasan.

---

## 1. Tech Stack

| Layer | Teknologi | Alasan |
|---|---|---|
| Frontend | Next.js (App Router) + TypeScript + Tailwind | Type-safe, build cepat, modern routing |
| Backend | Laravel + Sanctum | Queue, event, cache built-in; productive |
| Database | PostgreSQL 16 | Transaksi ACID + optimistic locking |
| Queue + Cache | Redis 7 | Satu service untuk queue (DB0) & cache (DB1) |
| Worker | Laravel Queue Worker | Memproses background job async |
| AI/LLM | OpenAI-compatible API | Insight naratif dari data inventori teragregasi |
| Container | Docker Compose | 5 service, satu perintah jalan |

Versi pasti ada di [backend/composer.json](backend/composer.json) dan [frontend/package.json](frontend/package.json).

---

## 2. Arsitektur

```
Browser
  │
  ▼
Next.js Frontend (:3000)
  │  HTTP + Bearer token
  ▼
Laravel API (:8000) ──────────────┐
  │ query/write          dispatch │ job
  ▼                               ▼
PostgreSQL (:5432)          Redis (:6379)
                                  │ consume
                                  ▼
                          Laravel Worker
                                  │ create notification / audit
                                  ▼
                            PostgreSQL
```

5 container Docker:

| Container | Fungsi | Port |
|---|---|---|
| `inaai_frontend` | Next.js | 3000 |
| `inaai_backend` | Laravel (Nginx + PHP-FPM via Supervisord) | 8000 |
| `inaai_worker` | Laravel Queue Worker (image sama dgn backend) | — |
| `inaai_postgres` | PostgreSQL | 5432 |
| `inaai_redis` | Redis (queue + cache) | 6379 |

---

## 3. Cara Menjalankan

### Prasyarat
Docker Desktop berjalan. Tidak perlu install PHP/Node/Composer di host.

### Langkah

```bash
# 1. Nyalakan semua service dari clean state
docker compose up -d --build

# 2. Migrasi + seed data demo (produk, kategori, akun)
docker exec inaai_backend php artisan migrate --force
docker exec inaai_backend php artisan db:seed --force

# 3. Verifikasi health check
curl http://localhost:8000/api/health
# → {"status":"healthy","services":{"app":"ok","database":"ok","redis":"ok"}}
```

Buka frontend di **http://localhost:3000**.

### URL Penting

| Layanan | URL |
|---|---|
| Frontend | http://localhost:3000 |
| Backend API | http://localhost:8000/api |
| Health check | http://localhost:8000/api/health |
| Swagger API docs | http://localhost:8000/api/documentation |

---

## 4. Demo Account

| Role | Email | Password |
|---|---|---|
| Admin | `admin@stockflow.com` | `password` |
| Staff | `staff@stockflow.com` | `password` |

Data seed menyertakan 5 kategori dan 15 produk, beberapa sengaja low-stock
(mis. *Mineral Water 1.5L* stok 2 / threshold 30) untuk demo notifikasi.

---

## 5. Race Condition Handling — Optimistic Locking

Stok keluar memakai kolom `version` di tabel `products`. Frontend mengirim `version`
yang dilihatnya; backend hanya update jika `version` masih cocok:

```sql
UPDATE products
SET stock = stock - ?, version = version + 1
WHERE id = ? AND version = ? AND stock >= ?
```

Jika `affected = 0` → data sudah berubah user lain → backend balas **409 Conflict**
dengan kode `STOCK_CONFLICT`. UI menampilkan "Data telah berubah, silakan refresh".

Implementasi: [StockUpdateService.php](backend/app/Services/StockUpdateService.php).
Optimistic dipilih karena konflik *mungkin* terjadi tapi tidak selalu — lebih ringan
dari pessimistic locking dan tidak mem-block row.

**Demo 2 browser:** dua user stock-out produk sama bersamaan → satu 200, satu 409.

---

## 6. Event-Driven Flow

Stock-out memberi response cepat ke user; kerja berat dilempar ke worker:

```
POST /stock-out
  → DB::transaction: update stock + insert stock_transaction (sinkron, kecil)
  → dispatch StockUpdatedJob ke Redis queue
  → return 200 ke user (cepat)

Worker (async):
  StockUpdatedJob → audit log + cek threshold
    → jika stok ≤ threshold: LowStockNotificationJob → buat notification di DB
```

Jobs: [StockUpdatedJob.php](backend/app/Jobs/StockUpdatedJob.php),
[LowStockNotificationJob.php](backend/app/Jobs/LowStockNotificationJob.php).

---

## 7. Caching Strategy

| Data | Key | TTL | Invalidasi |
|---|---|---|---|
| Product catalog | `products:all` | 5 menit | saat produk created/updated/deleted |
| Category list | `categories:all` | 10 menit | saat kategori berubah |
| Dashboard summary | `dashboard:summary` | 60 detik | saat transaksi stok |
| AI insights | `analytics:insights` | 1 jam | regenerate manual |

**Tidak di-cache:** stock quantity & version untuk validasi transaksi — harus selalu
akurat dari DB agar tidak overselling / double deduction.

Cache di Redis DB1 (prefix `laravel-database-laravel-cache-`), queue di DB0.
Verifikasi: `docker exec inaai_redis redis-cli -n 1 KEYS "*"`.

---

## 8. AI-Driven Analytics

`GET /api/analytics/insights` mengagregasi data inventori (daftar low-stock, top
transaksi keluar 7 hari, jumlah transaksi hari ini), menyusun prompt terstruktur,
memanggil LLM, dan mengembalikan insight naratif. Hasil di-cache 1 jam;
`POST /api/analytics/insights/regenerate` memaksa refresh.

Data dikirim sudah teragregasi (bukan raw) untuk hemat token & jaga privasi.
Implementasi: [AnalyticsService.php](backend/app/Services/AnalyticsService.php).

> Catatan latency: generation pertama bisa beberapa detik (panggil LLM); request
> berikutnya diambil dari cache. Tidak diklaim real-time.

---

## 9. API Endpoints

```
POST   /api/auth/login              POST   /api/auth/logout      GET /api/auth/me

GET    /api/products                POST   /api/products
GET    /api/products/{id}           PUT    /api/products/{id}    DELETE /api/products/{id}

POST   /api/products/{id}/stock-in
POST   /api/products/{id}/stock-out     ← optimistic locking, 409 on conflict
POST   /api/products/{id}/adjust-stock
GET    /api/products/{id}/transactions

GET    /api/categories             POST/PUT/DELETE /api/categories/{id}

GET    /api/dashboard/summary
GET    /api/notifications          PATCH /api/notifications/{id}/read

GET    /api/analytics/insights     POST  /api/analytics/insights/regenerate
```

Dokumentasi lengkap (request/response schema + auth) di Swagger UI:
**http://localhost:8000/api/documentation**.

---

## 10. Testing (TDD)

Test ditulis **sebelum** implementasi — terbukti dari commit history
(`test:` lalu `feat:`). Test diisolasi ke SQLite in-memory agar tidak menyentuh
PostgreSQL produksi.

```bash
docker exec inaai_backend php artisan test
# → 43 passed
```

---

## 11. Structured Logging

Setiap operasi stok dan job menulis log terstruktur dengan key konsisten:
`stock.updated`, `stock.conflict`, `job.processed`, `analytics.insight_generated`,
`analytics.llm_error`. Investigasi error mulai dari:

```bash
docker compose logs backend --tail=50
docker compose logs worker --tail=50
```

---

## 12. Command Harian

```bash
docker compose up -d                    # nyalakan
docker compose ps                       # status
docker compose logs -f worker           # log worker
docker exec inaai_backend php artisan [cmd]
docker compose down                     # matikan
docker compose down -v                  # matikan + hapus volume (clean state)
```

---

## 13. Dokumentasi Tambahan

- [AI Usage Log](backend/docs/AI_USAGE_LOG.md) — catatan interaksi AI coding tools
- [CLAUDE.md](CLAUDE.md) — project context & development guide

---

## 14. Limitasi yang Disadari

- Role management sederhana (admin/staff), belum granular per-permission.
- Monitoring memakai Laravel log file, bukan APM eksternal.
- AI insight bergantung pada LLM eksternal; latency tidak dijamin real-time.
- Deployment free tier untuk demo — cold start & resource limit bisa membuat
  latency tidak stabil. Tidak diklaim production-grade.

---

## 15. Atribusi Open-source

- [Laravel](https://laravel.com), [Laravel Sanctum](https://laravel.com/docs/sanctum),
  [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger)
- [Next.js](https://nextjs.org), [Tailwind CSS](https://tailwindcss.com), [axios](https://axios-http.com)
- Image Docker resmi: PostgreSQL 16, Redis 7
