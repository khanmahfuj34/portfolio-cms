<?php

namespace App\Http\Requests\SuperAdmin;

use App\Models\Theme;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateThemeRequest extends StoreThemeRequest
{
    protected function uniqueThemeRule(string $column): Unique
    {
        $theme = $this->route('theme');
        $themeId = $theme instanceof Theme ? $theme->getKey() : $theme;

        return Rule::unique('themes', $column)->ignore($themeId);
    }
}
