# Bebras IT Del Portal

Portal resmi **Bebras Biro Institut Teknologi Del** (Gerakan PANDAI) — `https://bebras.del.ac.id`.

Aplikasi Laravel yang menggantikan situs statis lama. Fitur utama:

- **Halaman publik:** beranda, berita & pengumuman, dan detail berita.
- **Pencarian sertifikat:** peserta mencari sertifikat dengan *Bebras Username* (tanpa login), difilter per tahun, dikelompokkan per edisi, dan diunduh sebagai PDF privat.
- **Panel admin** (Filament) untuk mengelola berita, konten situs (teks beranda, kontak, media sosial), sertifikat, dan impor massal sertifikat (CSV + ZIP).

## Teknologi

| | |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Admin | Filament 5 (`/admin`) |
| Database | MySQL (dev saat ini) atau SQLite |
| Front-end | Blade, Bootstrap 5, jQuery, Font Awesome 5 (tanpa build step) |

## Menjalankan secara lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
# atur DB_* di .env, lalu:
php artisan migrate --seed
php artisan serve
```

Situs berjalan di <http://localhost:8000>, panel admin di <http://localhost:8000/admin>.

Impor massal berjalan lewat antrean. Jalankan worker di terminal terpisah:

```bash
php artisan queue:work
```

Tidak ada langkah `npm`; seluruh aset front-end ada di `public/`.

## Akun admin

Dibuat otomatis oleh `php artisan db:seed` (`database/seeders/DatabaseSeeder.php`):

| | |
|---|---|
| URL | `/admin` |
| Email | `admin@bebras.del.ac.id` |
| Password | `password` |

> **Penting:** password ini hanya untuk pengembangan lokal (`local`/`testing`). Di luar itu, `db:seed` memakai `ADMIN_PASSWORD`
> dari `.env`, atau membuat password acak yang ditampilkan sekali. Setelah masuk, ganti password lewat menu pengguna → **Profil**.

### Profil & autentikasi dua faktor (Google Authenticator)

- Menu pengguna (kanan atas) → **Profil**: ubah nama, email, dan password (butuh password saat ini; password baru min. 12 karakter, huruf besar/kecil, angka, dan tidak bocor di produksi).
- Di halaman Profil, aktifkan **Aplikasi autentikator**: pindai kode QR dengan Google Authenticator (atau aplikasi TOTP lain), masukkan kode 6 digit, lalu **simpan kode pemulihan** (hanya tampil sekali).
- Saat masuk, setelah email + password diminta kode 6 digit (atau satu kode pemulihan).
- Untuk mewajibkan 2FA bagi semua admin, ubah `isRequired: false` menjadi `true` di `AdminPanelProvider` (`->multiFactorAuthentication(...)`).
- Admin kehilangan ponsel dan kode pemulihan: `php artisan bebras:2fa-reset admin@bebras.del.ac.id`.
- Rahasia 2FA dienkripsi dengan `APP_KEY`; jangan ganti/hilangkan `APP_KEY` tanpa mengatur ulang 2FA.

## Alur kerja admin

0. **Dashboard** — statistik peserta per tahun dan kategori (dihitung dari sertifikat yang sudah diunggah) serta statistik berita & pengumuman.
1. **Berita** — buat, jadwalkan, dan publikasikan berita.
2. **Pengaturan Situs** — ubah teks beranda, statistik, alamat, telepon, email, dan tautan Facebook/Instagram/YouTube (`contact_*`, `social_*`). Footer membaca nilai ini dan di-cache 10 menit; cache dibersihkan otomatis saat disimpan.
3. **Sertifikat** — unggah satu per satu. Kombinasi `(username, tahun, kategori, jenis penghargaan)` bersifat unik.
4. **Impor massal** — unggah CSV metadata + ZIP berisi PDF. Kolom CSV:
   `bebras_username,participant_name,school_name,category,award_type,year,file_name`.
   Contoh data dapat dibuat dengan `php sample-bulk-import/generate_samples.php`.

Kategori: `SiKecil`, `Siaga`, `Penggalang`, `Penegak`. Jenis penghargaan: `Participant` (Peserta), `Winner` (Pemenang).

## Pencarian sertifikat

- URL dapat dibagikan: `/sertifikat?username=budi_123&year=2024`.
- Username dinormalkan ke huruf kecil. Hasil dikelompokkan per tahun (tahun terbaru terbuka).
- PDF disimpan di disk privat `storage/app/private/certificates` dan hanya dapat diunduh lewat `/sertifikat/unduh/{id}`.
- Nama berkas unduhan: `{username}_{tahun}_{kategori}_{penghargaan}_sertifikat.pdf`.
- Halaman hasil bertanda `noindex`; pencarian dibatasi 40 permintaan/menit per IP, unduhan 30/menit.

## Tema & struktur UI

Satu tema ceria ramah anak dipakai oleh situs publik **dan** panel admin.

| Berkas | Fungsi |
|---|---|
| `public/css/theme.css` | **Satu-satunya sumber** warna, radius, bayangan, dan font (token CSS). Ubah palet hanya di sini. |
| `public/css/style.css` | Gaya situs publik; hanya memakai token (`var(--...)`). |
| `public/css/admin.css` | Gaya panel Filament; tanpa kode warna, hanya menunjuk ke token di `theme.css`. |
| `resources/views/partials/theme-head.blade.php` | Font (Fredoka + Nunito) dan `theme.css`, dimuat oleh layout publik dan panel admin. |

- **Warna kategori** (satu warna per kategori, publik dan admin): SiKecil = pink, Siaga = hijau mint, Penggalang = kuning, Penegak = ungu. Token: `--cat-{kategori}`; kelas publik: `.cat-badge .cat-{kategori}`; admin: `Certificate::categoryKey()`.
- **Kontras:** teks kecil memakai `--brand-blue-ink` / `--brand-blue-strong` (>= 4.5:1). Jangan memakai `--brand-blue` untuk teks kecil.
- **Gerak:** hanya efek halus; dimatikan oleh `prefers-reduced-motion`.
- Seluruh halaman publik memakai `layouts/app.blade.php`. Komponen bersama: `<x-page-header>` (satu-satunya `<h1>`), `<x-empty-state>`, serta kelas `.surface`, `.page-section`, `.eyebrow`, `.rainbow-rule`.
- Warna baru untuk admin: tambahkan token di `theme.css`, daftarkan palet dasar di `AdminPanelProvider`, lalu arahkan ramp-nya di `admin.css`.

Saat menambah halaman baru, gunakan `<x-page-header>`, bungkus konten dengan `.page-section`, dan kartu dengan `.surface`.

## Pengujian

```bash
php artisan test
```

Mencakup landing page, berita, pencarian & unduhan sertifikat, impor massal, serta konsistensi UI dan tema (satu `<h1>`, tautan navigasi, footer dari `SiteSetting`, palet hanya di `theme.css`, warna kategori, tema yang sama di situs dan admin).

## Lisensi

Kode aplikasi dilisensikan di bawah [GPL-3.0](LICENSE). Templat tampilan berasal dari HTML Codex dan tunduk pada [LICENSE.txt](LICENSE.txt) (atribusi wajib dipertahankan).
