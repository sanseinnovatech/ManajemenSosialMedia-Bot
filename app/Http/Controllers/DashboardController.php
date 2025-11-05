<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// Ganti sesuai nama model yang kamu pakai
use App\Models\Broadcast;
use App\Models\Account;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Sesuaikan middleware-mu (Jetstream/Breeze biasanya pakai auth + verified)
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Tampilkan Dashboard dengan ringkasan Broadcast & Akun.
     */
    public function index(Request $request)
    {
        // Ambil data secukupnya (ringan)
        $broadcasts = Broadcast::query()
            ->select(['id','caption','media_path','status','created_at'])
            ->latest('created_at')
            ->limit(100)
            ->get();

        $accounts = Account::query()
            ->select(['id','platform','username','access_token','access_token_secret','page_id','created_at','updated_at'])
            ->latest('updated_at')
            ->limit(100)
            ->get();

        // (Opsional) siapkan seri 7 hari untuk grafik
        $now = Carbon::now();
        $series7 = collect(range(6, 0))->mapWithKeys(function ($i) use ($now) {
            return [$now->copy()->subDays($i)->format('Y-m-d') => 0];
        })->all();

        foreach ($broadcasts as $b) {
            $d = optional($b->created_at)->format('Y-m-d');
            if ($d && array_key_exists($d, $series7)) {
                $series7[$d] += 1;
            }
        }

        return view('dashboard', [
            'broadcasts' => $broadcasts,
            'accounts'   => $accounts,
            'series7'    => $series7, // opsional dipakai di view
        ]);
    }
}
