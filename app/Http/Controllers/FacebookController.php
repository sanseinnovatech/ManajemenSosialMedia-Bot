<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SocialMediaAccount;
use Carbon\Carbon;

class FacebookController extends Controller
{
    public function callback(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('accounts.index')->with('error', 'Authorization code tidak ditemukan.');
        }

        // Ganti dengan kredensial Facebook App kamu
        $clientId     = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');
        $redirectUri  = route('facebook.callback');

        // Step 1: Tukar code ke token
        $tokenRes = Http::get('https://graph.facebook.com/v18.0/oauth/access_token', [
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'client_secret' => $clientSecret,
            'code'          => $code,
        ]);

        if (!$tokenRes->successful()) {
            return redirect()->route('accounts.index')->with('error', 'Gagal menukar code menjadi token.');
        }

        $accessToken = $tokenRes['access_token'];

        // Step 2: Ambil info user
        $me = Http::get('https://graph.facebook.com/me', [
            'access_token' => $accessToken,
            'fields' => 'id,name',
        ]);

        if (!$me->successful()) {
            return redirect()->route('accounts.index')->with('error', 'Gagal mengambil info akun Facebook.');
        }

        // Step 3: Simpan ke database
        SocialMediaAccount::create([
            'platform' => 'Facebook',
            'username' => $me['name'],
            'access_token' => $accessToken,
            'expires_at' => Carbon::now()->addDays(60), // default long-lived
        ]);

        return redirect()->route('accounts.index')->with('success', 'Akun Facebook berhasil ditambahkan.');
    }
}
