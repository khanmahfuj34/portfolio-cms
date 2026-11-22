<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PortfolioService
{
    /**
     * Create a portfolio with its default navigation.
     */
    public function create(User $admin, array $data): Portfolio
    {
        return DB::transaction(function () use ($admin, $data) {

            // Create portfolio
            $portfolio = $admin->portfolios()->create($data);

            // Create default Home navigation item
            $portfolio->navItems()->create([
                'label' => 'Home',
                'section_key' => 'home',
                'url' => null,
                'sort_order' => 1,
                'is_visible' => true,
            ]);

            return $portfolio;
        });
    }
}
