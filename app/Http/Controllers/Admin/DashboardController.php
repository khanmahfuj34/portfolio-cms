<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Super Admin Dashboard and Portfolios list.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $portfoliosQuery = Portfolio::with(['profile', 'user', 'primaryDomain'])
            ->withCount('navItems');

        if ($search !== '') {
            $portfoliosQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhereHas('profile', function ($pq) use ($search) {
                      $pq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('professional_title', 'like', "%{$search}%");
                  });
            });
        }

        if ($status && in_array($status, ['published', 'draft'], true)) {
            $portfoliosQuery->where('status', $status);
        }

        $portfolios = $portfoliosQuery
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Portfolio::count(),
            'published' => Portfolio::where('status', 'published')->count(),
            'draft' => Portfolio::where('status', 'draft')->count(),
            'profiles' => PortfolioProfile::count(),
        ];

        return view('admin.dashboard', compact('portfolios', 'stats', 'search', 'status'));
    }
}
