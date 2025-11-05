<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialMediaAccount;
use Illuminate\Support\Facades\Storage;
use App\Models\Broadcast;
use Illuminate\Support\Facades\Http;
use App\Helpers\TwitterHelper;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\Helpers\InstagramHelper as IG;

class BroadcastController extends Controller
{
    private string $graph = 'v23.0';

    public function index()
    {
        $broadcasts = Broadcast::latest()->get();
        return view('broadcast.index', compact('broadcasts'));
    }

    public function create()
    {
        return view('broadcast.create');
    }

    public function send(Request $request)
    {
        \Log::info('🚀 BroadcastController@send dipanggil');

        $request->validate([
            'accounts' => 'required|array',
            'caption'  => 'required|string',
            'media'    => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:10240',
        ]);

        $path = $request->hasFile('media')
            ? $request->file('media')->store('broadcasts', 'public')
            : null;

        $broadcast = Broadcast::create([
            'caption'    => $request->caption,
            'media_path' => $path,
            'status'     => 'pending',
        ]);

        foreach ($request->accounts as $id) {
            $account = SocialMediaAccount::find($id);
            if (!$account || !$account->access_token) {
                \Log::warning("⚠️ Akun tidak ditemukan atau token kosong: ID {$id}");
                continue;
            }

            $mediaFullPath = $path ? Storage::disk('public')->path($path) : null;

            // FACEBOOK
            if (strtolower($account->platform) === 'facebook') {
                if (!$account->page_id) continue;

                if ($mediaFullPath && is_readable($mediaFullPath)) {
                    $mime = mime_content_type($mediaFullPath);
                    $isVid = $mime && str_starts_with($mime, 'video/');
                    $endpoint = $isVid
                        ? "https://graph.facebook.com/{$this->graph}/{$account->page_id}/videos"
                        : "https://graph.facebook.com/{$this->graph}/{$account->page_id}/photos";

                    $fields = [
                        'access_token' => $account->access_token,
                    ] + ($isVid ? ['description' => $broadcast->caption] : ['caption' => $broadcast->caption]);

                    $response = Http::asMultipart()
                        ->attach('source', fopen($mediaFullPath, 'r'), basename($mediaFullPath))
                        ->post($endpoint, $fields);
                } else {
                    $endpoint = "https://graph.facebook.com/{$this->graph}/{$account->page_id}/feed";
                    $response = Http::post($endpoint, [
                        'message'      => $broadcast->caption,
                        'access_token' => $account->access_token,
                    ]);
                }

                if ($response->successful()) {
                    \Log::info("✅ Sukses Facebook: {$account->username}", ['response' => $response->body()]);
                    $broadcast->update(['status' => 'success']);
                } else {
                    \Log::error("❌ Gagal Facebook: {$account->username}", ['response' => $response->body()]);
                    $broadcast->update(['status' => 'failed']);
                }
            }

            // INSTAGRAM
            if (strtolower($account->platform) === 'instagram') {
                \Log::info("📸 Proses Instagram untuk: {$account->username}");

                if (!$path) {
                    \Log::warning('⚠️ IG butuh media (image/video). Text-only tidak didukung.');
                    $broadcast->update(['status' => 'failed']);
                } else {
                    $result = IG::publishFromStorage(
                        userAccessToken: $account->access_token, // user token dgn scope instagram_content_publish
                        caption:        $broadcast->caption,
                        storagePath:    $path,
                        igUserId:       $account->ig_user_id ?? null, // opsional
                        pageId:         $account->page_id            // fallback ambil dari Page
                    );

                    if (!empty($result['success'])) {
                        \Log::info("✅ Sukses Instagram: {$account->username}", ['response' => $result['response'] ?? null]);
                        $broadcast->update(['status' => 'success']);
                    } else {
                        \Log::error("❌ Gagal Instagram: {$account->username}", $result);
                        $broadcast->update(['status' => 'failed']);
                    }
                }
            }

            // TWITTER / X
            if (in_array(strtolower($account->platform), ['x','twitter'], true)) {
                \Log::info("🔁 Proses Twitter untuk: {$account->username}");

                $twitter = new TwitterOAuth(
                    config('services.twitter.consumer_key'),
                    config('services.twitter.consumer_secret'),
                    $account->access_token,
                    $account->access_token_secret
                );
                $twitter->setApiVersion('1.1');

                $check = $twitter->get("account/verify_credentials", [
                    'include_entities' => false, 'skip_status' => true
                ]);
                if ($twitter->getLastHttpCode() !== 200) {
                    \Log::error("⚠️ Token Twitter invalid: {$account->username}", [
                        'http' => $twitter->getLastHttpCode(),
                        'body' => $twitter->getLastBody(),
                    ]);
                    $broadcast->update(['status' => 'failed']);
                    continue;
                }

                if ($mediaFullPath && is_readable($mediaFullPath)) {
                    $result = TwitterHelper::postTweetWithMedia(
                        $account->access_token,
                        $account->access_token_secret,
                        $broadcast->caption,
                        $mediaFullPath
                    );
                } else {
                    $result = TwitterHelper::postTweetTextOnly(
                        $account->access_token,
                        $account->access_token_secret,
                        $broadcast->caption
                    );
                }

                if (!empty($result['success'])) {
                    \Log::info("✅ Sukses Twitter: {$account->username}", ['response' => $result['response'] ?? null]);
                    $broadcast->update(['status' => 'success']);
                } else {
                    \Log::error("❌ Gagal Twitter: {$account->username}", [
                        'error' => $result['error'] ?? 'Unknown error',
                        'details' => $result['details'] ?? null,
                    ]);
                    $broadcast->update(['status' => 'failed']);
                }
            }
        }

        return redirect()->route('broadcast.index')->with('success', 'Broadcast diproses.');
    }

    public function retry(Broadcast $broadcast)
    {
        return view('broadcast.retry', compact('broadcast'));
    }

    public function retrySend(Request $request, Broadcast $broadcast)
    {
        \Log::info('🔁 BroadcastController@retrySend dipanggil');

        $request->validate([
            'caption'  => 'required|string',
            'media'    => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:10240',
            'accounts' => 'required|array',
        ]);

        $broadcast->caption = $request->caption;
        $broadcast->status  = 'pending';

        if ($request->hasFile('media')) {
            $broadcast->media_path = $request->file('media')->store('broadcasts', 'public');
        }
        $broadcast->save();

        foreach ($request->accounts as $id) {
            $account = SocialMediaAccount::find($id);
            if (!$account || !$account->access_token) {
                \Log::warning("⚠️ Akun tidak ditemukan atau token kosong (retry): ID {$id}");
                continue;
            }

            $mediaFullPath = $broadcast->media_path ? Storage::disk('public')->path($broadcast->media_path) : null;

            // FACEBOOK (retry)
            if (strtolower($account->platform) === 'facebook') {
                if (!$account->page_id) continue;

                if ($mediaFullPath && is_readable($mediaFullPath)) {
                    $mime = mime_content_type($mediaFullPath);
                    $isVid = $mime && str_starts_with($mime, 'video/');
                    $endpoint = $isVid
                        ? "https://graph.facebook.com/{$this->graph}/{$account->page_id}/videos"
                        : "https://graph.facebook.com/{$this->graph}/{$account->page_id}/photos";

                    $fields = [
                        'access_token' => $account->access_token,
                    ] + ($isVid ? ['description' => $broadcast->caption] : ['caption' => $broadcast->caption]);

                    $response = Http::asMultipart()
                        ->attach('source', fopen($mediaFullPath, 'r'), basename($mediaFullPath))
                        ->post($endpoint, $fields);
                } else {
                    $endpoint = "https://graph.facebook.com/{$this->graph}/{$account->page_id}/feed";
                    $response = Http::post($endpoint, [
                        'message'      => $broadcast->caption,
                        'access_token' => $account->access_token,
                    ]);
                }

                if ($response->successful()) {
                    \Log::info("✅ RETRY Facebook: {$account->username}", ['response' => $response->body()]);
                    $broadcast->update(['status' => 'success']);
                } else {
                    \Log::error("❌ RETRY Gagal Facebook: {$account->username}", ['response' => $response->body()]);
                    $broadcast->update(['status' => 'failed']);
                }
            }

            // INSTAGRAM (retry)
            if (strtolower($account->platform) === 'instagram') {
                \Log::info("📸 RETRY Instagram untuk: {$account->username}");

                if (!$broadcast->media_path) {
                    \Log::warning('⚠️ IG butuh media (image/video). Text-only tidak didukung (retry).');
                    $broadcast->update(['status' => 'failed']);
                } else {
                    $result = IG::publishFromStorage(
                        userAccessToken: $account->access_token,
                        caption:        $broadcast->caption,
                        storagePath:    $broadcast->media_path,
                        igUserId:       $account->ig_user_id ?? null,
                        pageId:         $account->page_id
                    );

                    if (!empty($result['success'])) {
                        \Log::info("✅ RETRY Instagram: {$account->username}", ['response' => $result['response'] ?? null]);
                        $broadcast->update(['status' => 'success']);
                    } else {
                        \Log::error("❌ RETRY Gagal Instagram: {$account->username}", $result);
                        $broadcast->update(['status' => 'failed']);
                    }
                }
            }

            // TWITTER / X (retry)
            if (in_array(strtolower($account->platform), ['x','twitter'], true)) {
                \Log::info("🔁 RETRY Twitter untuk: {$account->username}");

                $twitter = new TwitterOAuth(
                    config('services.twitter.consumer_key'),
                    config('services.twitter.consumer_secret'),
                    $account->access_token,
                    $account->access_token_secret
                );
                $twitter->setApiVersion('1.1');

                $check = $twitter->get("account/verify_credentials", [
                    'include_entities' => false, 'skip_status' => true
                ]);
                if ($twitter->getLastHttpCode() !== 200) {
                    \Log::error("⚠️ RETRY Token Twitter invalid: {$account->username}", [
                        'http' => $twitter->getLastHttpCode(),
                        'body' => $twitter->getLastBody(),
                    ]);
                    $broadcast->update(['status' => 'failed']);
                    continue;
                }

                if ($mediaFullPath && is_readable($mediaFullPath)) {
                    $result = TwitterHelper::postTweetWithMedia(
                        $account->access_token,
                        $account->access_token_secret,
                        $broadcast->caption,
                        $mediaFullPath
                    );
                } else {
                    $result = TwitterHelper::postTweetTextOnly(
                        $account->access_token,
                        $account->access_token_secret,
                        $broadcast->caption
                    );
                }

                if (!empty($result['success'])) {
                    \Log::info("✅ RETRY Twitter: {$account->username}", ['response' => $result['response'] ?? null]);
                    $broadcast->update(['status' => 'success']);
                } else {
                    \Log::error("❌ RETRY Gagal Twitter: {$account->username}", [
                        'error' => $result['error'] ?? 'Unknown error',
                        'details' => $result['details'] ?? null,
                    ]);
                    $broadcast->update(['status' => 'failed']);
                }
            }
        }

        return redirect()->route('broadcast.index')->with('success', 'Broadcast berhasil dikirim ulang.');
    }
}
