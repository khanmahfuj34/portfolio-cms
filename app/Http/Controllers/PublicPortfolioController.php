<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicPortfolioController extends Controller
{
    /**
     * Reserved slugs that must not be handled by the public portfolio route.
     *
     * @var array<int, string>
     */
    private const RESERVED_SLUGS = [
        'admin',
        'api',
        'login',
        'logout',
        'dashboard',
        'profiles',
        'portfolios',
        'build',
        'assets',
        'storage',
    ];

    /**
     * Display the specified portfolio.
     */
    public function show(string $slug): View
    {
        if (in_array(strtolower($slug), self::RESERVED_SLUGS, true)) {
            abort(404);
        }

        $isSuperAdmin = Auth::check() 
            && Auth::user()->role === 'super_admin' 
            && (bool) Auth::user()->is_active;

        $query = Portfolio::query()
            ->where('slug', $slug)
            ->with([
                'profile',
                'navItems' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order', 'asc'),
                'skills'   => fn ($query) => $query->orderBy('id', 'asc'),
                'projects' => fn ($query) => $query
                    ->where('is_visible', true)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('id', 'asc'),
            ]);

        // Public visitors can only view published portfolios
        if (! $isSuperAdmin) {
            $query->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }

        $portfolio = $query->firstOrFail();

        $isPreview = $isSuperAdmin && (
            $portfolio->status !== 'published' ||
            $portfolio->published_at === null ||
            $portfolio->published_at > now()
        );

        return view('portfolio.show', [
            'portfolio' => $portfolio,
            'profile'   => $portfolio->profile,
            'navItems'  => $portfolio->navItems,
            'skills'    => $portfolio->skills,
            'projects'  => $portfolio->projects,
            'isPreview' => $isPreview,
        ]);
    }
}
