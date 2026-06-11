# AI Usage Log — StockFlow

Catatan penggunaan AI coding tools selama pembangunan StockFlow.
AI dipakai sebagai *assistant*, bukan pengganti pemahaman — setiap baris kode
di project ini bisa dijelaskan. Log ini mencatat interaksi paling impactful,
termasuk output yang **dimodifikasi** atau **ditolak** karena tidak sesuai
arsitektur atau salah secara teknis.

**Tool yang dipakai:**
- **Fase 1 (Planning):** Claude via claude.ai — Sonnet 4.6 (perancangan, analisis, alur)
- **Fase 2 (Coding):** Claude Code — Sonnet 4.6 & Opus 4.8 (implementasi, debugging)

**Pattern:** planning, architecture-design, analysis, generation, debugging, refactoring.

> Catatan: bagian wajib menurut aturan kompetisi adalah history prompt Fase 2
> (coding). Bagian A (Fase 1) disertakan sebagai konteks judgment end-to-end —
> bagaimana arsitektur diputuskan sebelum satu baris kode ditulis.

---

# Bagian A — Fase 1: Planning & Architecture (claude.ai)

## Entry P1
**Tool:** Claude (claude.ai)
**Pattern:** architecture-design

**Prompt:**
> "kenapa tidak menggunakan boilerplate publik? jika ada justifikasi yang masuk
> akal mari kita lanjut buatkan boilerplate nya"

**Output Summary:**
AI menjelaskan bahwa boilerplate publik tetap dipakai untuk komponen masing-masing
(`create-next-app`, `laravel new`), tapi Docker Compose yang menghubungkan
Laravel + Next.js + Redis + PostgreSQL tidak ada boilerplate publik yang langsung
jalan. Lalu generate boilerplate custom dengan 5 service Docker.

**Decision:** ACCEPTED

**Reason:**
Justifikasi masuk akal — kombinasi multi-service memang harus disusun manual.
Diterima karena hemat waktu setup di hari H dan tetap sesuai aturan kompetisi
(boilerplate publik komponen disebut di README).

---

## Entry P2
**Tool:** Claude (claude.ai)
**Pattern:** analysis

**Prompt:**
> "saya bingung kenapa ada mention ai di situ" (saat AI menyebut AI analytics di
> rekomendasi awal arsitektur)

**Output Summary:**
AI salah mengintegrasikan AI ke arsitektur awal padahal track Full-stack Developer
saat itu (sebelum briefing) tidak mewajibkan integrasi AI di aplikasi. AI
mengoreksi diri: AI hanya muncul di dua konteks — coding assistant (Must Have) dan
AI-driven analytics (Extraordinary, opsional).

**Decision:** MODIFIED

**Reason:**
Saya menolak AI di arsitektur awal karena fokus harus di full-stack engineering
yang solid dulu. Setelah dokumen resmi rilis dan mensyaratkan integrasi AI/LLM,
saya menerima penambahan AI analytics sebagai fitur — bukan sebagai fondasi.

---

## Entry P3
**Tool:** Claude (claude.ai)
**Pattern:** planning

**Prompt:**
> "bagaimana dengan konteks yang saya buat ini apakah bisa overwrite punyamu dan
> lebih baik dari yang kamu buat?"

**Output Summary:**
AI menilai jujur bahwa konteks buatan user lebih baik di banyak aspek (32 section
vs 10, ada instruksi eksplisit untuk AI assistant, jawaban siap untuk Live Defense).
AI mengakui versinya kurang lengkap dan merekomendasikan file user jadi dokumen utama.

**Decision:** ACCEPTED dengan MODIFIED

**Reason:**
Saya menerima penilaian jujur ini dan tetap menggabungkan kekuatan kedua versi —
konteks user sebagai utama, ditambah validasi command per fase dari versi Claude.

---

## Entry P4
**Tool:** Claude (claude.ai)
**Pattern:** analysis

**Prompt:**
> "kenapa jumlah line di .md yang kamu berikan justru lebih sedikit dari file
> sebelumnya... pastikan jangan sampai pengurangan ini menggangu alur keseluruhan,
> apakah penulisan versi stack yang spesifik tidak mengganggu?"

**Output Summary:**
AI mengakui tiga kekeliruan di revisi sebelumnya: ada section yang seharusnya tidak
dihapus, versi stack ditulis spesifik (Next.js 14, Redis 7) tidak sesuai yang
sebenarnya terinstall (Next.js 16, Redis 7.4), dan tidak ada deklarasi bahwa dokumen
harus dibaca berdampingan dengan PDF kompetisi. Lalu memperbaiki ketiganya.

**Decision:** ACCEPTED

**Reason:**
Saya menerima koreksi karena tiga poin yang saya angkat valid dan berdampak nyata —
versi stack yang salah bisa membuat AI lain menulis kode untuk versi yang keliru.

---

# Bagian B — Fase 2: Coding & Implementation (Claude Code)

## Entry 1
**Tool:** Claude Code
**Pattern:** generation

**Prompt:**
> "Buat StockUpdateService dengan optimistic locking untuk stock-out. Pakai kolom
> version, return 409 jika conflict, dispatch StockUpdatedJob setelah sukses."

**Output Summary:**
AI menghasilkan service dengan `DB::transaction()`, single UPDATE atomic ber-guard
`WHERE id=? AND version=? AND stock>=?`, cek `affected===0` untuk deteksi konflik,
insert stock_transaction, lalu dispatch job.

**Decision:** ACCEPTED

**Reason:**
Pola single atomic UPDATE dengan guard di WHERE clause adalah inti optimistic
locking yang benar — race ditangani di level database, bukan di PHP (yang akan
TOCTOU). Saya verifikasi: tidak ada `SELECT ... lalu UPDATE` terpisah yang bisa
bocor antar request. Diterima karena saya bisa menjelaskan kenapa `affected===0`
ekuivalen dengan stale version, dan ini yang saya demokan ke juri.

---

## Entry 2 — paling impactful
**Tool:** Claude Code
**Pattern:** debugging

**Prompt:**
> "`php artisan test` menghapus seluruh data PostgreSQL produksi. Sebelum test ada
> 2 user, setelah test 0 user. RefreshDatabase memanggil migrate:fresh ke pgsql,
> bukan sqlite. phpunit.xml sudah set DB_CONNECTION=sqlite force=true tapi tetap kena."

**Output Summary:**
AI awalnya menyarankan `putenv('DB_CONNECTION=sqlite')` di dalam
`TestCase::createApplication()` sebelum `parent::createApplication()`.

**Decision:** REJECTED (saran pertama) → MODIFIED (solusi final)

**Reason:**
Saran `putenv` **ditolak** karena setelah diuji, PostgreSQL tetap ter-wipe.
Akar masalah: docker-compose meng-inject `DB_CONNECTION=pgsql` sebagai *system
env var*, dan phpdotenv `ImmutableAdapter` mengecek `getenv()` yang sudah berisi
nilai pgsql sebelum kode test kita jalan — jadi `force="true"` di phpunit.xml
maupun `putenv()` sama-sama kalah. Solusi yang saya terima setelah memahami ini:
override **config object** SETELAH `parent::createApplication()`, bukan env var:
```php
$app['config']->set('database.default', 'sqlite');
$app['config']->set('database.connections.sqlite.database', ':memory:');
```
Config sudah final saat itu dan tidak dibaca ulang dari env. Hasil: 43/43 test
pass, data PostgreSQL utuh. Ini interaksi paling impactful karena bug-nya
silent-destructive dan solusinya butuh paham urutan bootstrap Laravel, bukan
sekadar menyalin saran pertama AI.

---

## Entry 3
**Tool:** Claude Code
**Pattern:** debugging

**Prompt:**
> "Cache Redis tidak aktif padahal sudah set CACHE_DRIVER=redis di docker-compose.
> Driver masih kebaca default."

**Output Summary:**
AI mengidentifikasi bahwa Laravel 11 mengubah env key dari `CACHE_DRIVER` (Laravel 10)
menjadi `CACHE_STORE`. `config/cache.php` membaca `env('CACHE_STORE')`.

**Decision:** MODIFIED

**Reason:**
Diterima sebagai diagnosis, tapi saya tidak hanya ganti satu baris. Saya verifikasi
ke [config/cache.php](../config/cache.php) bahwa benar key-nya `CACHE_STORE`, lalu
update docker-compose backend & worker. Penting karena dokumentasi/blog lama masih
pakai `CACHE_DRIVER` — kalau ditelan mentah, debugging bisa berjam-jam.

---

## Entry 4
**Tool:** Claude Code
**Pattern:** debugging

**Prompt:**
> "Worker error `__PHP_Incomplete_Class` saat memproses StockUpdatedJob, padahal
> backend bisa dispatch normal."

**Output Summary:**
AI menjelaskan worker dan backend adalah container terpisah dari image yang sama.
File job yang di-`docker cp` hanya ke container backend tidak muncul di image worker.

**Decision:** ACCEPTED

**Reason:**
Diterima setelah saya konfirmasi: `docker compose up -d --build backend worker`
me-rebuild kedua image dari filesystem host, lalu worker punya semua file job.
Pelajaran arsitektural yang saya pegang: `docker cp` itu per-container, bukan
per-image — untuk konsistensi multi-service harus rebuild, bukan cp.

---

## Entry 5
**Tool:** Claude Code
**Pattern:** generation

**Prompt:**
> "Buat AnalyticsService yang panggil LLM untuk insight inventori."

**Output Summary:**
Draft awal AI mengirim seluruh baris transaksi mentah ke LLM dalam prompt.

**Decision:** MODIFIED

**Reason:**
Saya ubah agar service **mengagregasi** dulu (daftar low-stock, top 5 transaksi
keluar 7 hari, jumlah transaksi hari ini) baru dikirim ke LLM. Alasan: mengirim raw
transaksi boros token, lambat, dan membocorkan data lebih dari perlu. Agregasi
membuat prompt deterministik dan insight lebih fokus. Saya juga tambah
`Http::timeout(30)` dan log `analytics.llm_error` agar kegagalan LLM tidak
menggantung request.

---

## Entry 6
**Tool:** Claude Code
**Pattern:** generation

**Prompt:**
> "Tambah AI insight panel di dashboard, fetch GET /analytics/insights."

**Output Summary:**
AI menulis `api.get('/analytics/insights').then((r) => setInsight(r.data))`.
TypeScript build gagal: parameter `r` ber-tipe implicit `any`.

**Decision:** MODIFIED

**Reason:**
Generic axios `api.get<T>()` tidak mengalir ke callback `.then()` pada konfigurasi
TS strict project ini. Saya ganti ke destructuring eksplisit
`({ data }: { data: InsightData }) => setInsight(data)`. Diterima setelah diubah —
output AI fungsional tapi tidak lolos `tsc` strict, jadi tidak bisa ditelan mentah.

---

## Entry 7
**Tool:** Claude Code
**Pattern:** refactoring

**Prompt:**
> "Seeder gagal saat dijalankan ulang: duplicate key violation untuk email/sku."

**Output Summary:**
AI mengganti `User::create()` / `Category::create()` / `Product::create()` menjadi
`firstOrCreate()` dengan unique key (email, name, sku) sebagai pembeda.

**Decision:** ACCEPTED

**Reason:**
Diterima karena membuat seeder idempotent — bisa dijalankan berkali-kali tanpa error,
penting saat demo clean-state berulang. Saya pastikan key pembeda benar-benar unik
(sku untuk produk, email untuk user) agar tidak ada update tak terduga.

---

## Ringkasan Pola Penggunaan

**Fase 1 (Planning):** AI dipakai untuk merancang arsitektur, menganalisis kriteria
resmi, dan menyusun alur — tapi keputusan akhir tetap saya pegang: menolak AI sebagai
fondasi arsitektur (P2), menerima penilaian jujur AI atas konteks saya sendiri (P3),
dan merevisi klaim agar lebih jujur (P5).

**Fase 2 (Coding):**
- **Diterima langsung:** pola yang secara teknis benar dan bisa saya jelaskan
  (optimistic locking, idempotent seeder).
- **Dimodifikasi:** output fungsional tapi tidak optimal/aman (raw data ke LLM,
  TS implicit any, CACHE_DRIVER lama).
- **Ditolak:** saran yang tidak menyelesaikan akar masalah (putenv untuk test
  isolation) — diganti setelah memahami bootstrap order Laravel.

Prinsip: AI mempercepat penulisan, tapi keputusan arsitektur dan verifikasi
teknis tetap manual. Bug paling kritis (test wipe PostgreSQL) justru butuh
menolak saran pertama AI dan menggali lebih dalam.
