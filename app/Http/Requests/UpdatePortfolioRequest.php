<?php

namespace App\Http\Requests;

use App\Models\Portfolio;
use App\Models\PortfolioDomain;
use App\Services\PortfolioDomainService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

class UpdatePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');

        return $portfolio instanceof Portfolio
            && $this->user()?->can('update', $portfolio) === true;
    }

    public function rules(): array
    {
        $portfolio = $this->route('portfolio');

        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'slug' => [
                'required',
                'string',
                'max:63',
                'alpha_dash',
                Rule::unique('portfolios', 'slug')->ignore($portfolio?->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],
            'professional_title' => [
                'nullable',
                'string',
                'max:150',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'location' => [
                'nullable',
                'string',
                'max:150',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $portfolio = $this->route('portfolio');
            $rawSlug = $this->input('slug');

            if ($portfolio instanceof Portfolio && is_string($rawSlug) && trim($rawSlug) !== '' && ! $validator->errors()->has('slug')) {
                $domainService = app(PortfolioDomainService::class);
                try {
                    $hostname = $domainService->buildSubdomainHostname($rawSlug);
                    $existsElsewhere = PortfolioDomain::where('domain', $hostname)
                        ->where('portfolio_id', '!=', $portfolio->id)
                        ->exists();

                    if ($existsElsewhere) {
                        $validator->errors()->add('slug', "The subdomain '{$hostname}' is already registered.");
                    }
                } catch (InvalidArgumentException $e) {
                    $validator->errors()->add('slug', $e->getMessage());
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Portfolio name is required.',
            'slug.required' => 'Portfolio slug is required.',
            'slug.alpha_dash' => 'Slug can only contain letters, numbers, dashes and underscores.',
            'slug.unique' => 'This portfolio slug is already in use.',
            'full_name.required' => 'Owner full name is required.',
            'email.email' => 'Please provide a valid email address.',
        ];
    }
}
