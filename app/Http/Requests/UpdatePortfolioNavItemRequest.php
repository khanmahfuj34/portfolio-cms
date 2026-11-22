<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioNavItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'label' => [
                'sometimes',
                'required',
                'string',
                'max:80',
            ],

            'section_key' => [
                'sometimes',
                'nullable',
                'string',
                'max:80',
            ],

            'url' => [
                'sometimes',
                'nullable',
                'url',
                'max:255',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'is_visible' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
