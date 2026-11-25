<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UserService
{
    public function __construct(
        private readonly PortfolioPreviewService $previewService
    ) {}

    /**
     * Register a new Individual Admin account with enforced role and active status.
     */
    public function registerIndividualAdmin(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = new User([
                'name' => trim((string) $data['name']),
                'email' => strtolower(trim((string) $data['email'])),
                'password' => (string) $data['password'],
            ]);

            // Privilege fields are always enforced server-side, never from client input.
            $user->role = User::ROLE_ADMIN;
            $user->is_active = true;
            $user->save();

            return $user;
        });
    }

    /**
     * Update an Individual Admin's own name and email without modifying role or status.
     */
    public function updateOwnProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $newEmail = strtolower(trim((string) $data['email']));

            $user->name = trim((string) $data['name']);
            if ($user->email !== $newEmail) {
                $user->email = $newEmail;
                $user->email_verified_at = null;
            }
            $user->save();

            return $user->refresh();
        });
    }

    /**
     * Securely update an Individual Admin's password and rotate session/preview tokens.
     */
    public function updateOwnPassword(User $user, string $newPassword): User
    {
        $updated = DB::transaction(function () use ($user, $newPassword): User {
            $user->password = $newPassword;
            $user->remember_token = Str::random(60);
            $user->save();

            return $user->refresh();
        });

        $this->previewService->revokeAllForUser($updated);

        return $updated;
    }

    /**
     * Toggle an Individual Admin account's active status (Super Admin action).
     */
    public function toggleIndividualAdminStatus(User $actor, User $target): User
    {
        if ((int) $actor->id === (int) $target->id) {
            throw new InvalidArgumentException('You cannot modify your own account status.');
        }

        if ($target->role !== User::ROLE_ADMIN) {
            throw new InvalidArgumentException('Only Individual Admin accounts can be modified here.');
        }

        $updated = DB::transaction(function () use ($target): User {
            $target->is_active = ! $target->is_active;

            if (! $target->is_active) {
                $target->remember_token = Str::random(60);
            }

            $target->save();

            return $target->refresh();
        });

        if (! $updated->is_active) {
            $this->previewService->revokeAllForUser($updated);
            $this->revokeUserSessions($updated);
        }

        return $updated;
    }

    /**
     * Revoke all database sessions for a user except the current active session ID.
     */
    public function revokeOtherSessions(User $user, string $keepSessionId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $table = (string) config('session.table', 'sessions');

        if (Schema::hasTable($table)) {
            DB::table($table)
                ->where('user_id', $user->id)
                ->where('id', '!=', $keepSessionId)
                ->delete();
        }
    }

    private function revokeUserSessions(User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $table = (string) config('session.table', 'sessions');

        if (Schema::hasTable($table)) {
            DB::table($table)->where('user_id', $user->id)->delete();
        }
    }
}
