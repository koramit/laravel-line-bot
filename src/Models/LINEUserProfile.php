<?php

namespace Koramit\LaravelLINEBot\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class LINEUserProfile extends Model
{
    protected $table = 'line_user_profiles';

    protected function casts(): array
    {
        return [
            'profile' => AsEncryptedArrayObject::class,
            'verified_at' => 'datetime',
            'unfollowed_at' => 'datetime',
        ];
    }

    public function scopeFromPendingVerifyCode(Builder $query, string $verifyCode): void
    {
        $query->whereNull('verified_at')
            ->where('verify_code', $verifyCode);
    }

    public function updateProfile(): void
    {
        $response = Http::withToken(config('line.bot_channel_access_token'))
            ->get(config('line.bot_get_user_profile_endpoint').$this->line_user_id);

        $profile = $response->json();

        $this->profile = [
            'display_name' => $profile['displayName'],
            'language' => $profile['language'] ?? null,
            'picture_url' => $profile['pictureUrl'] ?? null,
            'status_message' => $profile['statusMessage'] ?? null,
        ];

        $this->save();
    }
}
