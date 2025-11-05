<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class SocialMediaAccountController extends Controller
{
    /**
     * Versi Graph API yang digunakan.
     */
    private string $graph = 'v23.0';

    public function index()
    {
        $accounts = SocialMediaAccount::latest('updated_at')->paginate(10);
        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        // Normalisasi platform ke lowercase biar konsisten
        $request->merge(['platform' => strtolower((string)$request->platform)]);

        // Validasi dasar per platform
        $request->validate([
            'platform' => ['required', Rule::in(['facebook', 'instagram', 'x'])],
            'username' => 'required|string',
            'access_token' => 'required|string',
            'access_token_secret' => 'required_if:platform,x|nullable|string',
            'page_id' => 'nullable|string',
            // Instagram: minimal salah satu (page_id atau ig_user_id) harus ada
            'ig_user_id' => [
                'nullable', 'string',
                function ($attr, $val, $fail) use ($request) {
                    if ($request->platform === 'instagram' && empty($val) && empty($request->page_id)) {
                        $fail('Untuk Instagram, isi IG User ID atau Page ID.');
                    }
                }
            ],
        ]);

        // Trim whitespace yang sering bikin masalah
        $platform = strtolower(trim((string)$request->platform));
        $username = trim((string)$request->username);
        $accessToken = trim((string)$request->access_token);
        $accessTokenSecret = trim((string)$request->access_token_secret);
        $pageId = $request->filled('page_id') ? trim((string)$request->page_id) : null;
        $igUserId = $request->filled('ig_user_id') ? trim((string)$request->ig_user_id) : null;

        // Validasi token Facebook/IG (opsional: jika kamu punya helper khusus)
        if ($platform === 'facebook') {
            // Jika kamu punya helper validasi, panggil di sini.
            // Contoh:
            // $validation = \App\Helpers\FacebookHelper::validateAccessToken($accessToken);
            // if (!$validation['valid']) return back()->withErrors(['access_token' => 'Token tidak valid: '.$validation['error']])->withInput();

            if (!$pageId) {
                return back()->withErrors(['page_id' => 'Page ID wajib diisi untuk Facebook.'])->withInput();
            }
        }

        // Instagram: resolve ig_user_id dari page_id jika belum diisi
        if ($platform === 'instagram' && !$igUserId && $pageId) {
            $resolved = $this->getIgUserIdFromPage($pageId, $accessToken);
            if (!$resolved) {
                return back()->withErrors([
                    'ig_user_id' => 'Tidak bisa mendapatkan IG User ID dari Page. Pastikan Page terhubung ke IG Business/Creator dan token adalah Page Access Token.'
                ])->withInput();
            }
            $igUserId = $resolved;
        }

        // Waktu sekarang untuk created_at dan updated_at
        $now = now();

        // Simpan - expired otomatis 10 menit setelah dibuat
        $data = [
            'platform' => $platform,
            'username' => $username,
            'access_token' => $accessToken,
            'access_token_secret' => $platform === 'x' ? $accessTokenSecret : null,
            'page_id' => ($platform === 'facebook' || $platform === 'instagram') ? $pageId : null,
            'ig_user_id' => $platform === 'instagram' ? $igUserId : null,
            'expires_at' => $now->copy()->addMinutes(10), // Expired 10 menit setelah create
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Tangani unique constraint (platform, ig_user_id) untuk Instagram
        try {
            SocialMediaAccount::create($data);
        } catch (\Throwable $e) {
            // Kemungkinan melanggar constraint unik atau error lain
            return back()->withErrors([
                'general' => 'Gagal menyimpan akun: ' . $e->getMessage()
            ])->withInput();
        }

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil ditambahkan dan akan expired dalam 10 menit');
    }

    public function edit(SocialMediaAccount $account)
    {
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, SocialMediaAccount $account)
    {
        // Normalisasi
        $request->merge(['platform' => strtolower((string)$request->platform)]);

        $request->validate([
            'platform' => ['required', Rule::in(['facebook', 'instagram', 'x'])],
            'username' => 'required|string',
            'access_token' => 'required|string',
            'access_token_secret' => 'required_if:platform,x|nullable|string',
            'page_id' => 'nullable|string',
            'ig_user_id' => [
                'nullable', 'string',
                function ($attr, $val, $fail) use ($request) {
                    if ($request->platform === 'instagram' && empty($val) && empty($request->page_id)) {
                        $fail('Untuk Instagram, isi IG User ID atau Page ID.');
                    }
                }
            ],
        ]);

        $platform = strtolower(trim((string)$request->platform));
        $username = trim((string)$request->username);
        $accessToken = trim((string)$request->access_token);
        $accessTokenSecret = trim((string)$request->access_token_secret);
        $pageId = $request->filled('page_id') ? trim((string)$request->page_id) : null;
        $igUserId = $request->filled('ig_user_id') ? trim((string)$request->ig_user_id) : null;

        // Facebook: wajib Page ID
        if ($platform === 'facebook' && !$pageId) {
            return back()->withErrors(['page_id' => 'Page ID wajib diisi untuk Facebook.'])->withInput();
        }

        // Instagram: resolve ig_user_id jika kosong tapi ada page_id
        if ($platform === 'instagram' && !$igUserId && $pageId) {
            $resolved = $this->getIgUserIdFromPage($pageId, $accessToken);
            if (!$resolved) {
                return back()->withErrors([
                    'ig_user_id' => 'Tidak bisa mendapatkan IG User ID dari Page. Pastikan Page terhubung ke IG Business/Creator dan token adalah Page Access Token.'
                ])->withInput();
            }
            $igUserId = $resolved;
        }

        // Update waktu - expired 10 menit setelah update
        $now = now();

        // Jangan nubruk unique constraint untuk akun IG lain
        try {
            $account->update([
                'platform' => $platform,
                'username' => $username,
                'access_token' => $accessToken,
                'access_token_secret' => $platform === 'x' ? $accessTokenSecret : null,
                'page_id' => ($platform === 'facebook' || $platform === 'instagram') ? $pageId : null,
                'ig_user_id' => $platform === 'instagram' ? $igUserId : null,
                'expires_at' => $now->copy()->addMinutes(10), // Reset expired ke 10 menit setelah update
                'updated_at' => $now,
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'general' => 'Gagal mengupdate akun: ' . $e->getMessage()
            ])->withInput();
        }

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diupdate dan expired timer direset ke 10 menit');
    }

    public function destroy(SocialMediaAccount $account)
    {
        $username = $account->username;
        $account->delete();
        return back()->with('success', "Akun {$username} berhasil dihapus");
    }

    /**
     * Ambil IG User ID dari Page menggunakan Page Access Token.
     * Coba 2 field: connected_instagram_account & instagram_business_account.
     */
    private function getIgUserIdFromPage(string $pageId, string $pageAccessToken): ?string
    {
        // Safety trim
        $pageId = trim($pageId);
        $pageAccessToken = trim($pageAccessToken);

        // Panggil Graph API
        $url = "https://graph.facebook.com/{$this->graph}/{$pageId}";
        $resp = Http::get($url, [
            'fields' => 'connected_instagram_account,instagram_business_account',
            'access_token' => $pageAccessToken,
        ]);

        if (!$resp->successful()) {
            // Log detail agar mudah debug di laravel.log
            \Log::error('❌ Gagal ambil IG User ID dari Page', [
                'page_id' => $pageId,
                'http' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return null;
        }

        $json = $resp->json();
        return data_get($json, 'connected_instagram_account.id')
            ?: data_get($json, 'instagram_business_account.id');
    }

    /**
     * Method helper untuk cek status expired (opsional, bisa dipanggil dari view)
     */
    public static function getAccountStatus(SocialMediaAccount $account): array
    {
        $lastModified = $account->updated_at ?? $account->created_at;
        $expiredAt = $lastModified->copy()->addMinutes(10);
        $isExpired = now()->isAfter($expiredAt);
        $minutesLeft = $isExpired ? 0 : now()->diffInMinutes($expiredAt, false);

        return [
            'is_expired' => $isExpired,
            'minutes_left' => $minutesLeft,
            'expired_at' => $expiredAt,
            'last_modified' => $lastModified,
        ];
    }
}
