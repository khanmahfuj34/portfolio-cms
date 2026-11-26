<?php

namespace App\Http\Requests\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreThemeRequest extends FormRequest
{
    /**
     * Allow only active Super Admins authenticated on the super_admin guard.
     */
    public function authorize(): bool
    {
        $user = $this->user('super_admin');

        return $user instanceof User && $user->isSuperAdmin();
    }

    protected function prepareForValidation(): void
    {
        $trimmed = [];

        foreach (['name', 'slug', 'theme_key', 'category', 'thumbnail', 'version'] as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $trimmed[$field] = trim($this->input($field));
            }
        }

        if ($trimmed !== []) {
            $this->merge($trimmed);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:180', $this->uniqueThemeRule('slug')],
            'theme_key' => ['required', 'string', 'max:100', $this->uniqueThemeRule('theme_key')],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:20'],
            'is_featured' => ['sometimes', 'boolean'],

            // Protected system/lifecycle attributes cannot be set through metadata input.
            'id' => ['prohibited'],
            'created_by' => ['prohibited'],
            'status' => ['prohibited'],
            'published_at' => ['prohibited'],
        ];
    }

    protected function uniqueThemeRule(string $column): Unique
    {
        return Rule::unique('themes', $column);
    }
}
