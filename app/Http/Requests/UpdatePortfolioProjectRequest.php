<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'thumbnail' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'live_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:255',
            ],

            'github_url' => [
                'sometimes',
                'nullable',
                'url',
                'max:255',
            ],

            'technologies' => [
                'sometimes',
                'nullable',
                'array',
                'max:30',
            ],

            'technologies.*' => [
                'required',
                'string',
                'max:50',
                'distinct:ignore_case',
            ],

            'is_featured' => [
                'sometimes',
                'boolean',
            ],

            'is_visible' => [
                'sometimes',
                'boolean',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }
}
