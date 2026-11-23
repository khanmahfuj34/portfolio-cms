<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
                'regex:/^[a-z][a-z0-9_-]*$/',
            ],

            'url' => [
                'sometimes',
                'nullable',
                'string',
                'url:http,https',
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {

            $sectionProvided = $this->exists('section_key');
            $urlProvided = $this->exists('url');

            // Destination is optional during a partial update.
            if (! $sectionProvided && ! $urlProvided) {
                return;
            }

            $hasSection = filled($this->input('section_key'));
            $hasUrl = filled($this->input('url'));

            if ($hasSection === $hasUrl) {
                $validator->errors()->add(
                    'section_key',
                    'Provide exactly one destination: section_key or url.'
                );
            }
        });
    }
}
