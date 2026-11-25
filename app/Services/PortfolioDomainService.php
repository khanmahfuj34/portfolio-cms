<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\PortfolioDomain;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class PortfolioDomainService
{
    private const LABEL_REGEX = '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/';
    private const HOSTNAME_REGEX = '/^(?=.{1,255}$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*$/';

    /**
     * Normalize and validate a subdomain label from a slug or raw input.
     *
     * @throws InvalidArgumentException
     */
    public function normalizeSubdomainLabel(string $raw): string
    {
        $label = strtolower(trim($raw));
        $label = preg_replace('/[^a-z0-9-]+/', '-', $label) ?? '';
        $label = preg_replace('/-+/', '-', $label) ?? '';
        $label = trim($label, '-');

        if ($label === '' || strlen($label) > 63 || ! preg_match(self::LABEL_REGEX, $label)) {
            throw new InvalidArgumentException("Invalid subdomain label derived from '{$raw}'.");
        }

        if ($this->isReservedSubdomain($label)) {
            throw new InvalidArgumentException("Subdomain '{$label}' is reserved and cannot be assigned.");
        }

        return $label;
    }

    /**
     * Determine whether a subdomain label is platform-reserved.
     */
    public function isReservedSubdomain(string $label): bool
    {
        $reserved = array_map(
            static fn (mixed $item): string => strtolower(trim((string) $item)),
            (array) config('portfolio.reserved_subdomains', [])
        );

        return in_array(strtolower(trim($label)), $reserved, true);
    }

    /**
     * Build the full platform subdomain hostname from a slug.
     *
     * @throws InvalidArgumentException
     */
    public function buildSubdomainHostname(string $slug): string
    {
        $label = $this->normalizeSubdomainLabel($slug);
        $baseDomain = $this->baseDomain();
        $hostname = "{$label}.{$baseDomain}";

        if (strlen($hostname) > 255 || ! preg_match(self::HOSTNAME_REGEX, $hostname)) {
            throw new InvalidArgumentException("Generated subdomain hostname '{$hostname}' is invalid or too long.");
        }

        return $hostname;
    }

    /**
     * Normalize and validate a full domain/hostname string.
     *
     * @throws InvalidArgumentException
     */
    public function normalizeHostname(string $rawHost): string
    {
        $host = strtolower(trim($rawHost));
        // Strip optional port suffix (e.g., mahfuj.localhost:8000 -> mahfuj.localhost)
        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }
        $host = rtrim($host, '.');

        if ($host === '' || strlen($host) > 255 || ! preg_match(self::HOSTNAME_REGEX, $host)) {
            throw new InvalidArgumentException("Invalid hostname '{$rawHost}'.");
        }

        return $host;
    }

    /**
     * Automatically create the default verified primary subdomain for a newly created Portfolio.
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function createDefaultSubdomain(Portfolio $portfolio, ?string $preferredSlug = null): PortfolioDomain
    {
        $hostname = $this->buildSubdomainHostname($preferredSlug ?? $portfolio->slug);

        return $this->attachDomain(
            portfolio: $portfolio,
            hostname: $hostname,
            type: PortfolioDomain::TYPE_SUBDOMAIN,
            isPrimary: true,
            isVerified: true
        );
    }

    /**
     * Synchronize the default subdomain hostname when a portfolio's slug is updated.
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function syncDefaultSubdomain(Portfolio $portfolio, string $newSlug): PortfolioDomain
    {
        $hostname = $this->buildSubdomainHostname($newSlug);

        try {
            return DB::transaction(function () use ($portfolio, $hostname): PortfolioDomain {
                Portfolio::whereKey($portfolio->id)->lockForUpdate()->firstOrFail();

                $existingForHost = PortfolioDomain::where('domain', $hostname)->first();
                if ($existingForHost && (int) $existingForHost->portfolio_id !== (int) $portfolio->id) {
                    throw new InvalidArgumentException("Domain '{$hostname}' is already registered.");
                }

                $currentSubdomain = PortfolioDomain::where('portfolio_id', $portfolio->id)
                    ->where('type', PortfolioDomain::TYPE_SUBDOMAIN)
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->first();

                if ($currentSubdomain) {
                    if ($currentSubdomain->domain !== $hostname) {
                        $currentSubdomain->update([
                            'domain' => $hostname,
                            'is_verified' => true,
                            'verified_at' => $currentSubdomain->verified_at ?? now(),
                        ]);
                    }

                    return $currentSubdomain->refresh();
                }

                $hasPrimary = PortfolioDomain::where('portfolio_id', $portfolio->id)
                    ->where('is_primary', true)
                    ->exists();

                return $portfolio->domains()->create([
                    'domain' => $hostname,
                    'type' => PortfolioDomain::TYPE_SUBDOMAIN,
                    'is_primary' => ! $hasPrimary,
                    'is_verified' => true,
                    'verified_at' => now(),
                ]);
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                throw new InvalidArgumentException("Domain '{$hostname}' is already registered.", 0, $e);
            }

            throw $e;
        }
    }

    /**
     * Attach a subdomain or custom domain to a Portfolio atomically.
     * Enforces duplicate rejection and transactional primary-domain uniqueness.
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function attachDomain(
        Portfolio $portfolio,
        string $hostname,
        string $type = PortfolioDomain::TYPE_SUBDOMAIN,
        bool $isPrimary = true,
        bool $isVerified = true
    ): PortfolioDomain {
        if (! in_array($type, [PortfolioDomain::TYPE_SUBDOMAIN, PortfolioDomain::TYPE_CUSTOM], true)) {
            throw new InvalidArgumentException("Unsupported domain type '{$type}'.");
        }

        $normalizedHost = $this->normalizeHostname($hostname);
        $baseDomain = $this->baseDomain();

        if ($normalizedHost === $baseDomain) {
            throw new InvalidArgumentException("Cannot assign base domain '{$baseDomain}' directly to a portfolio.");
        }

        if ($type === PortfolioDomain::TYPE_SUBDOMAIN) {
            $suffix = ".{$baseDomain}";
            if (! str_ends_with($normalizedHost, $suffix)) {
                throw new InvalidArgumentException("Subdomain '{$normalizedHost}' must belong to base domain '{$baseDomain}'.");
            }
            $label = substr($normalizedHost, 0, -strlen($suffix));
            $this->normalizeSubdomainLabel($label);
        }

        try {
            return DB::transaction(function () use ($portfolio, $normalizedHost, $type, $isPrimary, $isVerified): PortfolioDomain {
                // Lock the owning portfolio row to serialize concurrent domain updates per portfolio.
                Portfolio::whereKey($portfolio->id)->lockForUpdate()->firstOrFail();

                if (PortfolioDomain::where('domain', $normalizedHost)->exists()) {
                    throw new InvalidArgumentException("Domain '{$normalizedHost}' is already registered.");
                }

                if ($isPrimary) {
                    PortfolioDomain::where('portfolio_id', $portfolio->id)
                        ->where('is_primary', true)
                        ->update(['is_primary' => false]);
                }

                return $portfolio->domains()->create([
                    'domain' => $normalizedHost,
                    'type' => $type,
                    'is_primary' => $isPrimary,
                    'is_verified' => $isVerified,
                    'verified_at' => $isVerified ? now() : null,
                ]);
            });
        } catch (QueryException $e) {
            // Final database unique constraint protection against race conditions.
            if ($this->isUniqueConstraintViolation($e)) {
                throw new InvalidArgumentException("Domain '{$normalizedHost}' is already registered.", 0, $e);
            }

            throw $e;
        }
    }

    /**
     * Promote an existing domain record to be the portfolio's sole primary domain.
     *
     * @throws Throwable
     */
    public function setPrimaryDomain(PortfolioDomain $domain): PortfolioDomain
    {
        return DB::transaction(function () use ($domain): PortfolioDomain {
            Portfolio::whereKey($domain->portfolio_id)->lockForUpdate()->firstOrFail();

            PortfolioDomain::where('portfolio_id', $domain->portfolio_id)
                ->where('id', '!=', $domain->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            $domain->update(['is_primary' => true]);

            return $domain->refresh();
        });
    }

    /**
     * Resolve a verified PortfolioDomain record from an incoming HTTP host.
     */
    public function resolveVerifiedDomain(string $rawHost): ?PortfolioDomain
    {
        try {
            $normalizedHost = $this->normalizeHostname($rawHost);
        } catch (InvalidArgumentException) {
            return null;
        }

        $baseDomain = $this->baseDomain();
        if ($normalizedHost === $baseDomain) {
            return null;
        }

        $suffix = ".{$baseDomain}";
        if (str_ends_with($normalizedHost, $suffix)) {
            $label = substr($normalizedHost, 0, -strlen($suffix));
            if ($label === '' || str_contains($label, '.') || $this->isReservedSubdomain($label)) {
                return null;
            }
        }

        return PortfolioDomain::query()
            ->where('domain', $normalizedHost)
            ->where('is_verified', true)
            ->first();
    }

    /**
     * Format the public HTTP URL for a PortfolioDomain, preserving non-standard local ports.
     */
    public function publicUrlFor(PortfolioDomain $domain, ?Request $request = null): string
    {
        $scheme = $request?->getScheme()
            ?? (parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http');

        $port = $request?->getPort()
            ?? parse_url((string) config('app.url'), PHP_URL_PORT);

        $portSuffix = '';
        if ($port && ! (($scheme === 'http' && (int) $port === 80) || ($scheme === 'https' && (int) $port === 443))) {
            $portSuffix = ':' . $port;
        }

        return "{$scheme}://{$domain->domain}{$portSuffix}";
    }

    /**
     * Determine whether an incoming request host is a supported base host
     * (configured base_domain or local loopback aliases during local/testing).
     */
    public function isBaseHost(string $rawHost): bool
    {
        $host = strtolower(trim($rawHost));
        if (str_starts_with($host, '[')) {
            $host = trim(explode(']', $host, 2)[0], '[');
        } elseif (substr_count($host, ':') === 1) {
            $host = explode(':', $host, 2)[0];
        }
        $host = rtrim($host, '.');

        $baseDomain = $this->baseDomain();
        if ($host === $baseDomain) {
            return true;
        }

        if (in_array($baseDomain, ['localhost', '127.0.0.1'], true) && app()->environment(['local', 'testing'])) {
            return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        }

        return false;
    }

    /**
     * Format a full URL for a path on the base domain, preserving local host/port.
     */
    public function baseUrlFor(string $path = '/', ?Request $request = null): string
    {
        $normalizedPath = '/' . ltrim($path, '/');

        if ($request && $this->isBaseHost($request->getHost())) {
            $port = $request->getPort();
            $scheme = $request->getScheme();
            $portSuffix = ($port && ! (($scheme === 'http' && (int) $port === 80) || ($scheme === 'https' && (int) $port === 443)))
                ? ':' . $port
                : '';

            return "{$scheme}://{$request->getHost()}{$portSuffix}{$normalizedPath}";
        }

        $scheme = $request?->getScheme()
            ?? (parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'http');

        $port = $request?->getPort()
            ?? parse_url((string) config('app.url'), PHP_URL_PORT);

        $portSuffix = ($port && ! (($scheme === 'http' && (int) $port === 80) || ($scheme === 'https' && (int) $port === 443)))
            ? ':' . $port
            : '';

        return "{$scheme}://{$this->baseDomain()}{$portSuffix}{$normalizedPath}";
    }

    /**
     * Get the normalized configured base domain.
     */
    public function baseDomain(): string
    {
        return strtolower(trim((string) config('portfolio.base_domain', 'localhost')));
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;

        return $sqlState === '23000' || $sqlState === '23505' || $driverCode === 1062;
    }
}
