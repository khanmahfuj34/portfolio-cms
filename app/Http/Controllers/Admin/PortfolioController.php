<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioRequest;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;

class PortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioService $portfolioService
    ) {}

    /**
     * Store a new portfolio.
     */
    public function store(
        StorePortfolioRequest $request
    ): RedirectResponse {

        $this->portfolioService->create(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Portfolio created successfully.');
    }
}
