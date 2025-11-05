<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SocialMediaAccount;

class SocialMediaAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['platform' => 'Instagram', 'username' => 'ig_toko1'],
            ['platform' => 'Instagram', 'username' => 'ig_toko2'],
            ['platform' => 'Facebook', 'username' => 'fb_toko1'],
            ['platform' => 'X', 'username' => 'x_toko1'],
            ['platform' => 'YouTube', 'username' => 'yt_toko1'],
            ['platform' => 'TikTok', 'username' => 'tt_toko1'],
        ];

        foreach ($accounts as $acc) {
            SocialMediaAccount::create([
                'platform' => $acc['platform'],
                'username' => $acc['username'],
                'access_token' => 'dummy_token_' . rand(1000, 9999),
                'refresh_token' => 'dummy_refresh_' . rand(1000, 9999),
                'expires_at' => now()->addDays(30),
            ]);
        }
    }
}
