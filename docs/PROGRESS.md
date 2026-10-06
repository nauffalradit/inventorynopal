# PROGRESS — Nopal A1 Inventory (otak luar repo)

> Aturan pakai: update tiap tutup sesi (5 baris). Buka sesi baru: baca file ini + `git log --oneline -10` dulu.
> Pemilik repo memegang git (commit/push manual). Tanpa `--force` ke `main`. Secret tidak pernah di repo.

## Konteks pemilik

- Nauffal — portfolio pertama, deploy pertama, web pertama yang proper. Tujuan: tempat belajar jadi programmer profesional (BUKAN sidang; koreksi 29 Sep 2026).
- Mode: fullstack seimbang, 6–10 jam/minggu. Arrange: plan mode = rencana saja, build mode = eksekusi.
- Stack: Laravel 12, Neon Postgres (sharing lokal+staging), Railway (web `serve` + cron 5-menit, tanpa worker), PHP 8.4 image / 8.5 laptop.

## Kurikulum UI modern (disepakati 29–30 Sep)

- U1 tokens ✅ (30 Sep): `public/css/tokens.css` + kontrak di AGENTS.md + `DesignTokensTest`. Prinsip: nama=makna, tokenize tanpa redesign. Keputusan: type 7 tangga (geser 1px diterima), radius 9→8, file terpisah. Koreksi: `--muted` tetap `#7c879d`.
- U2 komponen ✅ (3 Okt): `x-card/x-btn/x-badge/x-field/x-table/x-empty-state` + migrasi 12 halaman (dashboard, products/*+_form, orders/*, reports/index, admin/users/index, notifications/index+show). Pelajaran: `@error($var)` dinamis rapuh di komponen → pakai `$errors->has/first` eksplisit; tabel detail key-value (notif show) tetap raw table; `auth/login` (standalone, palet teal sendiri) + `reports/pdf` (dompdf) SENGAJA tidak disentuh.
- U3 NOTIFIKASI (batch B, 6 Okt, BELUM commit): `x-check-list` reusable (checkbox kartu + avatar inisial + search + Pilih-semua + scroll 200px) + panel Pratinjau live + char-count + Kirim disabled (progressive enhancement; validasi tetap server). Sisa U3: layout tokenize → admin + notif-show → thumbnail unifikasi → pagination/sisa → login DITUNDA (standalone).
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

## Status terakhir (6 Okt 2026, batch B notifikasi)

- HEAD = `053bb0d` (U2 commit, tree bersih sebelum batch ini). Batch B BELUM commit: `x-check-list` + recipients search + pratinjau + sisa `x-btn` notifikasi + `NotificationCheckListTest`.
- Test 42/42 (174 assertions), pint passed (verifikasi 6 Okt, isolasi sqlite — +3 dari test render checklist/pratinjau).
- Referensi mock `Notif & Komunikasi – Konsep Ulang.html`: pola diadopsi (search, kartu checkbox avatar, pratinjau live, char-count, Kirim disabled); palet/font mock DITOLAK (kontrak U1), channel tetap 3, riwayat tetap 7 kolom + Aksi.
- Antre: preview lokal batch B (Anda) → commit → smoke staging → layout tokenize → admin + notif-show → thumbnail → pagination/sisa → login ditunda.

## Sesi (b): debug mandiri (jadwal tiap 2 minggu)

- Ambil 1 bug kecil, TANPA asisten: hanya log + test. Catat waktu + jalan buntu. Asisten menilai setelahnya.
