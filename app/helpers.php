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
