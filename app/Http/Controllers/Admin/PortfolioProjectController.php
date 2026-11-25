<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioProjectRequest;
use App\Http\Requests\UpdatePortfolioProjectRequest;
use App\Models\Portfolio;
use App\Models\PortfolioProject;
use App\Services\PortfolioProjectService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PortfolioProjectController extends Controller
{
    public function __construct(
        private readonly PortfolioProjectService $projectService
    ) {}

    /**
     * Display all projects of a portfolio.
     */
    public function index(Request $request, Portfolio $portfolio): View|JsonResponse
    {
        Gate::authorize('view', $portfolio);

        $projects = $this->projectService->getAll($portfolio);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $projects,
            ]);
        }

        return view('admin.portfolios.projects.index', compact(
            'portfolio',
            'projects'
        ));
    }

    /**
     * Store a new project.
     */
    public function store(
        StorePortfolioProjectRequest $request,
        Portfolio $portfolio
    ): RedirectResponse|JsonResponse {
        $project = $this->projectService->create(
            $portfolio,
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project created successfully.',
                'data' => $project,
            ], 201);
        }

        return redirect()
            ->back()
            ->with('success', 'Project created successfully.');
    }

    /**
     * Update an existing project.
     */
    public function update(
        UpdatePortfolioProjectRequest $request,
        Portfolio $portfolio,
        PortfolioProject $project
    ): RedirectResponse|JsonResponse {
        $updatedProject = $this->projectService->update(
            $portfolio,
            $project,
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully.',
                'data' => $updatedProject,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Delete a project.
     */
    public function destroy(
        Request $request,
        Portfolio $portfolio,
        PortfolioProject $project
    ): RedirectResponse|JsonResponse {
        Gate::authorize('update', $portfolio);

        $this->projectService->delete($portfolio, $project);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully.',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Project deleted successfully.');
    }
}
