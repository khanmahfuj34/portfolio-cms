<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Display paginated Individual Admin accounts with search and status filters.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $baseQuery = User::query()->where('role', User::ROLE_ADMIN);

        $usersQuery = (clone $baseQuery)->withCount('portfolios');

        if ($search !== '') {
            $usersQuery->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status && in_array($status, ['active', 'inactive'], true)) {
            $usersQuery->where('is_active', $status === 'active');
        }

        $users = $usersQuery
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive' => (clone $baseQuery)->where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'search', 'status'));
    }

    /**
     * Activate or deactivate an Individual Admin account without deleting data.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if (! $actor instanceof User || ! $actor->isSuperAdmin()) {
            abort(403);
        }

        if ((int) $actor->id === (int) $user->id || $user->role !== User::ROLE_ADMIN) {
            abort(403, 'Super Admin and non-admin accounts cannot be modified through this interface.');
        }

        try {
            $updated = $this->userService->toggleIndividualAdminStatus($actor, $user);
        } catch (InvalidArgumentException $exception) {
            abort(403, $exception->getMessage());
        }

        $message = $updated->is_active
            ? "Activated {$updated->name}'s Individual Admin account."
            : "Deactivated {$updated->name}'s Individual Admin account.";

        return redirect()
            ->route('admin.users.index', array_filter([
                'search' => $request->query('search'),
                'status' => $request->query('status'),
                'page' => $request->query('page'),
            ]))
            ->with('success', $message);
    }
}
