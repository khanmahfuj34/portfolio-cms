<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill default local subdomain rows for pre-existing portfolios.
     */
    public function up(): void
    {
        $baseDomain = strtolower((string) config('portfolio.base_domain', 'localhost'));
        $reserved = config('portfolio.reserved_subdomains', []);
        $now = now();

        $portfolios = DB::table('portfolios')
            ->select(['id', 'slug'])
            ->orderBy('id')
            ->get();

        DB::transaction(function () use ($portfolios, $baseDomain, $reserved, $now) {
            foreach ($portfolios as $portfolio) {
                $alreadyHasDomain = DB::table('portfolio_domains')
                    ->where('portfolio_id', $portfolio->id)
                    ->exists();

                if ($alreadyHasDomain) {
                    continue;
                }

                $label = strtolower(trim((string) $portfolio->slug));
                $label = preg_replace('/[^a-z0-9-]+/', '-', $label) ?? '';
                $label = preg_replace('/-+/', '-', $label) ?? '';
                $label = trim($label, '-');

                if (
                    $label === '' ||
                    strlen($label) > 63 ||
                    in_array($label, $reserved, true) ||
                    ! preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $label)
                ) {
                    continue;
                }

                $domain = "{$label}.{$baseDomain}";
                if (strlen($domain) > 255) {
                    continue;
                }

                $domainExists = DB::table('portfolio_domains')
                    ->where('domain', $domain)
                    ->exists();

                if ($domainExists) {
                    continue;
                }

                DB::table('portfolio_domains')->insert([
                    'portfolio_id' => $portfolio->id,
                    'domain' => $domain,
                    'type' => 'subdomain',
                    'is_primary' => true,
                    'is_verified' => true,
                    'verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $baseDomain = strtolower((string) config('portfolio.base_domain', 'localhost'));

        DB::table('portfolio_domains')
            ->where('type', 'subdomain')
            ->where('domain', 'like', "%.{$baseDomain}")
            ->delete();
    }
};
