<?php

namespace Koramit\LaravelLINEBot\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Random\RandomException;

/**
 * @property int $id
 * @property string|null $verify_code
 * @property Carbon|null $verified_at
 * @property string $line_user_id
 * @property AsEncryptedArrayObject|null $profile
 * @property int|null $user_id
 * @property Carbon|null $unfollowed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $connected
 * @property-read string|null $display_name
 *
 * @method static Builder<static>|LINEUserProfile fromPendingVerifyCode(string $verifyCode)
 * @method static Builder<static>|LINEUserProfile newModelQuery()
 * @method static Builder<static>|LINEUserProfile newQuery()
 * @method static Builder<static>|LINEUserProfile query()
 * @method static Builder<static>|LINEUserProfile whereCreatedAt($value)
 * @method static Builder<static>|LINEUserProfile whereId($value)
 * @method static Builder<static>|LINEUserProfile whereLineUserId($value)
 * @method static Builder<static>|LINEUserProfile whereProfile($value)
 * @method static Builder<static>|LINEUserProfile whereUnfollowedAt($value)
 * @method static Builder<static>|LINEUserProfile whereUpdatedAt($value)
 * @method static Builder<static>|LINEUserProfile whereUserId($value)
 * @method static Builder<static>|LINEUserProfile whereVerifiedAt($value)
 * @method static Builder<static>|LINEUserProfile whereVerifyCode($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Koramit\LaravelLINEBot\Models\LINEBotChatLog> $chatLogs
 * @property-read int|null $chat_logs_count
 *
 * @mixin \Eloquent
 */
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

    public function chatLogs(): HasMany
    {
        return $this->hasMany(LINEBotChatLog::class, 'line_user_profile_id', 'id');
    }

    protected function connected(): Attribute
    {
        return Attribute::make(get: fn () => $this->verified_at && $this->user_id);
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(get: fn () => $this->profile['display_name'] ?? null);
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
        if ($this->unfollowed_at) {
            return;
        }

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
        $this->touch('unfollowed_at');
    }
}
