<?php

if (! function_exists('format_inr')) {
    function format_inr(float|int|string $amount): string
    {
        $number = (float) $amount;
        $negative = $number < 0;
        $number = abs($number);

        $parts = explode('.', number_format($number, 2, '.', ''));
        $integer = $parts[0];
        $decimal = $parts[1] ?? '00';

        $lastThree = substr($integer, -3);
        $rest = substr($integer, 0, -3);

        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $formatted = $rest.','.$lastThree;
        } else {
            $formatted = $lastThree;
        }

        $result = $formatted.'.'.$decimal;

        return ($negative ? '-' : '').'₹'.$result;
    }
}

if (! function_exists('format_inr_compact')) {
    function format_inr_compact(float|int|string $amount): string
    {
        $number = abs((float) $amount);
        $negative = (float) $amount < 0;
        $prefix = ($negative ? '-' : '').'₹';

        if ($number >= 10000000) {
            $value = $number / 10000000;

            return $prefix.rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').' Cr';
        }

        if ($number >= 100000) {
            $value = $number / 100000;

            return $prefix.rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').' L';
        }

        return format_inr($amount);
    }
}

if (! function_exists('normalize_phone')) {
    function normalize_phone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10) {
            return '+91'.$digits;
        }

        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            return '+'.$digits;
        }

        return '+'.$digits;
    }
}

if (! function_exists('phone_lookup_variants')) {
    /**
     * @return list<string>
     */
    function phone_lookup_variants(?string $phone): array
    {
        $normalized = normalize_phone($phone);

        if ($normalized === null) {
            return [];
        }

        $digits = preg_replace('/\D+/', '', $normalized) ?? '';
        $variants = [$normalized, $digits];

        if (str_starts_with($digits, '91') && strlen($digits) > 2) {
            $variants[] = substr($digits, 2);
        }

        return array_values(array_unique(array_filter($variants)));
    }
}
