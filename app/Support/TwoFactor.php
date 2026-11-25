<?php

namespace App\Support;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactor
{
    public function __construct(private Google2FA $google2fa) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $url = $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);

        return (new Writer(new ImageRenderer(new RendererStyle(200, 1), new SvgImageBackEnd)))->writeString($url);
    }

    /**
     * Verify a code for a secret that is not yet confirmed (during setup).
     */
    public function verifySecret(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $this->normalize($code), 1);
    }

    /**
     * Verify a code for a user's confirmed secret, rejecting any code that was already used.
     */
    public function verifyForUser(User $user, string $code): bool
    {
        $timestamp = $this->google2fa->verifyKeyNewer(
            (string) $user->two_factor_secret,
            $this->normalize($code),
            $user->two_factor_last_timestamp,
            1,
        );

        if ($timestamp === false) {
            return false;
        }

        $user->forceFill(['two_factor_last_timestamp' => $timestamp === true ? $this->google2fa->getTimestamp() : $timestamp])->save();

        return true;
    }

    /**
     * Generate plain recovery codes and store only their hashes on the user.
     *
     * @return list<string>
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = collect(range(1, 8))->map(fn (): string => Str::upper(Str::random(5).'-'.Str::random(5)))->all();

        $user->forceFill([
            'two_factor_recovery_codes' => array_map(fn (string $code): string => Hash::make($code), $codes),
        ])->save();

        return $codes;
    }

    /**
     * Consume a recovery code: each code works exactly once.
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $code = Str::upper(trim($code));
        $hashes = $user->two_factor_recovery_codes ?? [];

        foreach ($hashes as $index => $hash) {
            if (Hash::check($code, $hash)) {
                unset($hashes[$index]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($hashes)])->save();

                return true;
            }
        }

        return false;
    }

    private function normalize(string $code): string
    {
        return preg_replace('/\D/', '', $code) ?? '';
    }
}
