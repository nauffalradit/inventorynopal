# AGENTS.md

Aplikasi inventory Laravel 12 (pencatatan barang, mutasi stok, laporan, order + pembayaran DOKU). Keep All — semua fitur dipertahankan. Berjalan ringan via Docker Compose + Neon (online), Postgres lokal hanya alternatif test case. Auth hanya Google OAuth.

## Arsitektur / Docker compose (docker-compose.yml)

Default (Neon, online) — 2 service:
- `pencatatan` — web `php artisan serve --host=0.0.0.0 --port=8000` di `http://localhost:8000` (Traefik optional `profiles: ["prod"]` untuk `http://inventory.localhost`).
- `worker` — opsional, khusus antrean `notifications` (`queue:work --queue=reports,notifications,default`). Laporan PDF diproses sinkron-first (`dispatchSync` + fallback queue di `ReportController::store`), jadi worker tidak wajib untuk laporan. Di Railway diganti cron 5-menit `--stop-when-empty` menempel di service web (tanpa service ke-2).

Alternatif lokal (test case saja):
- `postgres` — Postgres 16 (`profiles: ["local-db"]`), hanya jalan via `docker compose --profile local-db up`. Default `docker compose up` TIDAK menjalankan postgres — langsung Neon via `DB_URL` di `.env`.

Prod/legacy: `traefik` (`profiles: ["prod"]`) + `cetak-laporan`/`notif-komunikasi` (`profiles: ["legacy"]`) tetap ada untuk kompatibilitas.

Penting (gotchas):
- `QUEUE_CONNECTION=database` (config/queue.php). Notifikasi diproses `worker` (compose/lokal) atau cron Railway; laporan sinkron-first sehingga deterministik tanpa worker. Volume `inventory-storage` shared; PDF `reports/*.pdf` (`app/Jobs/GenerateInventoryReport.php`) ditulis di disk yang sama dengan web (sinkron) atau worker (fallback) — di Railway wajib 1 Volume bersama bila worker dipakai.
- `CACHE_STORE=file` (bukan `database`) — cache DB = RTT Neon tiap hit, sengaja dihindari. `.env.example` default `CACHE_STORE=file`, `DB_URL` Neon aktif `connect_timeout=10`.
- Tidak ada `depends_on: postgres` wajib di `pencatatan`/`worker` — Neon tidak butuh postgres lokal. Untuk test lokal: ubah `.env` ke blok alternatif (`DB_HOST=postgres`) lalu `docker compose --profile local-db up --build`.
- `php artisan migrate` di dalam `pencatatan`: `docker compose exec pencatatan php artisan migrate`. Migration baru `2026_09_04_000000_add_performance_indexes.php` wajib jalan untuk index `stock/minimum_stock/created_at/status`.
- Image `--no-dev` + `docker/php/opcache.ini` (`99-opcache.ini`). Prod build menjalankan `config:cache/route:cache/view:cache/event:cache` di `Dockerfile:16`.
- Dashboard cache `app/Http/Controllers/DashboardController.php:17-27` (`dashboard:stats` 60s, `recentMovements`/`recentNotifications` 30s) — invalidasi di `MovementController`, `ProductController`, `ReportController`, `OrderController::refreshPayment`, `DokuNotificationController`, `GenerateInventoryReport`/`SendInventoryNotification` via `Cache::forget`.

## Perintah umum

- Default Neon (online): `docker compose up --build`
- Test lokal (alternatif): `docker compose --profile local-db up --build` (setelah ubah `.env` ke blok lokal di `.env.example:14-19`)
- Dengan Traefik: `docker compose --profile prod up --build`
- Migrasi: `docker compose exec pencatatan php artisan migrate`
- Lint/format (host only): `vendor/bin/pint`
- Test RBAC: `vendor/bin/phpunit --filter RbacTest` (`phpunit.xml` sqlite memory, `CACHE_STORE=array`, `QUEUE=sync` — isolasi, tidak sentuh Neon). 7 tests di `tests/Feature/RbacTest.php`: admin 200, staff operasional 200, staff 403 admin endpoint, self-promote 403, hapus barang 403, duplikat SKU 422, logout session.

## Aturan domain (pertahankan)

- Pengurangan stok saat pembayaran memakai `DB::transaction` + `lockForUpdate()` — jangan disederhanakan (lihat `OrderController`/`DokuNotificationController`/`MovementController`).
- Webhook `DokuNotificationController` (routes/web.php:18) unauthenticated, wajib validasi HMAC `notificationIsValid()` dulu.
- `OrderController::store` dan `pay`/`refreshPayment` duplikasi transaksi stok — jaga sinkron.

## Lingkungan / secret

- `.env` gitignored; `.env.example` default Neon online (`DB_URL` aktif, `connect_timeout=10`, `CACHE_STORE=file`). Untuk test lokal, matikan `DB_URL` dan uncomment blok lokal `DB_HOST=postgres` di `.env.example:14-19`. `DOKU_SANDBOX=true` default.
- `config/services.php` (google/doku) dari env.

## Penjadwalan

- `routes/console.php` prune failed jobs harian (`queue:prune-failed --hours=168`).
