<?php

namespace App\Services;

use Illuminate\Support\Str;

class PhoneValidationService
{
    /**
     * Normaliza e valida número de telefone no formato brasileiro
     *
     * Aceita:
     * - +55 11 99999-8888
     * - 55 11 99999-8888
     * - 11 99999-8888
     * - 5511999998888
     *
     * Retorna: 5511999998888 (com country code)
     */
    public static function normalize(string $phone): ?string
    {
        // Remove espaços, hífens, parênteses
        $clean = preg_replace('/[\s\-\(\)]+/', '', $phone);

        // Remove + se houver
        $clean = Str::start($clean, '');
        if (Str::startsWith($clean, '+')) {
            $clean = Str::substr($clean, 1);
        }

        // Se não tem country code, adiciona 55 (Brasil)
        if (!Str::startsWith($clean, '55')) {
            // Se começar com 0, remove
            if (Str::startsWith($clean, '0')) {
                $clean = Str::substr($clean, 1);
            }
            $clean = '55' . $clean;
        }

        return self::validate($clean) ? $clean : null;
    }

    /**
     * Valida se o número está no formato correto
     *
     * Rules:
     * - Must start with 55 (Brazil country code)
     * - Must have 10 or 11 digits after country code
     * - Total: 12 or 13 characters
     */
    public static function validate(string $phone): bool
    {
        // Deve ser apenas dígitos
        if (!preg_match('/^\d+$/', $phone)) {
            return false;
        }

        // Deve começar com 55
        if (!Str::startsWith($phone, '55')) {
            return false;
        }

        // Deve ter 12 ou 13 dígitos (55 + 10 ou 11 dígitos)
        $length = strlen($phone);
        if ($length !== 12 && $length !== 13) {
            return false;
        }

        // Remove country code para verificar DDD e número
        $numberWithoutCountry = Str::substr($phone, 2);

        // Extrai DDD (primeiros 2 dígitos)
        $areaCode = Str::substr($numberWithoutCountry, 0, 2);

        // DDD válidos brasileiros (11-99, exceto alguns números)
        $validAreaCodes = range(11, 99);
        if (!in_array((int) $areaCode, $validAreaCodes)) {
            return false;
        }

        // Primeiro dígito do número deve ser 9 (celular)
        // ou 2-5 (fixo), mas permitimos apenas celular (9) neste caso
        $firstDigit = (int) Str::substr($numberWithoutCountry, 2, 1);
        if ($firstDigit !== 9 && ($length === 13)) {
            // Se tem 13 dígitos, deve ser celular (9)
            return false;
        }

        return true;
    }

    /**
     * Formata número para exibição
     * 5511999998888 → +55 11 99999-8888
     */
    public static function format(string $phone): string
    {
        $clean = self::normalize($phone);
        if (!$clean) {
            return $phone;
        }

        // +55 11 99999-8888
        $country = Str::substr($clean, 0, 2);
        $areaCode = Str::substr($clean, 2, 2);
        $firstPart = Str::substr($clean, 4, 5);
        $secondPart = Str::substr($clean, 9);

        return "+{$country} {$areaCode} {$firstPart}-{$secondPart}";
    }
}
