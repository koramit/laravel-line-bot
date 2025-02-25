<?php

namespace Koramit\LaravelLINEBot;

use Illuminate\Support\Carbon;
use Koramit\LaravelLINEBot\Exceptions\InvalidLINEBotVerifyCode;
use Koramit\LaravelLINEBot\Models\LINEUserProfile;

class VerifyUser
{
    public function __invoke(string $verifyCode, int $userId): LINEUserProfile
    {
        if (! $profile = LINEUserProfile::query()
            ->fromPendingVerifyCode($verifyCode)
            ->first()) {
            throw new InvalidLINEBotVerifyCode('invalid verify code');
        }

        foreach (LINEUserProfile::query()
            ->whereUserId($userId)
            ->where('id', '!=', $profile->id)
            ->get() as $anotherProfile) {
            $anotherProfile->verified_at = null;
            $anotherProfile->verify_code = null;
            $anotherProfile->save();
        }

        $profile->verified_at = Carbon::now();
        $profile->user_id = $userId;
        $profile->save();

        return $profile;
    }
}
