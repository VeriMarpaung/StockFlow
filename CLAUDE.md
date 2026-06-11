# StockFlow — Project Context & Development Guide

> **PENTING — Dokumen wajib pendamping:**
> Dokumen ini HARUS dibaca berdampingan dengan dokumen teknis resmi kompetisi
> (`Peserta_FullStack.md` — INaAI Competition 2026 Full-stack Developer Track).
> Dokumen kompetisi adalah sumber kebenaran utama untuk kriteria penilaian, probe
> question juri, aturan AI policy, dan ketentuan submission. Section 3 di bawah hanya
> RINGKASAN; jika ada perbedaan, dokumen kompetisi yang berlaku.
>
> **Cara membaca dokumen ini:**
> - Baca seluruh dokumen sebelum menulis kode apapun.
> - Ikuti fase pengembangan (section 26) secara berurutan.
> - Jangan lanjut ke fase berikutnya sebelum validasi fase sebelumnya passed.
>
> **Sumber kebenaran versi dependency:**
> Versi teknologi di dokumen ini ditulis pada level mayor saja (mis. "Next.js App
> Router", "Laravel 11", "Redis 7+"). Untuk versi pasti, SELALU rujuk file
> `frontend/package.json` dan `backend/composer.json` di project. Jangan menulis kode
> atau config berdasarkan asumsi versi minor tertentu tanpa cek file tersebut.

---

## 1. Identitas Project

**Nama project:** StockFlow
**Jenis aplikasi:** Full-stack web application
**Track kompetisi:** INaAI Competition 2026 — Full-stack Developer Track
**Tagline:** Real-time stock tracking with concurrent safety.

**Deskripsi singkat:**
StockFlow adalah sistem manajemen inventori untuk mengelola produk, kategori, stok masuk, stok keluar, riwayat transaksi stok, dashboard inventori, dan notifikasi stok rendah. Project ini dipilih karena secara alami cocok untuk menunjukkan kemampuan full-stack engineering, terutama pada aspek event-driven architecture, race condition handling, caching, clean code, dan penggunaan AI coding tools secara bertanggung jawab.

**Filosofi desain:** Build less, understand more. Setiap baris kode harus bisa dijelaskan. Setiap keputusan teknis harus punya alasan.

---

## 2. Konteks Kompetisi

Kompetisi: INaAI Competition 2026 x IT Del — Full-stack Developer Track.
Tagline: Build, Pitch, Get Hired. Bersifat individu, bukan tim.

**Jadwal (11–12 Juni 2026, semua WIB):**
- Briefing & Topic Release: 11 Juni, 09:00–10:00
- Fase 1 (Planning & Proposal): 11 Juni, 10:00–14:00 (4 jam)
- Submit Draft Plan: 11 Juni, 14:00–14:30 (window 30 menit)
- Fase 2 (Coding & Implementation): 11–12 Juni, 14:30–13:00 (22,5 jam kalender; sudah mencakup waktu istirahat yang diatur sendiri, bukan target kerja nonstop)
- Submit Final Solusi: 12 Juni, 13:00–13:30 (window 30 menit)
- Presentasi & Penjurian: 12 Juni, 15:00–selesai
- Pengumuman: TBA di career.inaai.ai

**Topik resmi (Track Fullstack):** Aplikasi web lengkap (frontend + backend),
terintegrasi dengan AI/LLM, berjalan live di browser. Tech stack bebas. Yang dinilai:
kualitas eksekusi, kerapian kode, dan pemahaman sistem.

Deliverable Fase 2 (submit di window 13:00–13:30):
- GitHub repo public dengan tag `submission-final` di commit terakhir
- URL deployed yang accessible oleh juri (boleh basic auth, taruh credentials di README)
- Technical report PDF 2-3 halaman tanpa cover (arsitektur, keputusan teknis kunci,
  AI Usage Log, limitasi). LaTeX disarankan tidak wajib.

**Presentasi pakai materi dari deliverable di atas — tidak perlu submit slide terpisah.**

---

## 3. Kriteria Penilaian

> **PERHATIAN — kriteria ini sesuai dokumen resmi terbaru (11 Juni 2026).**
> TDD dan API Documentation adalah MUST HAVE (wajib, miss = gugur). Event-driven
> dan race condition turun menjadi Nice to Have. Ini berbeda dari draf awal.

### Must Have — semua wajib pass, satu miss = gugur

1. **Docker** — `docker compose up` dari clean state harus jalan
   - Probe juri: "Berapa menit sampai ready dan kenapa ordering Dockerfile begini?"
2. **Clean code** — DRY, YAGNI, SOLID. Siap tunjukkan 2 file terbaik
   - Probe juri: "Tunjukkan 2 file yang dibanggakan. SOLID principle mana yang paling kuat?"
3. **TDD (Test-Driven Development)** — test ditulis SEBELUM implementasi
   - Probe juri: "Pilih satu fitur. Tunjukkan test yang ditulis sebelum implementasi via commit history."
4. **API documentation (OpenAPI/Swagger)**
   - Probe juri: "Buka dokumentasi API. Pilih satu endpoint, jelaskan request/response schema dan auth requirement."
5. **Effective use of AI coding tools** — wajib ada AI Usage Log
   - Probe juri: "Tunjukkan AI Usage Log. Pilih satu interaksi paling impactful, jelaskan kenapa diterima/ditolak."

### Nice to Have — minimal 50% tercapai untuk lolos ranking

1. **Service management untuk event-driven architecture**
   - Probe juri: "Bagaimana service A komunikasi dengan service B? Tunjukkan event flow dan tools-nya."
2. **Race condition handling untuk concurrent operations**
   - Probe juri: "3 user edit data sama bersamaan. Walk me through bagaimana sistem handle conflict."
3. **CI/CD pipeline otomatis**
   - Probe juri: "Trigger pipeline dari satu commit. Berapa durasi commit ke deploy?"
4. **Caching mechanism**
   - Probe juri: "Tunjukkan endpoint yang pakai cache. Apa strategy dan invalidation-nya?"
5. **Structured logging dan error monitoring**
   - Probe juri: "Tunjukkan log file. Kalau ada error production, dari mana mulai investigate?"

### Extraordinary — poin bonus ranking

1. **Multi-service event-driven architecture (3+ service terpisah)**
   - Probe juri: "Apa trade-off saat split jadi banyak service vs monolith?"
2. **Concurrent edit handling dengan locking strategy yang proper**
   - Probe juri: "Demo 2 browser edit data sama. Optimistic atau pessimistic locking?"
3. **AI-driven analytics yang menghasilkan insight useful**
   - Probe juri: "Show me analytics output dari sample data. Insight apa yang tidak obvious dari raw data?"
4. **End-to-end feature flow yang smooth**
   - Probe juri: "Walk me through full flow sampai AI generate analytics. Berapa latency, di mana bottleneck?"

**Rumus ranking:** `Skor = (Nice to Have tercapai) + 2 × (Extraordinary tercapai)`

### Implikasi Strategis untuk StockFlow

- **TDD naik ke Must Have:** WAJIB ada test sebelum implementasi, terbukti dari commit. Tidak bisa ditunda ke akhir. Setiap fitur inti minimal punya satu test yang di-commit sebelum kode implementasinya.
- **API Documentation naik ke Must Have:** WAJIB Swagger/OpenAPI. Bukan lagi opsi manual markdown. Pasang `darkaonline/l5-swagger` sejak awal.
- **Event-driven + race condition turun ke Nice to Have:** Tetap dikerjakan karena jadi keunggulan StockFlow dan mengisi 2 dari 5 Nice to Have sekaligus 2 Extraordinary. Tapi prioritas eksekusi sekarang di bawah TDD dan API docs.
- **Integrasi AI/LLM:** Topik resmi mensyaratkan aplikasi terintegrasi AI/LLM. StockFlow menambahkan AI-driven analytics (Extraordinary #3) sebagai pemenuhan syarat ini.

---

## 4. Klarifikasi tentang AI

Topik resmi Track Fullstack mensyaratkan aplikasi **terintegrasi dengan AI/LLM**.
StockFlow memenuhi ini lewat fitur AI-driven analytics (lihat section 9.7).

AI muncul dalam dua konteks berbeda:
1. **AI sebagai coding assistant** (Claude, Copilot, ChatGPT) — dipakai saat coding,
   wajib dicatat di AI Usage Log. Dinilai di Must Have #5.
2. **AI terintegrasi di aplikasi** — fitur AI-driven analytics di StockFlow yang
   menganalisis data inventori dan menghasilkan insight. Memenuhi syarat topik resmi
   sekaligus mengisi Extraordinary #3.

Fokus utama tetap full-stack engineering yang kuat. Fitur AI analytics dikerjakan
SETELAH semua Must Have selesai, sebagai layer di atas fondasi yang solid.

---

## 5. Tech Stack

| Layer | Teknologi | Alasan |
|---|---|---|
| Frontend | Next.js (App Router) + TypeScript + Tailwind CSS | App Router modern, type-safe, build cepat |
| Backend | Laravel API | Queue, event, cache built-in; familiar dan productive |
| Database | PostgreSQL | Mendukung transaksi ACID dan locking strategy |
| Queue + Cache | Redis | Satu service untuk dua kebutuhan |
| Worker | Laravel Queue Worker | Memproses background job |
| AI/LLM | Gemini/OpenAI-compatible API | Menghasilkan insight dari data inventori teragregasi |
| Container | Docker Compose | Wajib kompetisi |
| Deployment | Vercel + Railway/Render/Neon/Upstash | Cukup untuk demo kompetisi; bukan klaim performa production-grade |

> **Catatan versi:** Versi pasti ada di `frontend/package.json`, `backend/composer.json`, dan `docker-compose.yml`. Jangan menulis kode atau konfigurasi berdasarkan asumsi versi minor tertentu.
>
> **Catatan deployment & latency:** Free tier cukup untuk demo kompetisi, tetapi jangan mengklaim performa production-grade. Cold start, batas resource, dan layanan eksternal seperti LLM dapat membuat latency tidak stabil. Klaim latency harus dibedakan antara warm runtime, cache hit, dan request AI insight.

---

## 6. Kondisi Boilerplate

Lokasi project: `D:\Exercise\inaai-boilerplate`

**5 container Docker:**

| Container | Fungsi | Port |
|---|---|---|
| inaai_frontend | Next.js | 3000 |
| inaai_backend | Laravel + Nginx + PHP-FPM via Supervisord | 8000 |
| inaai_worker | Laravel Queue Worker (image sama backend) | — |
| inaai_postgres | PostgreSQL | 5432 |
| inaai_redis | Redis (queue + cache) | 6379 |

Health check sudah confirmed:
```json
{"status":"healthy","services":{"app":"ok","database":"ok","redis":"ok"}}
```

**File boilerplate yang sudah ada:**
- `docker-compose.yml` — 5 service terkonfigurasi
- `backend/Dockerfile` — multi-stage build, sudah fix issue autoconf
- `frontend/Dockerfile` — multi-stage build dengan output standalone
- `backend/.env` — terkonfigurasi ke PostgreSQL + Redis
- `backend/routes/api.php` — health check endpoint
- `backend/bootstrap/app.php` — sudah include api.php
- `backend/app/Jobs/ProcessDataJob.php` — skeleton, bisa dihapus
- `backend/app/Http/Controllers/ExampleController.php` — contoh locking, bisa dihapus
- `frontend/next.config.ts` — output standalone sudah diset

---

## 7. Command Harian

```powershell
# Nyalakan semua container
docker compose up -d

# Cek status
docker compose ps

# Lihat log backend
docker compose logs -f backend

# Lihat log worker
docker compose logs -f worker

# Matikan
docker compose down

# Rebuild jika ada perubahan Dockerfile
docker compose up -d --build

# Masuk shell backend
docker exec -it inaai_backend sh

# Artisan commands
docker exec inaai_backend php artisan [command]
```

---

## 8. Database Schema

### users
```
id, name, email, password, role (enum: admin|staff),
remember_token, created_at, updated_at
```

### categories
```
id, name, description (nullable), created_at, updated_at
```

### products
```
id, category_id (FK), name, sku (unique), description (nullable),
price (decimal 10,2), stock (integer default 0),
threshold (integer default 10),
version (integer default 0),   ← untuk optimistic locking
created_at, updated_at
```

### stock_transactions
```
id, product_id (FK), user_id (FK),
type (enum: in|out|adjustment),
quantity (integer),
stock_before (integer), stock_after (integer),
note (nullable),
created_at
```

### notifications
```
id, user_id (FK nullable),   ← nullable: notifikasi bisa untuk semua admin
type (string), title (string), message (text),
data (json nullable), read_at (timestamp nullable),
created_at, updated_at
```

**Catatan schema:**
- `sku` harus unique dan di-index
- `version` default 0, di-increment setiap update stok
- `notifications.user_id` nullable — kalau null artinya notifikasi untuk semua admin
- `stock_transactions` tidak punya `updated_at` karena transaksi tidak boleh diedit

---

## 9. API Endpoints

```
AUTH
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me

PRODUCTS (semua butuh auth)
GET    /api/products              ← cached Redis 5 menit
POST   /api/products
GET    /api/products/{id}
PUT    /api/products/{id}
DELETE /api/products/{id}

STOCK (semua butuh auth)
POST   /api/products/{id}/stock-in
POST   /api/products/{id}/stock-out    ← optimistic locking
POST   /api/products/{id}/adjust-stock
GET    /api/products/{id}/transactions

CATEGORIES (semua butuh auth)
GET    /api/categories                 ← cached Redis
POST   /api/categories
PUT    /api/categories/{id}
DELETE /api/categories/{id}

NOTIFICATIONS (butuh auth)
GET    /api/notifications
PATCH  /api/notifications/{id}/read

DASHBOARD (butuh auth)
GET    /api/dashboard/summary          ← cached Redis TTL pendek

ANALYTICS / AI (butuh auth)
GET    /api/analytics/insights         ← cached Redis 1 jam, calls LLM jika cache miss
POST   /api/analytics/insights/regenerate ← force refresh insight
```

---

## 10. Frontend Pages

```
/                   → redirect ke /dashboard
/login              → halaman login
/dashboard          → summary cards, low stock alert, recent transactions, AI insight panel
/products           → list produk, search, filter kategori
/products/new       → form tambah produk
/products/[id]      → detail produk + history transaksi
/stock-in           → form stok masuk
/stock-out          → form stok keluar + conflict handling UI
/notifications      → list notifikasi
/insights           → opsional; bisa digabung ke dashboard jika waktu terbatas
```

---

## 11. Arsitektur Komunikasi

```
Browser
  |
  v
Next.js Frontend (:3000)
  |
  | HTTP Request (Authorization: Bearer {token})
  v
Laravel API Backend (:8000)
  |                         |
  | Query/Write             | Dispatch Job
  v                         v
PostgreSQL              Redis Queue/Cache
                            |
                            | Consume Job
                            v
                      Laravel Worker
                            |
                            | Create Notification / Audit Log
                            v
                       PostgreSQL
```

---

## 12. Event-driven Flow (Stock-out)

```
User klik Stock Out
  -> Frontend kirim POST /api/products/{id}/stock-out {quantity, version}
  -> Backend validasi request (Form Request)
  -> Backend mulai DB::transaction()
  -> Backend UPDATE products WHERE id=? AND version=? AND stock>=quantity
  -> Jika affected=0: return 409 Conflict
  -> Jika berhasil: INSERT stock_transactions
  -> Backend commit transaction
  -> Backend dispatch StockUpdatedJob ke Redis queue
  -> Backend return 200 success ke frontend
  -> Worker ambil StockUpdatedJob dari Redis
  -> Worker cek apakah stock <= threshold
  -> Jika iya: Worker buat notification di DB
  -> Frontend polling atau refresh untuk tampilkan notifikasi
```

**Jobs yang dibuat:**
- `StockUpdatedJob` — audit log + cek threshold
- `LowStockNotificationJob` — buat notifikasi stok rendah
- `GenerateDailyReportJob` — opsional, prioritas terakhir

---

## 13. Race Condition Handling

**Strategi: Optimistic Locking**

```php
// StockUpdateService.php
public function stockOut(int $productId, int $quantity, int $version, int $userId): array
{
    return DB::transaction(function () use ($productId, $quantity, $version, $userId) {
        $product = DB::table('products')
            ->where('id', $productId)
            ->first();

        $stockBefore = $product->stock;

        $affected = DB::table('products')
            ->where('id', $productId)
            ->where('version', $version)
            ->where('stock', '>=', $quantity)
            ->update([
                'stock'      => DB::raw('stock - ' . (int) $quantity),
                'version'    => DB::raw('version + 1'),
                'updated_at' => now(),
            ]);

        if ($affected === 0) {
            Log::warning('Stock conflict detected', [
                'product_id'        => $productId,
                'requested_version' => $version,
                'quantity'          => $quantity,
                'user_id'           => $userId,
            ]);
            return ['success' => false, 'code' => 'STOCK_CONFLICT'];
        }

        DB::table('stock_transactions')->insert([
            'product_id'   => $productId,
            'user_id'      => $userId,
            'type'         => 'out',
            'quantity'     => $quantity,
            'stock_before' => $stockBefore,
            'stock_after'  => $stockBefore - $quantity,
            'created_at'   => now(),
        ]);

        StockUpdatedJob::dispatch($productId, $quantity, 'out');

        return ['success' => true];
    });
}
```

**Skenario demo untuk juri:**
1. Produk A: stok 1, version 5
2. Browser 1 dan Browser 2 sama-sama buka detail produk A
3. Browser 1 stock-out 1 → berhasil → stok 0, version 6
4. Browser 2 stock-out 1 dengan version 5 → WHERE version=5 tidak cocok → 409 Conflict
5. UI Browser 2 tampilkan pesan: "Data telah berubah, silakan refresh"

**Jawaban untuk juri:**
> Saya pakai optimistic locking karena konflik bisa terjadi tapi tidak selalu terjadi. Lebih ringan dari pessimistic locking untuk aplikasi inventori sederhana. Kolom version memungkinkan sistem mendeteksi stale update dan mencegah double deduction stok tanpa memblok row di database.

---

## 14. Caching Strategy

**Data yang boleh di-cache:**
- Product catalog → `Cache::remember('products:all', 300, ...)`
- Category list → `Cache::remember('categories:all', 600, ...)`
- Dashboard summary → `Cache::remember('dashboard:summary', 60, ...)`

**Data yang TIDAK boleh di-cache:**
- Stock quantity untuk validasi transaksi → harus selalu baca dari DB
- Version number → harus selalu akurat untuk optimistic locking

**Invalidation:**
- `Cache::forget('products:all')` → saat product created/updated/deleted
- `Cache::forget('categories:all')` → saat category berubah
- `Cache::forget('dashboard:summary')` → saat stock transaction dibuat

**Jawaban untuk juri:**
> Saya cache product catalog karena endpoint ini read-heavy dan tidak harus real-time per milidetik. Tetapi saya tidak cache stock quantity saat transaksi karena data tersebut harus selalu akurat untuk mencegah overselling atau double deduction.

---

## 15. Clean Code Strategy

### StockUpdateService.php — file kebanggaan #1
- Single Responsibility: hanya urusan operasi stok
- Dependency Injection
- Mudah dites secara isolated
- Controller tidak tahu detail implementasi

### ProductRepository.php — file kebanggaan #2
- DRY: semua query produk di satu tempat
- Memudahkan caching
- Tidak ada query duplikat di berbagai controller

### Aturan Controller
Controller hanya boleh:
1. Menerima request
2. Memanggil Form Request validation
3. Memanggil service/repository
4. Return response

Controller tidak boleh mengandung business logic atau query langsung.

---

## 16. Frontend API Client Setup

Buat file `frontend/src/lib/api.ts`:

```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  headers: { 'Content-Type': 'application/json' },
});

// Attach token ke setiap request
api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('token');
    if (token) config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 global — redirect ke login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

File `.env.local` di folder `frontend/`:
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

---

## 17. Structured Logging

```php
// Stock berhasil
Log::info('stock.updated', [
    'product_id'   => $productId,
    'type'         => 'out',
    'quantity'     => $quantity,
    'stock_before' => $stockBefore,
    'stock_after'  => $stockAfter,
    'user_id'      => auth()->id(),
    'ip'           => request()->ip(),
]);

// Conflict
Log::warning('stock.conflict', [
    'product_id'        => $productId,
    'requested_version' => $version,
    'quantity'          => $quantity,
    'user_id'           => auth()->id(),
]);

// Job processed
Log::info('job.processed', [
    'job'        => 'StockUpdatedJob',
    'product_id' => $this->productId,
]);
```

---

## 18. AI Usage Log

File: `docs/AI_USAGE_LOG.md`

Format setiap entry:
```markdown
## Entry [N]
Tool: [nama tool]
Pattern: [generation / debugging / autocomplete / refactoring]

Prompt:
"[prompt utama yang dikirim]"

Output Summary:
[ringkasan apa yang AI hasilkan]

Decision: ACCEPTED / MODIFIED / REJECTED

Reason:
[alasan konkret kenapa diterima/dimodifikasi/ditolak]
```

**Yang penting dicatat:** Interaksi yang paling impactful dan contoh di mana kamu menolak atau memodifikasi output AI.

---

## 19. Commit Strategy

Commit kecil dan konsisten. Contoh urutan natural:

```
feat: setup initial boilerplate
feat: add categories and products migration
feat: add product model with relationships
feat: add category seeder and product seeder
feat: implement product CRUD API with cache
feat: add stock-in and stock-out endpoints
test: add failing test for stock-out conflict
feat: implement optimistic locking for stock update
feat: dispatch StockUpdatedJob after stock mutation
feat: add low stock notification worker
feat: add frontend login page
feat: add frontend product list page
feat: add frontend stock-out with conflict handling
feat: add Redis cache for product catalog
feat: add Swagger API documentation
docs: add AI usage log
chore: add GitHub Actions CI workflow
chore: final cleanup before submission
```

---

## 20. Testing Strategy

Jika waktu cukup, buat feature test untuk:
1. Product CRUD
2. Stock-out berhasil
3. Stock-out gagal jika stok tidak cukup
4. Stock-out return 409 jika version sudah berubah ← prioritas karena Must Have
5. Notification dibuat saat stok <= threshold

Commit pattern TDD yang dinilai baik:
```
test: add failing test for stock-out conflict scenario
feat: implement optimistic locking to pass conflict test
```

---

## 21. Prioritas Pengerjaan

> Disusun ulang sesuai kriteria resmi: TDD dan API docs sekarang Must Have.

### Prioritas 1 — Wajib (Must Have, miss = gugur)
1. Docker tetap berjalan (sudah ada dari boilerplate)
2. Migration + seeder
3. Auth API
4. Product & Category CRUD dengan clean code (service + repository)
5. **TDD: tulis test SEBELUM implementasi tiap fitur inti, commit terpisah**
6. **API documentation dengan Swagger/OpenAPI (l5-swagger)**
7. AI Usage Log (diisi terus selama Fase 2)
8. README lengkap
9. Frontend minimal yang fungsional

### Prioritas 2 — Nilai Tambah (Nice to Have, target min. 50%)
1. Event-driven: queue job dispatch ke Redis + worker notifikasi
2. Race condition: optimistic locking pada stock-out
3. Redis cache product list dan dashboard
4. Structured logging
5. CI/CD GitHub Actions

### Prioritas 3 — Bonus (Extraordinary)
1. Multi-service (backend + worker + redis) — sudah ada dari arsitektur
2. Concurrent edit demo (2 browser)
3. AI-driven analytics (integrasi AI/LLM — juga syarat topik resmi)
4. End-to-end flow smooth dengan latency terukur

### Catatan Urutan Eksekusi
TDD itu Must Have, jadi alur untuk setiap fitur inti adalah:
1. Tulis test yang gagal → commit (`test: ...`)
2. Tulis implementasi sampai test pass → commit (`feat: ...`)

Ini bukan opsional lagi. Commit history harus membuktikan test ditulis duluan.

---

## 22. Hal yang Jangan Dilakukan

- Overengineering terlalu banyak service
- Membuat AI analytics sebelum Must Have selesai
- Cache stock quantity untuk validasi transaksi stok
- Menaruh business logic di controller
- Membuat commit besar di akhir
- Mengabaikan README
- Mengabaikan AI Usage Log
- Submit kode yang tidak bisa dijelaskan
- Copy-paste dari repo/blog publik tanpa atribusi di README

---

## 23. Jawaban Siap untuk Live Defense

### Docker
> Project terdiri dari 5 container: frontend Next.js, backend Laravel dengan Nginx dan PHP-FPM dikelola Supervisord, worker Laravel Queue, PostgreSQL, dan Redis. Semua dijalankan dengan docker compose up. Ordering Dockerfile didesain agar layer yang jarang berubah di-cache duluan — composer install dilakukan sebelum copy source code sehingga dependency tidak di-install ulang setiap ada perubahan kode.

### Event-driven Architecture
> Setelah stock update berhasil, backend tidak langsung memproses semua hal secara sinkron. Backend dispatch StockUpdatedJob ke Redis queue. Worker mengambil job tersebut secara async dan memproses audit log serta pengecekan threshold. Kalau stok di bawah threshold, worker dispatch lagi LowStockNotificationJob. Dengan cara ini request user tetap cepat dan proses background dipisahkan.

### Race Condition
> Saya menggunakan optimistic locking dengan kolom version di tabel products. Saat frontend mengambil data produk, dia juga mendapat nilai version. Saat stock-out, request membawa version tersebut. Backend hanya update jika version masih sama. Jika data sudah diubah user lain, WHERE version tidak match, affected=0, sistem return 409 Conflict. Frontend menampilkan pesan untuk refresh. Ini mencegah double deduction stok.

### Caching
> Saya cache product catalog dengan TTL 5 menit karena endpoint ini read-heavy dan tidak harus real-time. Cache di-invalidate setiap kali ada perubahan produk. Tapi saya tidak cache stock quantity untuk proses transaksi karena data stok harus selalu akurat saat concurrent update terjadi.

### Clean Code
> Saya memisahkan business logic ke StockUpdateService dan query ke ProductRepository. Controller hanya menerima request, memanggil service, dan mengembalikan response. Dengan pemisahan ini kode lebih mudah dites, lebih mudah dibaca, dan setiap bagian punya satu tanggung jawab yang jelas.

### AI Usage
> Saya menggunakan AI sebagai coding assistant, bukan pengganti pemahaman. Setiap interaksi penting saya catat di AI Usage Log. Ada output yang saya terima langsung, ada yang saya modifikasi karena tidak sesuai arsitektur, dan ada yang saya tolak karena salah secara teknis. Saya bisa menjelaskan setiap baris kode yang ada di project ini.

---

## 24. README Minimal

README harus memuat:
1. Nama project dan deskripsi singkat
2. Tech stack
3. Architecture diagram (text-based cukup)
4. Cara menjalankan: `docker compose up -d` + `php artisan migrate`
5. URL lokal (frontend :3000, backend :8000, health check)
6. Demo account (email + password)
7. Penjelasan event-driven flow
8. Penjelasan race condition handling
9. Penjelasan caching strategy
10. Link AI Usage Log
11. Limitasi yang disadari
12. Atribusi open-source boilerplate yang dipakai

---

## 25. Technical Report (PDF 2-3 hal)

Isi yang disarankan:
1. **Problem & Solution** — masalah inventori, solusi StockFlow
2. **Architecture** — diagram + penjelasan 5 container
3. **Key Technical Decisions** — optimistic locking, Redis queue, caching strategy, service layer
4. **AI Usage Log Summary** — tool, pattern, contoh prompt impactful, keputusan accept/reject
5. **Limitations** — role management belum kompleks, monitoring sederhana, dsb

---

## 26. Fase Pengembangan & Validasi

> Ikuti urutan ini. Setiap fase harus divalidasi sebelum lanjut.

> **Catatan estimasi effort:** Fase 2 punya window 22,5 jam kalender, tetapi peserta tidak harus dan tidak mungkin bekerja nonstop selama itu. Estimasi durasi di bawah adalah effort per work package, bukan timeline kumulatif yang harus dijumlahkan menjadi 22,5 jam. Sisakan buffer untuk istirahat, debugging, review, deployment, dan kemungkinan error environment.

| Estimasi Effort | Work Package | Prioritas |
|---|---|---|
| 1-2 jam | Verifikasi Docker, migration, models, seeder baseline | Must Have foundation |
| 1-2 jam | Setup TDD + test contract Product/Category API | Must Have |
| 2-3 jam | Implement Product & Category CRUD + service/repository | Must Have |
| 1-2 jam | Swagger/OpenAPI + kontrak endpoint AI insight | Must Have |
| 2-3 jam | Stock-in/out + optimistic locking + stock transaction | Nice to Have |
| 1-2 jam | Redis queue/worker untuk low-stock notification | Nice to Have |
| 2-3 jam | Frontend minimal: login, dashboard, products, stock-out | End-to-end demo |
| 1-2 jam | Basic AI insight endpoint + cache | AI integration |
| 1-2 jam | Redis caching, structured logging, README, AI Usage Log update | Nice to Have + docs |
| 1-2 jam | CI/CD sederhana, concurrent demo check, deploy, tag final | Final buffer |


---

### FASE 0 — Verifikasi Boilerplate

**Target:** Pastikan semua container jalan dan siap digunakan.

```powershell
docker compose up -d
docker compose ps
curl http://localhost:8000/api/health
```

**✅ Validasi:**
```
- Semua 5 container status = running
- Health check return {"status":"healthy","services":{"app":"ok","database":"ok","redis":"ok"}}
- http://localhost:3000 terbuka di browser
```

**Git setup:**
```powershell
cd D:\Exercise\inaai-boilerplate
git init
git add .
git commit -m "feat: initial boilerplate setup"
```

---

### FASE 1 — Database & Models

**Target:** Semua tabel terbuat, model terdefinisi, data dummy tersedia.

```powershell
# Buat migration
docker exec inaai_backend php artisan make:migration add_role_to_users_table
docker exec inaai_backend php artisan make:migration create_categories_table
docker exec inaai_backend php artisan make:migration create_products_table
docker exec inaai_backend php artisan make:migration create_stock_transactions_table
docker exec inaai_backend php artisan make:migration create_notifications_table

# Jalankan migration
docker exec inaai_backend php artisan migrate

# Buat model
docker exec inaai_backend php artisan make:model Category
docker exec inaai_backend php artisan make:model Product
docker exec inaai_backend php artisan make:model StockTransaction
docker exec inaai_backend php artisan make:model Notification

# Buat seeder
docker exec inaai_backend php artisan make:seeder UserSeeder
docker exec inaai_backend php artisan make:seeder CategorySeeder
docker exec inaai_backend php artisan make:seeder ProductSeeder
docker exec inaai_backend php artisan db:seed
```

**✅ Validasi:**
```powershell
# Cek semua tabel ada
docker exec inaai_postgres psql -U inaai_user -d inaai_db -c "\dt"
# Expected: users, categories, products, stock_transactions, notifications, jobs, cache, migrations

# Cek data seeder
docker exec inaai_backend php artisan tinker --execute="echo \App\Models\Product::count().' products';"
# Expected: angka > 0

docker exec inaai_backend php artisan tinker --execute="echo \App\Models\User::first()->email;"
# Expected: email admin dari seeder
```

**Commit:** `feat: add database migrations, models, and seeders`

---

### FASE 2 — Authentication API

**Target:** Login, logout, get current user berfungsi dengan Sanctum.

```powershell
docker exec inaai_backend composer require laravel/sanctum
docker exec inaai_backend php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker exec inaai_backend php artisan migrate
docker exec inaai_backend php artisan make:controller AuthController
```

**✅ Validasi:**
```powershell
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@stockflow.com\",\"password\":\"password\"}"
# Expected: {"token":"...","user":{...}}

# Simpan token lalu test /me
curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer [TOKEN_DARI_ATAS]"
# Expected: data user yang login

# Test logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer [TOKEN]"
# Expected: {"message":"Logged out"}
```

**Commit:** `feat: implement authentication API with Sanctum`

---

### FASE 3 — Product & Category CRUD API (dengan TDD)

**Target:** CRUD produk dan kategori berfungsi dengan caching Redis.

> **TDD WAJIB (Must Have):** Untuk setiap endpoint inti, tulis feature test SEBELUM
> implementasi. Commit test dulu (`test: ...`), baru commit implementasi (`feat: ...`).
> Commit history harus membuktikan urutan ini. Mulai biasakan dari fase ini.

```powershell
# Buat test SEBELUM controller
docker exec inaai_backend php artisan make:test ProductApiTest --feature
# Tulis test: GET products, POST product, validasi. Commit: "test: add product API tests"
# BARU buat controller untuk membuat test pass. Commit: "feat: implement product CRUD"
```

```powershell
docker exec inaai_backend php artisan make:controller ProductController --resource
docker exec inaai_backend php artisan make:controller CategoryController --resource
docker exec inaai_backend php artisan make:request StoreProductRequest
docker exec inaai_backend php artisan make:request UpdateProductRequest
```

**✅ Validasi:**
```powershell
# Get products (pertama kali — cache miss)
curl http://localhost:8000/api/products \
  -H "Authorization: Bearer [TOKEN]"
# Expected: array of products

# Get products lagi (cache hit — lebih cepat)
curl http://localhost:8000/api/products \
  -H "Authorization: Bearer [TOKEN]"

# Cek Redis cache ada
docker exec inaai_redis redis-cli keys "*"
# Expected: ada key "laravel-cache-products:all"

# Create product
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer [TOKEN]" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Test\",\"sku\":\"TST-001\",\"price\":10000,\"category_id\":1}"
# Expected: {"id":...,"name":"Test",...}

# Cek cache invalidated setelah create
docker exec inaai_redis redis-cli keys "*"
# Expected: key products:all sudah tidak ada
```

**Commit:** `feat: implement product and category CRUD API with Redis cache`

---

### FASE 4 — Stock Operations & Race Condition

**Target:** Stock-in/out berfungsi dengan optimistic locking. Race condition scenario bisa didemo.

```powershell
docker exec inaai_backend php artisan make:controller StockController
# Buat StockUpdateService manual di app/Services/StockUpdateService.php
# Buat StockUpdatedJob
docker exec inaai_backend php artisan make:job StockUpdatedJob
docker exec inaai_backend php artisan make:job LowStockNotificationJob
```

**✅ Validasi — Normal flow:**
```powershell
# Ambil product dan catat version
curl http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer [TOKEN]"
# Catat nilai "version"

# Stock out
curl -X POST http://localhost:8000/api/products/1/stock-out \
  -H "Authorization: Bearer [TOKEN]" \
  -H "Content-Type: application/json" \
  -d "{\"quantity\":2,\"version\":[VERSION]}"
# Expected: {"message":"Stock updated successfully"}

# Cek stok berkurang dan version bertambah
curl http://localhost:8000/api/products/1 -H "Authorization: Bearer [TOKEN]"
# Expected: stock berkurang 2, version bertambah 1
```

**✅ Validasi — Race condition (paling penting!):**
```powershell
# Kirim 2 request dengan version yang sama hampir bersamaan
# Di terminal 1:
curl -X POST http://localhost:8000/api/products/1/stock-out \
  -H "Authorization: Bearer [TOKEN]" \
  -H "Content-Type: application/json" \
  -d "{\"quantity\":1,\"version\":[VERSION_SAMA]}"

# Di terminal 2 (jalankan hampir bersamaan):
curl -X POST http://localhost:8000/api/products/1/stock-out \
  -H "Authorization: Bearer [TOKEN]" \
  -H "Content-Type: application/json" \
  -d "{\"quantity\":1,\"version\":[VERSION_SAMA]}"

# Expected: salah satu 200 OK, satunya 409 Conflict
# {"message":"Stock conflict. Please refresh and try again.","code":"STOCK_CONFLICT"}
```

**✅ Validasi — Queue job:**
```powershell
# Cek worker memproses job
docker compose logs worker --tail=20
# Expected: ada log job diproses

# Cek stock_transactions terisi
docker exec inaai_backend php artisan tinker \
  --execute="echo \App\Models\StockTransaction::count().' transactions';"
# Expected: angka > 0
```

**Commit:**
```
test: add failing test for stock-out conflict scenario
feat: implement optimistic locking in StockUpdateService
feat: dispatch StockUpdatedJob after successful stock mutation
feat: add LowStockNotificationJob worker
```

---

### FASE 5 — Notification & Dashboard API

**Target:** Notifikasi stok rendah terbuat otomatis oleh worker. Dashboard summary berfungsi.

```powershell
docker exec inaai_backend php artisan make:controller NotificationController
docker exec inaai_backend php artisan make:controller DashboardController
```

**✅ Validasi:**
```powershell
# Set stok produk di bawah threshold untuk trigger notifikasi
docker exec inaai_backend php artisan tinker \
  --execute="\App\Models\Product::find(1)->update(['stock'=>2,'threshold'=>5,'version'=>0]);"

# Lakukan stock-out untuk trigger job
curl -X POST http://localhost:8000/api/products/1/stock-out \
  -H "Authorization: Bearer [TOKEN]" \
  -H "Content-Type: application/json" \
  -d "{\"quantity\":1,\"version\":0}"

# Tunggu 3-5 detik, cek notifikasi
curl http://localhost:8000/api/notifications \
  -H "Authorization: Bearer [TOKEN]"
# Expected: ada notifikasi low stock

# Cek dashboard
curl http://localhost:8000/api/dashboard/summary \
  -H "Authorization: Bearer [TOKEN]"
# Expected: {"total_products":...,"low_stock_count":...,"transactions_today":...}
```

**Commit:** `feat: add notification system and dashboard summary API`

---

### FASE 5B — API Documentation (Must Have, WAJIB)

**Target:** Swagger/OpenAPI documentation accessible. Ini Must Have — tidak boleh dilewat.

```powershell
docker exec inaai_backend composer require darkaonline/l5-swagger
docker exec inaai_backend php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
# Tambah anotasi @OA\ pada controller. Minimal cover: auth, products, stock-out, dashboard
docker exec inaai_backend php artisan l5-swagger:generate
```

**✅ Validasi:**
```
Buka http://localhost:8000/api/documentation
✓ Swagger UI muncul
✓ Endpoint utama terdokumentasi dengan request/response schema
✓ Auth requirement (Bearer token) terlihat di endpoint yang butuh auth
```

Siapkan jawaban probe: "Pilih satu endpoint, jelaskan request/response schema dan auth-nya."

**Commit:** `docs: add Swagger API documentation`

---

### FASE 6 — Frontend

**Target:** Semua halaman utama berfungsi, termasuk conflict handling di UI.

Setup axios dulu:
```powershell
docker exec inaai_frontend npm install axios
```

Buat `frontend/src/lib/api.ts` (lihat section 16 dokumen ini).

Buat file `frontend/.env.local`:
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

**Urutan pengerjaan halaman:**
1. `/login` — form login, simpan token ke localStorage
2. Layout dengan sidebar dan proteksi route
3. `/dashboard` — summary cards + low stock alert
4. `/products` — list + search
5. `/products/[id]` — detail + transaction history
6. `/products/new` — form tambah produk
7. `/stock-in` — form stock in
8. `/stock-out` — form stock out + tampilkan error 409 ke user
9. `/notifications` — list notifikasi

**✅ Validasi:**
```
Buka http://localhost:3000
✓ Redirect ke /login jika belum login
✓ Login berhasil redirect ke /dashboard
✓ Dashboard menampilkan summary cards
✓ /products menampilkan list produk
✓ Stock out berhasil mengurangi stok
✓ Stock out dengan version lama menampilkan pesan conflict di UI
✓ Setelah stock out di bawah threshold, notifikasi muncul di /notifications
✓ Logout bekerja dan redirect ke /login
```

**Commit:** `feat: add frontend pages with stock-out conflict handling`

---

### FASE 7 — Nice to Have

Kerjakan sesuai sisa waktu, urut dari yang tercepat:

**7.1 Caching sudah ada dari Fase 3 — tinggal verifikasi**

**7.2 Structured Logging — ~30 menit**
Tambah `Log::info/warning` di setiap operasi stok. Verifikasi:
```powershell
docker compose logs backend --tail=50
# Expected: log terformat saat ada operasi stok
```

**7.3 API Documentation Polish — ~30 menit**
API documentation sudah Must Have di FASE 5B. Di fase ini hanya polish: lengkapi contoh response, auth requirement, dan endpoint AI insight jika belum rapi.

**7.4 Feature Test — ~60 menit**
Minimal 1 test kuat untuk race condition:
```powershell
docker exec inaai_backend php artisan test
# Expected: semua test pass
```

**7.5 CI/CD GitHub Actions — ~30 menit**
Buat `.github/workflows/ci.yml` minimal: install deps + run tests.
Verifikasi: push ke GitHub, cek tab Actions hijau.

---

### FASE 7B — AI-Driven Analytics (integrasi AI/LLM)

**Target:** Endpoint analytics yang memanggil LLM untuk insight. Memenuhi syarat topik resmi (integrasi AI/LLM) sekaligus Extraordinary #3. Jangan menaruh seluruh fitur ini di akhir; minimal kontrak endpoint dan service skeleton dibuat setelah Swagger, lalu hasil akhirnya dipoles setelah Must Have aman.

**Prasyarat:** API key LLM (OpenAI/Gemini/Claude) sudah disiapkan.

```powershell
# Buat service dan controller
docker exec inaai_backend php artisan make:controller AnalyticsController
# Buat AnalyticsService di app/Services/ untuk agregasi + panggil LLM
```

Langkah:
1. `AnalyticsService` agregasi data transaksi (jangan kirim raw mentah ke LLM)
2. Susun prompt terstruktur yang minta pola dan anomali
3. Panggil LLM API, parse response jadi insight
4. Cache hasil di Redis TTL 1 jam
5. Endpoint `GET /api/analytics/insights` return insight

**✅ Validasi:**
```powershell
curl http://localhost:8000/api/analytics/insights \
  -H "Authorization: Bearer [TOKEN]"
# Expected: JSON berisi insight naratif dari LLM berdasarkan data inventori
```

Siapkan demo: tunjukkan insight yang tidak obvious dari raw data untuk probe Extraordinary #3.

**Commit:** `feat: add AI-driven inventory analytics with LLM integration`

---

### FASE 8 — Final Checks

**8.1 Clean State Test (wajib simulasi)**
```powershell
docker compose down -v
docker compose up --build
# Harus jalan tanpa error dalam < 5 menit
docker exec inaai_backend php artisan migrate --force
curl http://localhost:8000/api/health
# Expected: {"status":"healthy",...}
```

**8.2 Concurrent Demo Test**
Buka 2 tab browser, login sebagai 2 user berbeda, lakukan stock-out produk sama bersamaan. Screenshot atau rekam untuk referensi presentasi.

**8.3 Checklist Final**
```
Must Have (semua wajib, miss = gugur):
☐ docker compose up dari clean state jalan
☐ Bisa tunjukkan 2 file clean code (StockUpdateService + ProductRepository)
☐ TDD: commit history bukti test ditulis sebelum implementasi
☐ Swagger API documentation accessible di /api/documentation
☐ AI Usage Log lengkap di docs/AI_USAGE_LOG.md
☐ README lengkap dengan cara run

Nice to Have (target min. 50%):
☐ Event-driven flow via Redis queue + worker jalan
☐ Race condition: demo 2 browser conflict scenario
☐ Redis cache aktif, bisa tunjukkan latency difference
☐ Structured logging aktif
☐ CI/CD pipeline hijau di GitHub Actions

Extraordinary:
☐ 3 service terpisah (backend, worker, redis) — sudah ada
☐ Demo concurrent edit siap
☐ AI-driven analytics jalan dan bisa didemo
☐ End-to-end flow bisa didemokan dengan lancar

Skenario Wajib:
☐ Analisis Skenario 1 siap dipertahankan saat Live Defense
```

**8.4 Deploy & Submission**
```powershell
git add .
git commit -m "chore: final cleanup before submission"
git tag submission-final
git push origin main --tags
```

Deploy backend ke Railway, frontend ke Vercel. Pastikan URL accessible sebelum submit Google Form.

---

## 27. Instruksi untuk AI Assistant

Jika dokumen ini diberikan ke AI lain, ikuti aturan berikut:

1. Jangan mengubah arah project tanpa alasan kuat
2. Prioritaskan Must Have kompetisi
3. Berikan langkah bertahap, untuk setiap kode sebutkan: file yang dibuat/diubah, isi kode, command yang dijalankan, cara mengetes, alasan teknis
4. Jangan sarankan AI analytics sebelum Must Have selesai
5. Jangan buat arsitektur terlalu kompleks
6. Pastikan semua solusi bisa dijelaskan saat live defense
7. Utamakan Laravel best practice dan Next.js yang sederhana
8. Pastikan race condition handling benar-benar bisa didemokan
9. Pastikan AI Usage Log terus diperbarui
10. Jangan ubah konfigurasi Docker yang sudah berjalan kecuali ada alasan kuat

**Hal yang sudah TIDAK perlu dikerjakan (sudah ada):**
- Docker setup dan konfigurasi
- Health check endpoint
- Konfigurasi Redis sebagai queue dan cache driver di .env
- Konfigurasi api.php di bootstrap/app.php
- next.config.ts dengan output standalone

**Hal yang perlu dikerjakan mulai dari FASE 1.**

---

## 28. Fokus Berikutnya (Entry Point)

Langkah konkret pertama saat mulai pengembangan, berurutan:

1. Verifikasi boilerplate (FASE 0) — pastikan Docker jalan dan health check OK
2. Buat migration dan model: categories, products, stock_transactions, notifications, role di users
3. Buat seeder dengan data dummy + akun admin
4. Buat Product & Category CRUD API dengan ProductRepository
5. Buat StockUpdateService dengan optimistic locking
6. Buat queue job: StockUpdatedJob, LowStockNotificationJob
7. Buat frontend: login, product list, stock-out, conflict handling, AI insight panel
8. Tambahkan README, AI Usage Log, API documentation
9. Nice to Have sesuai sisa waktu
10. Final check + deploy + tag submission-final

Jangan kerjakan semua sekaligus. Selesaikan satu fase, validasi, commit, baru lanjut.

---

## 29. Checklist Ringkas Kompetisi

Checklist cepat untuk dibuka kapan saja. Detail validasi ada di section 26.

### Must Have (semua wajib, miss = gugur)
- [ ] Docker Compose jalan dari clean state
- [ ] Ada 2 file clean code siap dijelaskan (StockUpdateService + ProductRepository)
- [ ] TDD — test ditulis sebelum implementasi, terbukti dari commit history
- [ ] API documentation Swagger/OpenAPI accessible
- [ ] AI Usage Log lengkap
- [ ] README menjelaskan cara run

### Nice to Have (target min. 50%)
- [ ] Event-driven flow via Redis queue + worker
- [ ] Race condition handling dengan optimistic locking + demo
- [ ] CI/CD pipeline
- [ ] Redis caching dan invalidation
- [ ] Structured logging

### Extraordinary
- [ ] 3+ service/container terpisah (sudah ada dari arsitektur)
- [ ] Demo 2 browser concurrent edit
- [ ] AI-driven analytics (integrasi AI/LLM — juga syarat topik resmi)
- [ ] End-to-end flow smooth dengan latency terukur

### Skenario Wajib
- [ ] Analisis Skenario 1 (zero-latency tanpa async) siap di proposal & Live Defense

---

## 29B. Skenario Pilihan Wajib — untuk Proposal & Live Defense

Dokumen resmi mewajibkan memilih SATU dari 3 skenario "mustahil" untuk dianalisis di
proposal dan dipertahankan saat Live Defense. **Skenario yang dipilih: Skenario 1.**

### Skenario 1 — Zero-Latency Tanpa Async

> "Aplikasi harus 100% real-time untuk semua user, tidak boleh ada latency sama sekali,
> tapi tetap hemat biaya dan tidak boleh pakai websocket, queue, atau background worker
> karena arsitekturnya harus sederhana."

**Kenapa dipilih:** StockFlow justru memakai queue (Redis) dan worker. Jadi analisis ini
berakar pada arsitektur nyata yang dibangun, bukan teori abstrak. Saat Live Defense bisa
tunjuk kode sendiri sebagai bukti.

### Poin Analisis untuk Proposal

**1. Zero-latency tidak bisa dijanjikan untuk web production.**
Setiap request melewati jaringan fisik. Latency minimal dibatasi oleh kecepatan cahaya di
fiber, round-trip TCP, dan pemrosesan server. "Nol latency" secara teknis mustahil; yang
realistis adalah latency rendah dan predictable yang diukur per jenis operasi. Jangan mengklaim p95 tunggal untuk semua endpoint, terutama pada free tier dan endpoint AI.

**2. Konsekuensi memaksa real-time tanpa async.**
Tanpa queue/worker, semua proses berat (notifikasi, agregasi, kalkulasi) harus sinkron di
request-response cycle. Akibatnya: request lambat, blocking, server mudah overload saat
concurrent tinggi, dan user menunggu proses yang sebenarnya bisa di-background.

**3. Trade-off yang sebenarnya terjadi.**
"Real-time" dan "tanpa async" itu kontradiktif untuk operasi non-trivial. Semakin real-time
ingin terlihat tanpa async, semakin berat beban sinkron — yang justru menambah latency.
Larangan async tidak menyederhanakan arsitektur, tapi memindahkan kompleksitas ke tempat
yang lebih buruk.

**4. Arsitektur yang seharusnya dinegosiasikan.**
- Pisahkan operasi yang benar-benar butuh latensi rendah (read) dari yang bisa async (write side-effect)
- Untuk read real-time: caching + indexing, bukan menghapus async
- Untuk update near-real-time: polling ringan atau SSE jika websocket dilarang
- Definisikan SLA latency yang realistis dan terukur, bukan "nol"
- Bedakan warm runtime, cold start, cache hit, dan request yang memanggil LLM
- Untuk AI insight, gunakan cache dan jangan letakkan di critical path transaksi stok

### Hubungan dengan StockFlow

Di StockFlow, stock-out memberi response cepat ke user karena critical mutation tetap sinkron dan kecil, sementara side-effect berat (cek threshold, buat notifikasi, audit, precompute insight) dilempar ke queue atau di-cache. Implementasi ini bukan menerima constraint mustahil secara literal, melainkan hasil negosiasi requirement: zero-latency diganti menjadi bounded latency, dan larangan async diganti menjadi pemisahan critical path dari background side-effect.

**Jawaban inti untuk juri:**
> "Requirement ini kontradiktif. Zero-latency mustahil secara fisika jaringan, dan melarang
> async justru memperburuk latency untuk operasi non-trivial karena semua jadi blocking. Di
> StockFlow saya menunjukkan kebalikannya: response cepat ke user dicapai dengan memindahkan
> kerja berat ke worker. Yang saya negosiasikan adalah SLA latency realistis dan pemisahan
> read-path dari write-side-effect, bukan menghapus mekanisme async."

---

## 29C. Catatan Deployment Free Tier dan Latency

Deployment free tier boleh dipakai untuk demo kompetisi, tetapi jangan diposisikan sebagai jaminan production-grade. Strategi aman:

- Frontend Next.js: Vercel free tier.
- Backend Laravel: Railway atau Render.
- Database PostgreSQL: Railway Postgres, Render Postgres, Neon, atau Supabase.
- Redis: Railway Redis, Render Key Value, atau provider Redis yang kompatibel.
- AI/LLM: Gemini atau provider lain yang API key-nya siap dan dicatat di AI Usage Log.

Klaim latency harus spesifik:
- Core API pada warm runtime ditargetkan rendah dan predictable.
- Cached read-path seharusnya lebih cepat daripada query database langsung.
- AI insight tidak dijanjikan real-time; generation pertama bisa beberapa detik, hasil berikutnya diambil dari cache.
- Cold start free tier dikecualikan dari klaim p95 stabil.

---

## 30. Kesimpulan Arah Project

Project ini diposisikan sebagai aplikasi full-stack yang **sederhana secara domain, tetapi kuat secara engineering**.

Yang ingin ditunjukkan ke juri:
- Mampu membangun aplikasi web lengkap end-to-end
- Paham Docker dan service separation
- Paham event-driven architecture
- Paham race condition dan locking strategy
- Mampu memakai AI secara produktif dan bertanggung jawab
- Mampu menjelaskan setiap keputusan teknis, bukan hanya membuat aplikasi berjalan

Fokus utama bukan membuat fitur sebanyak mungkin, tetapi membuat fitur inti yang rapi, bisa dijelaskan, dan sesuai kriteria kompetisi. Saat ragu antara menambah fitur atau memperdalam pemahaman fitur yang ada, pilih memperdalam pemahaman.
