# PROGRESS — Nopal A1 Inventory (otak luar repo)

> Aturan pakai: update tiap tutup sesi (5 baris). Buka sesi baru: baca file ini + `git log --oneline -10` dulu.
> Pemilik repo memegang git (commit/push manual). Tanpa `--force` ke `main`. Secret tidak pernah di repo.

## Konteks pemilik

- Nauffal — portfolio pertama, deploy pertama, web pertama yang proper. Tujuan: tempat belajar jadi programmer profesional (BUKAN sidang; koreksi 29 Sep 2026).
- Mode: fullstack seimbang, 6–10 jam/minggu. Arrange: plan mode = rencana saja, build mode = eksekusi.
- Stack: Laravel 12, Neon Postgres (sharing lokal+staging), Railway (web `serve` + cron 5-menit, tanpa worker), PHP 8.4 image / 8.5 laptop.

## Kurikulum UI modern (disepakati 29–30 Sep)

- U1 tokens ✅ (30 Sep): `public/css/tokens.css` + kontrak di AGENTS.md + `DesignTokensTest`. Prinsip: nama=makna, tokenize tanpa redesign. Keputusan: type 7 tangga (geser 1px diterima), radius 9→8, file terpisah. Koreksi: `--muted` tetap `#7c879d`.
- U2 komponen ✅ SEBAGIAN (30 Sep–2 Okt): `x-card/x-btn/x-badge/x-field/x-table/x-empty-state` + pilot halaman notifikasi (tabel 7 kolom: Channel/Penerima/Subjek/Status/Dibaca/Waktu/Aksi + `notifications/show`). Pelajaran: `@error($var)` dinamis rapuh di komponen → pakai `$errors->has/first` eksplisit. Sisa U2: migrasi 11 halaman lain (dashboard, products/*, orders/*, reports/index, admin/users/index, auth/login — lain waktu).
- U3 migrasi halaman: dashboard → produk → order → laporan → **notifikasi (termasuk `<x-check-list>` penerima — KONTRAK TERTUNDA, jangan bangun standalone)** → admin/auth.
- U4 review & docs.

## Keputusan tercatat (jangan dibuka ulang tanpa alasan baru)

1. Opsi A tanpa Docker (lokal `serve` + `queue:work`; Fedora ringan).
2. Export CSV/XLSX DIBATALKAN — PDF saja.
3. Hapus: laporan saja (notifikasi tidak). Hapus ber-order → banner ramah (bukan 422/500).
4. Commit atomic kecil; amend hanya sebelum push; tidak pernah force ke main.
5. Notifikasi: log-only diterima (tanpa SMTP/WA real); penerima = akun terdaftar, multi, semua/admin/staff; channel = label arsip.
6. UI: ukuran/spacing dulu, warna belakangan. Pills + penerima bersyarat. Thumbnail 72px + klik perbesar; restock berfoto.
7. Arsitektur antrean: laporan sinkron-first + fallback queue; notifikasi via queue + cron; worker service pensiun; Volume bersama untuk upload (verifikasi: foto tahan redeploy).
8. Trial Railway → 1 bulan Hobby $5 menjelang butuh; jangka panjang: VPS DO $4/Lightsail $5 (compose penuh). AWS EC2: hanya bila butuh keyword CV. Hetzner entry tidak tersedia (2026).

## Gotcha aktif (masih berlaku!)

1. Railway ABAIKAN `docker-compose.yml` (hanya baca Dockerfile): `$PORT` dinamis, Start Command dashboard hanya untuk Nixpacks, Networking port ikut `$PORT` (kosongkan = auto).
2. DB Neon sharing, DISK terpisah (lokal vs staging, web vs worker): file lahir di disk eksekutor. Aturan: uji lokal→worker lokal ON; uji staging→worker lokal OFF. Jangan dua-duanya ON.
3. Volume fresh = kosong + milik root → app butuh mkdir saat start; image jalan sebagai root (lihat Dockerfile CMD). Volume beda ID = disk beda (pernah 2 volume!).
4. `queue:work` daemon di Railway TERBUKTI tidak polling (kasus tak terpecahkan, 28 Sep): gejala = log sepi + attempts=0. Obat baku: cron `--stop-when-empty` atau jalur sinkron. Jangan ulangi debug daemon.
5. Foto pre-Volume hilang tiap redeploy; symlink `public/storage` via CMD; `asset()` ikut `APP_URL` (https final).
6. Trust proxy wajib (`trustProxies(at:'*')`) — tanpa itu URL http → warning "not secure".
7. PHP host tanpa GD: test gambar pakai byte PNG minimal, bukan `fake()->image()`.
8. `config/database.php` WAJIB punya koneksi `sqlite` (tanpa `url`!) agar phpunit isolasi — jangan hapus.
9. Dobel-klik tombol submit = job ganda → guard global di layout (`data-double-submit-guard`).
10. Secret `bb25c3f:.env.example` bocor → password Neon SUDAH dirotasi (dead). Jangan taruh secret di `.env.example` lagi.

## Status terakhir (3 Okt 2026, sinkron pasca-4-commit)

- HEAD = `d151aa2` (tree bersih): `070095a` U2 + `f82afc6` PROGRESS + `0e67ddb` detail notifikasi (admin+staff bisa baca isi) + `d151aa2` tandai-dibaca (owner/admin, idempotent) + bel unread + hapus admin-only.
- Test 39/39 (158 assertions), pint passed (verifikasi 3 Okt, isolasi sqlite — dulu 32/32 pada 30 Sep, +7 dari fitur detail/read/delete/bell). Staging: smoke hijau + video demo 2:55 (30 Sep, sebelum 4 commit ini — smoke ulang antre).
- Antre: smoke staging pasca-U2 (pilot 7-kolom + show + dibaca/hapus) → U2 lanjutan (11 halaman) / U3 `<x-check-list>` (kontrak tertunda) / sesi (b) debug mandiri.

## Sesi (b): debug mandiri (jadwal tiap 2 minggu)

- Ambil 1 bug kecil, TANPA asisten: hanya log + test. Catat waktu + jalan buntu. Asisten menilai setelahnya.
