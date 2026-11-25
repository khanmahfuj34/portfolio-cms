<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PortfolioPreviewService
{
    private const DEFAULT_TTL_MINUTES = 15;

    public function __construct(
        private readonly PortfolioDomainService $domainService
    ) {}

    /**
     * Generate a short-lived HMAC-signed preview token bound to a specific portfolio and issuer.
     */
    public function generateToken(Portfolio $portfolio, User $issuer, int $ttlMinutes = self::DEFAULT_TTL_MINUTES): string
    {
        $now = now()->timestamp;
        $nowUs = (int) round(microtime(true) * 1000000);
        $payload = [
            'pid' => (int) $portfolio->id,
            'uid' => (int) $issuer->id,
            'iat' => $now,
            'iat_us' => $nowUs,
            'exp' => $now + ($ttlMinutes * 60),
            'jti' => Str::random(16),
        ];

        $encodedPayload = $this->base64UrlEncode((string) json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->sign($encodedPayload);

        return "{$encodedPayload}.{$signature}";
    }

    /**
     * Build the full signed preview URL for a portfolio's primary domain (or legacy fallback).
     */
    public function generatePreviewUrl(
        Portfolio $portfolio,
        User $issuer,
        ?Request $request = null,
        int $ttlMinutes = self::DEFAULT_TTL_MINUTES
    ): string {
        $token = $this->generateToken($portfolio, $issuer, $ttlMinutes);
        $portfolio->loadMissing('primaryDomain');

        if ($portfolio->primaryDomain) {
            $baseUrl = $this->domainService->publicUrlFor($portfolio->primaryDomain, $request);

            return $baseUrl . '/?preview_token=' . urlencode($token);
        }

        return url('/' . $portfolio->slug) . '?preview_token=' . urlencode($token);
    }

    /**
     * Verify whether a signed preview token is valid, unexpired, unrevoked, and bound to an authorized active user.
     */
    public function validateToken(string $token, Portfolio $portfolio): bool
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$encodedPayload, $providedSignature] = $parts;
        if ($encodedPayload === '' || $providedSignature === '') {
            return false;
        }

        $expectedSignature = $this->sign($encodedPayload);
        if (! hash_equals($expectedSignature, $providedSignature)) {
            return false;
        }

        $decodedJson = $this->base64UrlDecode($encodedPayload);
        if ($decodedJson === null) {
            return false;
        }

        $payload = json_decode($decodedJson, true);
        if (! is_array($payload)) {
            return false;
        }

        $pid = $payload['pid'] ?? null;
        $uid = $payload['uid'] ?? null;
        $iat = $payload['iat'] ?? null;
        $iatUs = $payload['iat_us'] ?? (is_int($iat) ? $iat * 1000000 : null);
        $exp = $payload['exp'] ?? null;
        $jti = $payload['jti'] ?? null;

        if (! is_int($pid) || ! is_int($uid) || ! is_int($iat) || ! is_int($iatUs) || ! is_int($exp) || ! is_string($jti) || $jti === '') {
            return false;
        }

        // Strict portfolio binding
        if ($pid !== (int) $portfolio->id) {
            return false;
        }

        // Expiration check
        if ($exp < now()->timestamp) {
            return false;
        }

        // Revocation checks (explicit token or portfolio/user microsecond revocation epoch)
        if (Cache::has($this->revokedTokenKey($jti))) {
            return false;
        }

        $portfolioRevokedBefore = Cache::get($this->portfolioRevocationEpochKey((int) $portfolio->id));
        if (is_int($portfolioRevokedBefore) && $iatUs <= $portfolioRevokedBefore) {
            return false;
        }

        $userRevokedBefore = Cache::get($this->userRevocationEpochKey($uid));
        if (is_int($userRevokedBefore) && $iatUs <= $userRevokedBefore) {
            return false;
        }

        // Issuer must still exist, be active, and be either Super Admin or owning Individual Admin
        $issuer = User::find($uid);
        if (! $issuer instanceof User || ! $issuer->isActive()) {
            return false;
        }

        if ($issuer->isSuperAdmin()) {
            return true;
        }

        return $issuer->isAdmin() && (int) $portfolio->user_id === (int) $issuer->id;
    }

    /**
     * Revoke all previously issued preview tokens for a given portfolio.
     */
    public function revokeAllForPortfolio(Portfolio $portfolio): void
    {
        Cache::put(
            $this->portfolioRevocationEpochKey((int) $portfolio->id),
            (int) round(microtime(true) * 1000000),
            now()->addDays(7)
        );
    }

    /**
     * Revoke all previously issued preview tokens created by a specific user.
     */
    public function revokeAllForUser(User $user): void
    {
        Cache::put(
            $this->userRevocationEpochKey((int) $user->id),
            (int) round(microtime(true) * 1000000),
            now()->addDays(7)
        );
    }

    private function sign(string $encodedPayload): string
    {
        $key = (string) config('app.key', '');

        return hash_hmac('sha256', $encodedPayload, $key);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): ?string
    {
        $remainder = strlen($data) % 4;
        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }

    private function revokedTokenKey(string $jti): string
    {
        return "portfolio_preview:revoked_jti:{$jti}";
    }

    private function portfolioRevocationEpochKey(int $portfolioId): string
    {
        return "portfolio_preview:revoked_portfolio_before:{$portfolioId}";
    }

    private function userRevocationEpochKey(int $userId): string
    {
        return "portfolio_preview:revoked_user_before:{$userId}";
    }
}
