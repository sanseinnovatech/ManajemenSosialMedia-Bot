<?php

namespace App\Helpers;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterHelper
{
    /**
     * Buat koneksi TwitterOAuth dengan timeout/retry & opsi cURL yang stabil di Windows/Laragon.
     */
    private static function makeConnection(string $accessToken, string $accessTokenSecret): TwitterOAuth
    {
        $ck = config('services.twitter.consumer_key');
        $cs = config('services.twitter.consumer_secret');

        $conn = new TwitterOAuth($ck, $cs, $accessToken, $accessTokenSecret);

        // Timeout dari .env (opsional). Default: 10s connect, 30s response
        $connectTimeout  = (int) env('TWITTER_CONNECT_TIMEOUT', 10);
        $responseTimeout = (int) env('TWITTER_TIMEOUT', 30);
        $conn->setTimeouts($connectTimeout, $responseTimeout);

        // Retry (beberapa versi lib butuh 2 argumen: jumlah & delay ms)
        $retries   = (int) env('TWITTER_RETRIES', 2);
        $retryWait = (int) env('TWITTER_RETRY_DELAY_MS', 500); // 500ms default
        if (method_exists($conn, 'setRetries')) {
            $ref = new \ReflectionMethod($conn, 'setRetries');
            $argc = $ref->getNumberOfParameters();
            if ($argc >= 2) {
                $conn->setRetries($retries, $retryWait);
            } else {
                $conn->setRetries($retries);
            }
        }

        // Paksa IPv4 (menghindari problem DNS/IPv6 di Windows), SSL verify on
        $curlOpts = [
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'twitteroauth (+https://github.com/abraham/twitteroauth)',
        ];

        // Jika perlu CA bundle custom (Windows): isi path di .env, mis. C:\laragon\bin\curl\cacert.pem
        if ($ca = env('TWITTER_CACERT')) {
            $curlOpts[CURLOPT_CAINFO] = $ca;
        }

        if (method_exists($conn, 'setCurlOptions')) {
            $conn->setCurlOptions($curlOpts);
        }

        return $conn;
    }

    /**
     * Ping ringan untuk memastikan konektivitas (HTTP 200/429 = ok).
     */
    private static function ping(TwitterOAuth $conn): bool
    {
        $conn->setApiVersion('1.1');
        $conn->get('application/rate_limit_status', ['resources' => 'application']);
        $code = $conn->getLastHttpCode();
        return $code === 200 || $code === 429; // 429 = tetap reachable (bukan timeout)
    }

    /**
     * Verifikasi kredensial user (HTTP 200 = valid).
     */
    private static function verifyCredentials(TwitterOAuth $conn): bool
    {
        $conn->setApiVersion('1.1');
        $conn->get('account/verify_credentials', ['include_entities' => false, 'skip_status' => true]);
        return $conn->getLastHttpCode() === 200;
    }

    /**
     * Tunggu proses media video selesai (v1.1). Return true jika 'succeeded'.
     */
    private static function waitMediaProcessing(TwitterOAuth $conn, string $mediaId, int $maxTries = 30, int $sleepMs = 800): bool
    {
        $conn->setApiVersion('1.1');
        for ($i = 0; $i < $maxTries; $i++) {
            $status = $conn->upload('media/upload', ['command' => 'STATUS', 'media_id' => $mediaId]);
            $code   = $conn->getLastHttpCode();
            if ($code !== 200) {
                \Log::debug('TW media STATUS http!=200', ['http' => $code, 'body' => $conn->getLastBody()]);
                return false;
            }

            $info  = $status->processing_info ?? null;
            $state = $info->state ?? null;
            if ($state === 'succeeded') {
                return true;
            }
            if ($state === 'failed') {
                \Log::error('TW media processing failed', ['info' => $status]);
                return false;
            }

            $waitSec = isset($info->check_after_secs) ? (int) $info->check_after_secs : 0;
            if ($waitSec > 0) {
                sleep($waitSec);
            } else {
                usleep($sleepMs * 1000);
            }
        }
        \Log::warning('TW media processing timeout');
        return false;
    }

    /**
     * Post tweet TEKS SAJA via API v2.
     */
    public static function postTweetTextOnly(string $accessToken, string $accessTokenSecret, string $text): array
    {
        try {
            $conn = self::makeConnection($accessToken, $accessTokenSecret);

            // Ping dulu untuk cegah "Operation timed out"
            if (!self::ping($conn)) {
                return ['success' => false, 'error' => 'Network/SSL timeout when pinging Twitter (check DNS/IPv4/CA bundle)'];
            }

            // Verifikasi kredensial
            if (!self::verifyCredentials($conn)) {
                return ['success' => false, 'error' => 'Invalid credentials', 'details' => $conn->getLastBody(), 'http' => $conn->getLastHttpCode()];
            }

            // Buat tweet v2 (HARUS pakai options array: ['json'=>true])
            $conn->setApiVersion('2');
            $resp = $conn->post('tweets', ['text' => $text], ['json' => true]);
            $code = $conn->getLastHttpCode();

            if (in_array($code, [200, 201], true)) {
                return ['success' => true, 'response' => $resp];
            }

            // Fallback v1.1 (opsional; perlu tier yang izinkan statuses/update)
            if (env('TWITTER_ENABLE_V11_FALLBACK', false)) {
                $conn->setApiVersion('1.1');
                $fb = $conn->post('statuses/update', ['status' => $text]);
                if ($conn->getLastHttpCode() === 200) {
                    return ['success' => true, 'response' => $fb];
                }
            }

            return ['success' => false, 'error' => 'v2 tweet failed', 'details' => $conn->getLastBody(), 'http' => $code];
        } catch (\Throwable $e) {
            \Log::error('💥 Twitter v2 text-only exception', ['msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Upload media (v1.1) lalu buat tweet (v2) dengan media_ids.
     * Mendukung image/jpeg, image/png, video/mp4 (video kecil; tanpa chunked INIT/APPEND).
     */
    public static function postTweetWithMedia(string $accessToken, string $accessTokenSecret, string $caption, string $mediaPath): array
    {
        try {
            $fullPath = realpath($mediaPath);
            if (!$fullPath || !file_exists($fullPath) || !is_readable($fullPath)) {
                return ['success' => false, 'error' => "File not found or unreadable: {$mediaPath}"];
            }

            $mime = mime_content_type($fullPath);
            $allowed = ['image/jpeg','image/png','video/mp4'];
            if (!in_array($mime, $allowed, true)) {
                return ['success' => false, 'error' => "Unsupported MIME type: {$mime}"];
            }

            $size = filesize($fullPath);
            if ($mime === 'video/mp4' && $size > 5 * 1024 * 1024) {
                \Log::warning('Video >5MB terdeteksi. Upload non-chunked bisa gagal. Pertimbangkan implementasi chunked upload.');
            }

            $conn = self::makeConnection($accessToken, $accessTokenSecret);

            // Ping
            if (!self::ping($conn)) {
                return ['success' => false, 'error' => 'Network/SSL timeout when pinging Twitter (check DNS/IPv4/CA bundle)'];
            }

            // Verifikasi kredensial v1.1
            if (!self::verifyCredentials($conn)) {
                return ['success' => false, 'error' => 'Invalid credentials', 'details' => $conn->getLastBody(), 'http' => $conn->getLastHttpCode()];
            }

            // Upload media v1.1
            $conn->setApiVersion('1.1');
            $media = $conn->upload('media/upload', ['media' => $fullPath]);
            $code  = $conn->getLastHttpCode();
            if ($code !== 200 || empty($media->media_id_string)) {
                return ['success' => false, 'error' => 'media/upload failed', 'details' => $conn->getLastBody(), 'http' => $code];
            }
            $mediaId = $media->media_id_string;

            // Jika video, tunggu processing selesai
            if ($mime === 'video/mp4') {
                if (!self::waitMediaProcessing($conn, $mediaId)) {
                    return ['success' => false, 'error' => 'Video processing failed/timeout'];
                }
            }

            // Buat tweet v2 dengan media (pakai options array untuk JSON)
            $conn->setApiVersion('2');
            $payload = [
                'text'  => $caption,
                'media' => ['media_ids' => [$mediaId]],
            ];
            $resp = $conn->post('tweets', $payload, ['json' => true]);
            $code = $conn->getLastHttpCode();

            if (in_array($code, [200, 201], true)) {
                return ['success' => true, 'response' => $resp];
            }

            // Fallback opsional v1.1 (butuh tier yg izinkan statuses/update)
            if (env('TWITTER_ENABLE_V11_FALLBACK', false)) {
                $conn->setApiVersion('1.1');
                $fallback = $conn->post('statuses/update', ['status' => $caption, 'media_ids' => $mediaId]);
                if ($conn->getLastHttpCode() === 200) {
                    return ['success' => true, 'response' => $fallback];
                }
            }

            return ['success' => false, 'error' => 'Twitter post failed', 'details' => $conn->getLastBody(), 'http' => $code];
        } catch (\Throwable $e) {
            \Log::error('💥 Twitter v2+v1.1 hybrid exception', ['msg' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
