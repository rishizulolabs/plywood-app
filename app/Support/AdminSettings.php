<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AdminSettings
{
    private const CACHE_KEY = 'admin.platform_settings';

    public static function defaults(): array
    {
        return [
            'support_email' => config('mail.from.address', 'support@plywood.com'),
            'support_phone' => '',
            'require_distributor_approval' => true,
        ];
    }

    public static function all(): array
    {
        return array_merge(self::defaults(), Cache::get(self::CACHE_KEY, []));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function put(array $data): void
    {
        Cache::forever(self::CACHE_KEY, array_merge(self::all(), $data));
    }
}
