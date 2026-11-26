<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Theme;
use Illuminate\Support\Facades\View;

class PortfolioThemeResolver
{
    public const DEFAULT_KEY = 'default';
    public const DEFAULT_VIEW = 'portfolio.show';
    public const RIFAH_MODERN_KEY = 'rifah-modern';
    public const RIFAH_MODERN_VIEW = 'portfolio-themes.rifah-modern.index';

    /**
     * Fixed allowlist mapping approved theme_key values to static Blade views.
     * Database values are never used to construct arbitrary view names.
     *
     * @var array<string, string>
     */
    private const THEME_REGISTRY = [
        self::DEFAULT_KEY => self::DEFAULT_VIEW,
        self::RIFAH_MODERN_KEY => self::RIFAH_MODERN_VIEW,
    ];

    /**
     * @var array<string, string>
     */
    private array $registry;

    /**
     * @param  array<string, string>|null  $registryOverride
     */
    public function __construct(?array $registryOverride = null)
    {
        $this->registry = $registryOverride ?? self::THEME_REGISTRY;
    }

    /**
     * Resolve the trusted Blade view name for a portfolio without gating on lifecycle status.
     */
    public function resolveView(Portfolio $portfolio): string
    {
        $theme = $portfolio->theme;

        if (! $theme instanceof Theme) {
            return self::DEFAULT_VIEW;
        }

        $key = strtolower(trim((string) $theme->theme_key));
        if ($key === '' || ! array_key_exists($key, $this->registry)) {
            return self::DEFAULT_VIEW;
        }

        $view = $this->registry[$key];

        return View::exists($view) ? $view : self::DEFAULT_VIEW;
    }

    /**
     * Resolve the effective trusted theme key used for rendering a portfolio.
     */
    public function resolveKey(Portfolio $portfolio): string
    {
        $theme = $portfolio->theme;

        if (! $theme instanceof Theme) {
            return self::DEFAULT_KEY;
        }

        $key = strtolower(trim((string) $theme->theme_key));
        if ($key === '' || ! array_key_exists($key, $this->registry)) {
            return self::DEFAULT_KEY;
        }

        return View::exists($this->registry[$key]) ? $key : self::DEFAULT_KEY;
    }

    /**
     * Check whether a theme key exists in the trusted static registry.
     */
    public function isRegisteredKey(string $themeKey): bool
    {
        $normalized = strtolower(trim($themeKey));

        return $normalized !== '' && array_key_exists($normalized, $this->registry);
    }
}
