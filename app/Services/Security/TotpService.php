<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    /** @return array<int, string> */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        while (count($codes) < $count) {
            $plain = '';

            for ($index = 0; $index < 12; $index++) {
                $plain .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $codes[] = implode('-', str_split($plain, 4));
        }

        return $codes;
    }

    /** @param array<int, string> $codes
     * @return array<int, string>
     */
    public function hashRecoveryCodes(array $codes): array
    {
        return array_map(
            fn (string $code): string => Hash::make($this->normalizeRecoveryCode($code)),
            $codes,
        );
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $normalized = $this->normalizeRecoveryCode($code);
        $hashes = $user->two_factor_recovery_codes ?? [];

        foreach ($hashes as $index => $hash) {
            if (! Hash::check($normalized, $hash)) {
                continue;
            }

            unset($hashes[$index]);
            $user->forceFill([
                'two_factor_recovery_codes' => array_values($hashes),
            ])->save();

            return true;
        }

        return false;
    }

    public function verify(
        string $secret,
        string $code,
        ?int $timestamp = null,
        int $window = 1,
        ?int $afterStep = null,
    ): ?int {
        $normalizedCode = preg_replace('/\s+/', '', $code) ?? '';

        if (! preg_match('/^\d{6}$/', $normalizedCode)) {
            return null;
        }

        $currentStep = intdiv($timestamp ?? now()->timestamp, 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $step = $currentStep + $offset;

            if ($step < 0 || ($afterStep !== null && $step <= $afterStep)) {
                continue;
            }

            if (hash_equals($this->codeAtStep($secret, $step), $normalizedCode)) {
                return $step;
            }
        }

        return null;
    }

    public function code(string $secret, ?int $timestamp = null): string
    {
        return $this->codeAtStep($secret, intdiv($timestamp ?? now()->timestamp, 30));
    }

    public function otpauthUri(User $user): string
    {
        $issuer = (string) config('app.name', 'JAR Computers');
        $label = rawurlencode($issuer.':'.$user->email);
        $query = http_build_query([
            'secret' => $user->two_factor_secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ], '', '&', PHP_QUERY_RFC3986);

        return "otpauth://totp/{$label}?{$query}";
    }

    public function formatSecret(string $secret): string
    {
        return implode(' ', str_split($secret, 4));
    }

    private function codeAtStep(string $secret, int $step): string
    {
        $key = $this->base32Decode($secret);
        $counter = pack('N2', intdiv($step, 4294967296), $step % 4294967296);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function normalizeRecoveryCode(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/i', '', $code) ?? '');
    }

    private function base32Encode(string $bytes): string
    {
        $bits = '';

        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }

        return $encoded;
    }

    private function base32Decode(string $encoded): string
    {
        $normalized = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $encoded) ?? '');

        if ($normalized === '') {
            throw new RuntimeException('TOTP secret is empty.');
        }

        $bits = '';

        foreach (str_split($normalized) as $character) {
            $position = strpos(self::BASE32_ALPHABET, $character);

            if ($position === false) {
                throw new RuntimeException('TOTP secret contains an invalid character.');
            }

            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }
}
