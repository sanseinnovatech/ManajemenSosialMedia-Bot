<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class InstagramHelper
{
    public const GRAPH = 'v23.0';

    /**
     * Buat URL publik untuk file di disk 'public'.
     * Prioritas sumber base URL:
     * 1) env MEDIA_BASE_URL (mis. https://cdn.domainmu.com)
     * 2) filesystems.disks.public.url (mis. https://domainmu.com/storage)
     * 3) app.asset_url atau app.url + '/storage'
     * Lalu force HTTPS dan tolak host localhost/127.0.0.1.
     */
    public static function makePublicUrl(?string $storagePath): ?string
    {
        if (!$storagePath) return null;

        $relative = ltrim($storagePath, '/');

        // 1) MEDIA_BASE_URL (tanpa /storage di belakang; kita yang tambahkan)
        $mediaBase = rtrim(config('services.media_base', ''), '/');
        if ($mediaBase !== '') {
            $url = $mediaBase . '/storage/' . $relative;
            return self::sanitizeAndValidate($url);
        }

        // 2) filesystems.disks.public.url (biasanya sudah mengandung /storage)
        $diskUrl = rtrim(config('filesystems.disks.public.url', ''), '/');
        if ($diskUrl !== '') {
            // Jika sudah mengandung '/storage', jangan gandakan.
            if (str_contains($diskUrl, '/storage')) {
                $url = $diskUrl . '/' . $relative;
            } else {
                $url = $diskUrl . '/storage/' . $relative;
            }
            return self::sanitizeAndValidate($url);
        }

        // 3) asset/app.url
        $base = rtrim(config('app.asset_url', config('app.url')), '/');
        $url  = $base . '/storage/' . $relative;

        return self::sanitizeAndValidate($url);
    }

    /** Paksa HTTPS dan tolak host lokal, kembalikan null jika tidak valid. */
    private static function sanitizeAndValidate(string $url): ?string
    {
        // Force https
        if (str_starts_with($url, 'http://')) {
            $url = 'https://' . ltrim(substr($url, 7), '/');
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $isLocal =
            $host === 'localhost' ||
            $host === '127.0.0.1' ||
            preg_match('/\.local(?:host)?$/i', $host) ||
            preg_match('/\.lan$/i', $host);

        if ($isLocal || !str_starts_with($url, 'https://')) {
            logger()->warning('⚠️ IG media URL harus publik & HTTPS', ['url' => $url, 'host' => $host]);
            return null;
        }
        return $url;
    }

    /** Deteksi apakah file video dari path lokal. */
    public static function isVideoFromLocalPath(?string $localPath): bool
    {
        if (!$localPath || !is_readable($localPath)) return false;
        $mime = mime_content_type($localPath) ?: '';
        return str_starts_with($mime, 'video/');
    }

    /** Ambil IG User ID dari Page ID (butuh user access token yang punya scope IG). */
    public static function getIgUserIdFromPage(string $pageId, string $userAccessToken): ?string
    {
        $res = Http::get("https://graph.facebook.com/".self::GRAPH."/$pageId", [
            'fields'       => 'instagram_business_account',
            'access_token' => $userAccessToken,
        ]);

        if (!$res->successful()) {
            logger()->error('❌ Gagal ambil IG User ID dari Page', [
                'http' => $res->status(),
                'body' => $res->body(),
            ]);
            return null;
        }
        return data_get($res->json(), 'instagram_business_account.id');
    }

    /** Buat container IG dari URL (image_url / video_url). */
    public static function createContainer(string $igUserId, string $userAccessToken, string $caption, string $mediaUrl, bool $isVideo): array
    {
        $endpoint = "https://graph.facebook.com/".self::GRAPH."/$igUserId/media";
        $payload  = [
            'caption'      => $caption,
            'access_token' => $userAccessToken,
        ] + ($isVideo ? ['video_url' => $mediaUrl] : ['image_url' => $mediaUrl]);

        $res = Http::asForm()->post($endpoint, $payload);
        if (!$res->successful()) {
            logger()->error('❌ IG create container failed', ['http' => $res->status(), 'body' => $res->body()]);
        }
        return $res->json() ?? [];
    }

    /** Poll status container sampai FINISHED. */
    public static function waitContainerFinished(string $creationId, string $userAccessToken, int $tries = 15, int $sleepMs = 800): bool
    {
        for ($i = 0; $i < $tries; $i++) {
            usleep($sleepMs * 1000);
            $st = Http::get("https://graph.facebook.com/".self::GRAPH."/$creationId", [
                'fields'       => 'status_code',
                'access_token' => $userAccessToken,
            ]);
            if (!$st->successful()) {
                logger()->error('❌ IG check status failed', ['http' => $st->status(), 'body' => $st->body()]);
                return false;
            }
            $code = $st->json('status_code');
            if ($code === 'FINISHED') return true;
            if (in_array($code, ['ERROR','EXPIRED'], true)) return false;
        }
        return false;
    }

    /** Publish media dari creation_id. */
    public static function publish(string $igUserId, string $creationId, string $userAccessToken): array
    {
        $res = Http::asForm()->post("https://graph.facebook.com/".self::GRAPH."/$igUserId/media_publish", [
            'creation_id'  => $creationId,
            'access_token' => $userAccessToken,
        ]);
        if (!$res->successful()) {
            logger()->error('❌ IG publish failed', ['http' => $res->status(), 'body' => $res->body()]);
        }
        return $res->json() ?? [];
    }

    /**
     * Alur lengkap publish (image/video) dari storage path.
     */
    public static function publishFromStorage(
        string $userAccessToken,
        string $caption,
        string $storagePath,
        ?string $igUserId = null,
        ?string $pageId   = null
    ): array {
        $mediaUrl = self::makePublicUrl($storagePath);
        if (!$mediaUrl) { // sudah dilog di makePublicUrl
            return ['success' => false, 'error' => 'Media URL tidak publik/HTTPS — set MEDIA_BASE_URL atau disks.public.url'];
        }

        // Cari IG user id bila belum disediakan
        if (!$igUserId && $pageId) {
            $igUserId = self::getIgUserIdFromPage($pageId, $userAccessToken);
        }
        if (!$igUserId) {
            return ['success' => false, 'error' => 'IG User ID tidak ditemukan (pastikan IG Business terhubung ke Page & token punya scope IG)'];
        }

        $localPath = Storage::disk('public')->path($storagePath);
        $isVideo   = self::isVideoFromLocalPath($localPath);

        $container = self::createContainer($igUserId, $userAccessToken, $caption, $mediaUrl, $isVideo);
        $creationId = $container['id'] ?? null;
        if (!$creationId) {
            return ['success' => false, 'error' => 'Container gagal dibuat', 'response' => $container];
        }

        $ok = self::waitContainerFinished($creationId, $userAccessToken);
        if (!$ok) {
            return ['success' => false, 'error' => 'Container timeout/failed', 'creation_id' => $creationId];
        }

        $publish = self::publish($igUserId, $creationId, $userAccessToken);
        $mediaId = $publish['id'] ?? null;

        return ['success' => (bool) $mediaId, 'id' => $mediaId, 'response' => $publish];
    }
}
