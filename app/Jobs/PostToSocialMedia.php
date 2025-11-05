<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PostToSocialMedia implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $account, $message, $mediaPath;

    public function __construct(SocialMediaAccount $account, $message, $mediaPath)
    {
        $this->account = $account;
        $this->message = $message;
        $this->mediaPath = $mediaPath;
    }

    public function handle()
    {
        try {
            $url = 'https://example.com/post/' . uniqid();

            PostLog::create([
                'account_id' => $this->account->id,
                'platform' => $this->account->platform,
                'username' => $this->account->username,
                'message' => $this->message,
                'status' => 'success',
                'post_url' => $url,
            ]);
        } catch (\Exception $e) {
            PostLog::create([
                'account_id' => $this->account->id,
                'platform' => $this->account->platform,
                'username' => $this->account->username,
                'message' => $this->message,
                'status' => 'failed',
                'post_url' => null,
            ]);
        }
    }
}
