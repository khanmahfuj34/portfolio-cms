<?php

namespace App\Http\Requests\Admin;

use App\Models\Portfolio;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ApplyThemeRequest extends FormRequest
{
    /**
     * Authorize active Individual Admins via the 'admin' guard and ThemePolicy.
     */
    public function authorize(): bool
    {
        $user = $this->user('admin');

        if (! $user instanceof User || ! $user->isAdmin()) {
            return false;
        }

        $portfolio = $this->route('portfolio');
        $theme = $this->route('theme');

        if ($portfolio instanceof Portfolio && $theme instanceof Theme) {
            return Gate::forUser($user)->allows('apply', [$theme, $portfolio]);
        }

        return true;
    }

    /**
     * Prevent tampering with ownership or lifecycle fields via request payload.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['prohibited'],
            'theme_id' => ['prohibited'],
            'status' => ['prohibited'],
            'published_at' => ['prohibited'],
            'created_by' => ['prohibited'],
        ];
    }
}
