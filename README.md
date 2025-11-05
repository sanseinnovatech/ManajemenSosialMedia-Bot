# Social Bot Suite (Laravel)

Sistem otomasi broadcast lintas platform (Facebook Page, Instagram, X/Twitter) dengan fokus pada:
- **Broadcast** (buat, jadwalkan, riwayat, retry)
- **Akun Sosial Media** (hubungkan akun, monitor token & masa berlaku)
- **Manajemen Token** (peringatan kedaluwarsa, refresh flow)
- **Dashboard** ringkas (KPI dasar & daftar broadcast terbaru)

> Catatan: Modul **Asset Library**, **Hashtag Bank**, dan **Analytics Overview** **TIDAK** disertakan. Dokumentasi ini hanya mencakup fitur yang digunakan saat ini.

---

## 🧰 Tech Stack

- **Backend**: Laravel 11/12 (PHP 8.2+)
- **Frontend**: Blade + Tailwind CSS
- **Auth**: Laravel Breeze/Jetstream (opsional)
- **DB**: MySQL/MariaDB
- **Storage**: `storage/app/public` (link ke `public/storage`)
- **UI**: SweetAlert2 (modal/konfirmasi)

---

## ⚙️ Instalasi

```bash
git clone <repo>
cd <repo>
composer install
cp .env.example .env
php artisan key:generate
```

### Konfigurasi `.env` (dasar)

```env
APP_NAME="Social Bot Suite"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=socialbot
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

### Jalankan migrasi & storage link

```bash
php artisan migrate
php artisan storage:link
```

### Jalankan aplikasi

```bash
php artisan serve
# (opsional, jika pakai Vite & Tailwind JIT)
npm install
npm run dev
```

---

## 🧭 Panduan Penggunaan Singkat

### 1) Login & Dashboard
- Masuk ke `/dashboard` setelah login.
- Lihat ringkasan KPI dasar (total broadcast, success rate sederhana) dan broadcast terbaru.
- Pantau status token akun yang hampir kedaluwarsa.

### 2) Broadcast
- Menu **Broadcast** → **Buat Baru** untuk membuat posting lintas platform (FB Page/IG/X).
- Isi **caption**, pilih **platform tujuan**, dan (jika ada) lampirkan media dengan URL publik.
- Simpan/jadwalkan. Pada kegagalan, gunakan tombol **Retry** di daftar broadcast.

### 3) Akun Sosial Media
- Menu **Akun Sosial Media** → **Tambah Akun** untuk menghubungkan Facebook/Instagram/X.
- Setelah proses OAuth, sistem menyimpan token yang diperlukan.
- Halaman daftar akun menampilkan **platform**, **username**, dan **sisa masa token**. Lakukan re-auth jika mendekati kedaluwarsa.

---

## 🔐 Token & OAuth (FB/IG & X) — Langkah Praktis

### A. Facebook Page + Instagram (Meta)

Kebutuhan:
- **Facebook Developer Account**
- **Aplikasi Meta** (disarankan *Business* app type untuk Instagram Graph API)
- **Facebook Page** yang terhubung ke **Instagram Business/Creator**

**Permissions umum** untuk posting & baca dasar:  
`pages_show_list`, `pages_read_engagement`, `pages_manage_posts`, `instagram_basic`, `instagram_content_publish`  
(Opsional analitik: `instagram_manage_insights` — tidak dibahas di dok ini)

**Alur ringkas**:
1) User login via Facebook Login → dapat **User Access Token (short-lived)**.  
2) Tukar menjadi **Long-Lived User Token** (~60 hari).  
3) Ambil daftar **Page** → pilih **Page Access Token**.  
4) Dari Page, dapatkan **instagram_business_account.id** (IG Business ID).  
5) Publish ke IG menggunakan **container** → **media_publish**.

**Contoh cURL (IG Graph API):**
```bash
# 1) Buat container untuk gambar
curl -X POST "https://graph.facebook.com/v19.0/{ig_business_id}/media" \
  -F "image_url=https://your-domain.com/storage/sample.jpg" \
  -F "caption=Promo #diskon" \
  -F "access_token={page_access_token}"

# 2) Publish container
curl -X POST "https://graph.facebook.com/v19.0/{ig_business_id}/media_publish" \
  -F "creation_id={creation_id}" \
  -F "access_token={page_access_token}"
```

**Contoh cURL (Facebook Page):**
```bash
curl -X POST "https://graph.facebook.com/v19.0/{page_id}/feed" \
  -F "message=Halo dari Social Bot Suite" \
  -F "link=https://your-domain.com" \
  -F "access_token={page_access_token}"
```

**ENV Meta (contoh):**
```env
META_APP_ID=your_meta_app_id
META_APP_SECRET=your_meta_app_secret
META_REDIRECT_URI=https://your-domain.com/oauth/meta/callback
```

> Pastikan **Redirect URI** cocok di **App Settings** (Meta) dan di `.env`. Untuk lokal, gunakan **ngrok** agar callback HTTPS valid.

### B. X (Twitter) — OAuth 2.0 (Authorization Code + PKCE)

Kebutuhan:
- **Developer Portal**: buat Project & App
- Permissions: **Read and Write** (+ **Offline Access** jika ingin refresh token)
- Scopes: `tweet.read tweet.write users.read offline.access`
- **Callback URL** harus identik dengan konfigurasi app

**Alur ringkas**:
1) Redirect user ke consent URL.  
2) Terima `code` pada callback.  
3) Tukar `code` → `access_token` (+ `refresh_token`).  
4) Gunakan `access_token` untuk posting tweet.

**Contoh cURL (tweet teks):**
```bash
curl -X POST "https://api.twitter.com/2/tweets" \
  -H "Authorization: Bearer {user_access_token}" \
  -H "Content-Type: application/json" \
  -d '{"text":"Hello from Social Bot Suite #promo"}'
```

**ENV X (contoh):**
```env
X_CLIENT_ID=your_x_client_id
X_CLIENT_SECRET=your_x_client_secret
X_REDIRECT_URI=https://your-domain.com/oauth/x/callback
```

---

## 🛣️ Route & Controller OAuth (Contoh Minimal)

Tambahkan route berikut ke `routes/web.php`:
```php
use App\Http\Controllers\OAuth\MetaOAuthController;
use App\Http\Controllers\OAuth\XOAuthController;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/oauth/meta/start',    [MetaOAuthController::class, 'start'])->name('oauth.meta.start');
    Route::get('/oauth/meta/callback', [MetaOAuthController::class, 'callback'])->name('oauth.meta.callback');

    Route::get('/oauth/x/start',       [XOAuthController::class, 'start'])->name('oauth.x.start');
    Route::get('/oauth/x/callback',    [XOAuthController::class, 'callback'])->name('oauth.x.callback');
});
```

> Implementasi controller mengikuti flow standar (exchange token, ambil Page & IG Business ID, simpan ke tabel `accounts`). Tambahkan validasi/error handling sesuai kebutuhan produksi.

---

## 🧪 Health Check & Debug

- **Meta**: Pastikan Page ↔ IG Business/Creator **terhubung**, dan user **Admin** di Page.  
- **Token 60 hari**: lakukan **exchange ulang** sebelum masa habis.  
- **Callback error**: cek kesesuaian **Redirect URI** dan `.env`.  
- **X**: pastikan `tweet.write` aktif dan App berizin **Read + Write**.

---

## 🔒 Keamanan

- Jangan commit `.env` ke repo.  
- Simpan token secara aman (ENV/terenkripsi).  
- Batasi akses admin (auth + verified).  
- Audit log untuk aksi penting (publish, delete, revoke).

---

## 📝 Lisensi

MIT — bebas digunakan & dimodifikasi.

---

> Jika di kemudian hari fitur **Asset Library**, **Hashtag Bank**, atau **Analytics** diaktifkan kembali, README ini bisa diperluas dengan petunjuk modul tersebut.
