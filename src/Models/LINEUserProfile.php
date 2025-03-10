<?php

namespace Koramit\LaravelLINEBot\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Random\RandomException;

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

    protected function connected(): Attribute
    {
        return Attribute::make(get: fn ($value) => $this->verified_at && $this->user_id);
    }

    public function scopeFromPendingVerifyCode(Builder $query, string $verifyCode): void
    {
        $query->whereNull('verified_at')
            ->where('verify_code', $verifyCode);
    }

    public function genVerifyCode(): void
    {
        $codeLength = (int) config('line.bot_verify_code_length');

        do {
            try {
                $randomCode = random_int(0, pow(10, $codeLength) - 1);
            } catch (RandomException $exception) {
                Log::error($exception->getMessage());
                exit(1);
            }
            $verifyCode = Str::padLeft($randomCode, $codeLength, '0');
            if (static::query()->fromPendingVerifyCode($verifyCode)->exists()) {
                $verifyCode = null;
            }
        } while ($verifyCode === null);

        $this->verify_code = $verifyCode;
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

    public function unfollow(): void
    {
        $this->unfollowed_at = Carbon::now();
        $this->save();
    }
}
