<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'required',
                'string',
                'max:150',
                'alpha_dash',
                Rule::unique('portfolios', 'slug')
                    ->where('user_id', $this->user()->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Portfolio name is required.',
            'slug.required' => 'Portfolio slug is required.',
            'slug.alpha_dash' => 'Slug can only contain letters, numbers, dashes and underscores.',
            'slug.unique' => 'You already have a portfolio with this slug.',
        ];
    }
}
