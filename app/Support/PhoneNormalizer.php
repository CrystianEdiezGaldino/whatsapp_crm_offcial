<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function digits(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }

    /** Variantes BR (com/sem 9 após DDD) para o mesmo celular. */
    public static function variants(string $phone): array
    {
        $digits = self::digits($phone);
        $out = [$digits];

        if (!str_starts_with($digits, '55') || strlen($digits) < 12) {
            return array_values(array_unique($out));
        }

        $national = substr($digits, 2);
        $ddd = substr($national, 0, 2);
        $subscriber = substr($national, 2);

        if (strlen($subscriber) === 9 && $subscriber[0] === '9') {
            $out[] = '55' . $ddd . substr($subscriber, 1);
        } elseif (strlen($subscriber) === 8) {
            $out[] = '55' . $ddd . '9' . $subscriber;
        }

        return array_values(array_unique($out));
    }

    /** Formato que a Meta/WhatsApp costuma usar no webhook (12 dígitos BR). */
    public static function forApi(string $phone): string
    {
        foreach (self::variants($phone) as $variant) {
            if (str_starts_with($variant, '55') && strlen($variant) === 12) {
                return $variant;
            }
        }

        return self::digits($phone);
    }
}
