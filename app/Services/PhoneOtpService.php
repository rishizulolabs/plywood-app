<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PhoneOtpService
{
    private const OTP_TTL_SECONDS = 600;

    public function send(string $phone, string $accountType): void
    {
        $user = $this->findUserByPhone($phone, $accountType);

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['No '.$accountType.' account found for this phone number.'],
            ]);
        }

        $normalized = normalize_phone($phone);

        if ($normalized === null) {
            throw ValidationException::withMessages([
                'phone' => ['That phone number is not valid.'],
            ]);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($this->cacheKey($normalized, $accountType), $code, self::OTP_TTL_SECONDS);

        if ($this->shouldSendSms()) {
            $this->sendSms($normalized, $code);
        } else {
            Log::info('Phone OTP (dev)', [
                'phone' => $normalized,
                'account_type' => $accountType,
                'code' => $code,
            ]);
        }
    }

    public function verify(string $phone, string $accountType, string $code): User
    {
        $normalized = normalize_phone($phone);

        if ($normalized === null) {
            throw ValidationException::withMessages([
                'phone' => ['That phone number is not valid.'],
            ]);
        }

        $cacheKey = $this->cacheKey($normalized, $accountType);
        $expected = Cache::get($cacheKey);

        if (! is_string($expected) || ! hash_equals($expected, $code)) {
            throw ValidationException::withMessages([
                'code' => ['Incorrect or expired OTP. Tap Resend OTP and try again.'],
            ]);
        }

        Cache::forget($cacheKey);

        $user = $this->findUserByPhone($phone, $accountType);

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['No '.$accountType.' account found for this phone number.'],
            ]);
        }

        return $user;
    }

    private function findUserByPhone(string $phone, string $accountType): ?User
    {
        $variants = phone_lookup_variants($phone);

        if ($variants === []) {
            return null;
        }

        return User::query()
            ->role($accountType)
            ->where(function ($query) use ($variants) {
                foreach ($variants as $variant) {
                    $query->orWhere('phone', $variant);
                }
            })
            ->first();
    }

    private function cacheKey(string $normalizedPhone, string $accountType): string
    {
        $digits = preg_replace('/\D+/', '', $normalizedPhone) ?? $normalizedPhone;

        return 'phone_otp:'.$accountType.':'.$digits;
    }

    private function shouldSendSms(): bool
    {
        return filled(config('services.msg91.key')) && filled(config('services.msg91.template_id'));
    }

    private function sendSms(string $normalizedPhone, string $code): void
    {
        $authKey = config('services.msg91.key');
        $templateId = config('services.msg91.template_id');
        $digits = preg_replace('/\D+/', '', $normalizedPhone) ?? '';

        if (str_starts_with($digits, '91') && strlen($digits) > 10) {
            $digits = substr($digits, 2);
        }

        $response = Http::timeout(20)
            ->withHeaders(['authkey' => $authKey])
            ->post('https://control.msg91.com/api/v5/otp', [
                'template_id' => $templateId,
                'mobile' => '91'.$digits,
                'otp' => $code,
            ]);

        if (! $response->successful()) {
            Log::error('MSG91 OTP send failed', [
                'phone' => $normalizedPhone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw ValidationException::withMessages([
                'phone' => ['Could not send OTP SMS. Please try again in a moment.'],
            ]);
        }
    }
}
