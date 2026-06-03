# Nopal A1 Inventory

Aplikasi inventory berbasis Laravel, PostgreSQL Neon, Docker Compose, dan Traefik.

## Service

- `pencatatan`: aplikasi web untuk pencatatan barang dan mutasi stok.
- `cetak-laporan`: worker queue untuk membuat laporan inventory.
- `notif-komunikasi`: worker queue untuk notifikasi dan komunikasi.

## Menjalankan

1. Salin `.env.example` menjadi `.env`.
2. Isi konfigurasi Neon PostgreSQL pada `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
3. Jalankan Docker Desktop.
4. Jalankan:

```bash
docker compose up --build
```

5. Jalankan migrasi:

```bash
docker compose exec pencatatan php artisan migrate
```

Traefik akan mengekspos aplikasi di `http://inventory.localhost`.
