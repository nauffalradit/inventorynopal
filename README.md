# Nopal A1 Inventory

Aplikasi inventory berbasis Laravel 12, PostgreSQL Neon (online), Docker Compose, dan Traefik. Keep All — laporan PDF, notifikasi, order+DOKU, Google OAuth semua dipertahankan. Postgres lokal hanya alternatif test case.

## Service

- `pencatatan`: aplikasi web untuk pencatatan barang dan mutasi stok (`http://localhost:8000`).
- `worker`: single worker untuk queue `reports,notifications,default` (menggantikan `cetak-laporan` + `notif-komunikasi` yang kini `profiles: ["legacy"]`).
- `postgres`: Postgres 16 lokal — **hanya alternatif test case** via `--profile local-db`, tidak jalan default.

## Menjalankan — Default Neon (Online, Tanpa Lokal)

1. Salin `.env.example` menjadi `.env` (default sudah `DB_URL` Neon aktif, `connect_timeout=10`, `CACHE_STORE=file`).
2. Jalankan Docker Desktop.
3. Jalankan:

```bash
docker compose up --build
```

4. Jalankan migrasi:

```bash
docker compose exec pencatatan php artisan migrate
```

Aplikasi tersedia di `http://localhost:8000`. Dengan Traefik prod: `docker compose --profile prod up --build` → `http://inventory.localhost`.

## Alternatif — Test Case Lokal (Tanpa Neon)

1. Edit `.env`: matikan `DB_URL` (komen), uncomment blok lokal `DB_HOST=postgres` di `.env.example:14-19` (`DB_DATABASE=inventory`, `DB_USERNAME=inventory`, `DB_PASSWORD=secret`).
2. Jalankan:

```bash
docker compose --profile local-db up --build
```

3. Migrasi: `docker compose exec pencatatan php artisan migrate`.

## Deploy Staging (Link Hidup untuk Portfolio)

> Kredensial asli hanya di `.env` (gitignored) / env vars platform — JANGAN di `.env.example`.

1. Push repo ke GitHub (pastikan `git status` bersih dari secret — cek `git log -S "DB_PASSWORD" --oneline`).
2. Buat service di Railway/Render/Fly dari repo ini (1 service cukup; worker bisa jadi service ke-2 dengan command `php artisan queue:work --queue=reports,notifications,default`).
3. Set env vars staging (copy dari `.env.example`, isi nilai asli):
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domain-staging>`
   - `DB_URL` (Neon, sama seperti lokal), `CACHE_STORE=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=database`
   - `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI=https://<domain-staging>/auth/google/callback`
   - `ADMIN_EMAILS` (email kamu — emergency access), `REGISTRATION_MODE=open`
   - `DOKU_SANDBOX=true` + `DOKU_CLIENT_ID`/`DOKU_SECRET_KEY` sandbox
4. Tambahkan `GOOGLE_REDIRECT_URI` staging ke Authorized redirect URIs di Google Cloud Console.
5. Jalankan migrasi sekali: `php artisan migrate --force`.
6. Verifikasi: login Google → dashboard → buat barang → buat order → `vendor/bin/phpunit --filter RbacTest` hijau di CI/lokal.

Catatan: database staging boleh pakai Neon yang sama (data sharing dengan lokal) atau Neon branch terpisah (data isolasi, disarankan).
