<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioSkillRequest;
use App\Http\Requests\UpdatePortfolioSkillRequest;
use App\Models\Portfolio;
use App\Models\PortfolioSkill;
use App\Services\PortfolioSkillService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortfolioSkillController extends Controller
{
    public function __construct(
        private readonly PortfolioSkillService $skillService
    ) {}

    /**
     * Display all skills of a portfolio.
     */
    public function index(Request $request, Portfolio $portfolio): View|JsonResponse
    {
        $skills = $this->skillService->getAll($portfolio);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $skills,
            ]);
        }

        return view('admin.portfolios.skills.index', compact(
            'portfolio',
            'skills'
        ));
    }

    /**
     * Store a new skill.
     */
    public function store(
        StorePortfolioSkillRequest $request,
        Portfolio $portfolio
    ): RedirectResponse|JsonResponse {
        $skill = $this->skillService->create(
            $portfolio,
            $request->validated('name')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Skill created successfully.',
                'data' => $skill,
            ], 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Skill created successfully.');
    }

    /**
     * Update an existing skill.
     */
    public function update(
        UpdatePortfolioSkillRequest $request,
        Portfolio $portfolio,
        PortfolioSkill $skill
    ): RedirectResponse|JsonResponse {
        $updatedSkill = $this->skillService->update(
            $portfolio,
            $skill,
            $request->validated('name')
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Skill updated successfully.',
                'data' => $updatedSkill,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Skill updated successfully.');
    }

    /**
     * Delete a skill.
     */
    public function destroy(
        Request $request,
        Portfolio $portfolio,
        PortfolioSkill $skill
    ): RedirectResponse|JsonResponse {
        $this->skillService->delete($portfolio, $skill);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Skill deleted successfully.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Skill deleted successfully.');
    }
}
