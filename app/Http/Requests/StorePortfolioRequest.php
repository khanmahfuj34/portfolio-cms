<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePortfolioRequest extends FormRequest
{
    /**
     * Authorize only active Super Admin users.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    /**
     * Prepare inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('status')) {
            $this->merge(['status' => 'draft']);
        }

        // Normalize skills if provided
        if ($this->has('skills') && is_array($this->skills)) {
            $normalizedSkills = [];
            foreach ($this->skills as $skill) {
                if (is_array($skill) && isset($skill['name'])) {
                    $name = trim((string) $skill['name']);
                    if ($name !== '') {
                        $normalizedSkills[] = ['name' => $name];
                    }
                } elseif (is_string($skill) && trim($skill) !== '') {
                    $normalizedSkills[] = ['name' => trim($skill)];
                }
            }
            $this->merge(['skills' => $normalizedSkills]);
        }

        // Normalize projects if provided
        if ($this->has('projects') && is_array($this->projects)) {
            $normalizedProjects = [];
            foreach ($this->projects as $project) {
                if (is_array($project)) {
                    $techs = $project['technologies'] ?? [];
                    if (is_string($techs)) {
                        $techs = array_values(array_filter(array_map('trim', explode(',', $techs))));
                    } elseif (is_array($techs)) {
                        $techs = array_values(array_filter(array_map(fn($t) => trim((string)$t), $techs)));
                    }
                    $project['technologies'] = $techs;
                    $project['is_featured'] = !empty($project['is_featured']) && $project['is_featured'] !== '0';
                    $project['is_visible'] = isset($project['is_visible']) ? (!empty($project['is_visible']) && $project['is_visible'] !== '0') : true;
                    $project['sort_order'] = isset($project['sort_order']) ? (int) $project['sort_order'] : 0;
                    $normalizedProjects[] = $project;
                }
            }
            $this->merge(['projects' => $normalizedProjects]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Portfolio Information
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
                Rule::unique('portfolios', 'slug'),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['draft', 'published']),
            ],

            // Owner Profile Information
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

            // Navbar Configuration
            'nav_items' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'nav_items.*.label' => [
                'required',
                'string',
                'max:80',
            ],

            'nav_items.*.type' => [
                'required',
                'string',
                Rule::in(['section', 'url']),
            ],

            'nav_items.*.section_key' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z][a-z0-9_-]*$/',
            ],

            'nav_items.*.url' => [
                'nullable',
                'string',
                'url:http,https',
                'max:255',
            ],

            'nav_items.*.sort_order' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],

            'nav_items.*.is_visible' => [
                'required',
                'boolean',
            ],

            // Skills Configuration
            'skills' => [
                'nullable',
                'array',
                'max:50',
            ],
            'skills.*.name' => [
                'required',
                'string',
                'max:100',
                'distinct:ignore_case',
            ],

            // Projects Configuration
            'projects' => [
                'nullable',
                'array',
                'max:50',
            ],
            'projects.*.title' => [
                'required',
                'string',
                'max:150',
            ],
            'projects.*.description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'projects.*.thumbnail' => [
                'nullable',
                'string',
                'max:255',
            ],
            'projects.*.live_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'projects.*.github_url' => [
                'nullable',
                'url',
                'max:255',
            ],
            'projects.*.technologies' => [
                'nullable',
                'array',
                'max:30',
            ],
            'projects.*.technologies.*' => [
                'required',
                'string',
                'max:50',
                'distinct:ignore_case',
            ],
            'projects.*.is_featured' => [
                'sometimes',
                'boolean',
            ],
            'projects.*.is_visible' => [
                'sometimes',
                'boolean',
            ],
            'projects.*.sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Cross-field validation after primary rules pass.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rawItems = $this->input('nav_items');

            if (! is_array($rawItems)) {
                return;
            }

            $items = array_values($rawItems);
            $totalItems = count($items);

            if ($totalItems === 0 || $totalItems > 10) {
                return;
            }

            $homeCount = 0;
            $sortOrders = [];

            foreach ($rawItems as $key => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $type = $item['type'] ?? null;
                $hasSection = isset($item['section_key']) && filled($item['section_key']);
                $hasUrl = isset($item['url']) && filled($item['url']);
                $label = trim((string) ($item['label'] ?? ''));
                $sortOrder = $item['sort_order'] ?? null;
                $sectionKey = trim((string) ($item['section_key'] ?? ''));

                // 1. Destination checks per type
                if ($type === 'section') {
                    if (! $hasSection) {
                        $validator->errors()->add(
                            "nav_items.{$key}.section_key",
                            'The section key field is required when destination type is internal section.'
                        );
                    }
                    if ($hasUrl) {
                        $validator->errors()->add(
                            "nav_items.{$key}.url",
                            'External URL must be empty when destination type is internal section.'
                        );
                    }
                } elseif ($type === 'url') {
                    if (! $hasUrl) {
                        $validator->errors()->add(
                            "nav_items.{$key}.url",
                            'The external URL field is required when destination type is external web URL.'
                        );
                    }
                    if ($hasSection) {
                        $validator->errors()->add(
                            "nav_items.{$key}.section_key",
                            'Section key must be empty when destination type is external web URL.'
                        );
                    }
                }

                // Both destinations supplied or neither supplied
                if ($hasSection && $hasUrl) {
                    $validator->errors()->add(
                        "nav_items.{$key}",
                        'Provide exactly one destination: section key or external URL.'
                    );
                } elseif (! $hasSection && ! $hasUrl && in_array($type, ['section', 'url'], true)) {
                    $validator->errors()->add(
                        "nav_items.{$key}",
                        'Each navbar item must provide a destination.'
                    );
                }

                // Collect sort order
                if (is_numeric($sortOrder)) {
                    $sortOrders[] = (int) $sortOrder;
                }

                // 2. Home Item Integrity
                $isHomeLabel = strcasecmp($label, 'Home') === 0;
                $isHomeSection = $sectionKey === 'home';

                if ($isHomeLabel || $isHomeSection) {
                    $homeCount++;

                    if (! $isHomeLabel) {
                        $validator->errors()->add(
                            "nav_items.{$key}.label",
                            "The Home navigation item must have label 'Home'."
                        );
                    }

                    if ($type !== 'section' || ! $isHomeSection) {
                        $validator->errors()->add(
                            "nav_items.{$key}.section_key",
                            "The Home item must use internal section destination with section key 'home'."
                        );
                    }

                    if ($hasUrl) {
                        $validator->errors()->add(
                            "nav_items.{$key}.url",
                            'The Home item cannot use an external URL.'
                        );
                    }

                    if ((int) $sortOrder !== 1) {
                        $validator->errors()->add(
                            "nav_items.{$key}.sort_order",
                            'The Home item must have sort order 1.'
                        );
                    }
                }
            }

            // Home item existence check
            if ($homeCount === 0) {
                $validator->errors()->add(
                    'nav_items',
                    'A default Home navbar item is required.'
                );
            } elseif ($homeCount > 1) {
                $validator->errors()->add(
                    'nav_items',
                    'Only one Home navbar item is allowed.'
                );
            }

            // 3. Sort Order Integrity: unique and consecutive 1 to N
            if (count($sortOrders) === $totalItems) {
                if (count(array_unique($sortOrders)) !== $totalItems) {
                    $validator->errors()->add(
                        'nav_items',
                        'Navbar item sort orders must be unique.'
                    );
                } else {
                    sort($sortOrders);
                    $expectedSequence = range(1, $totalItems);
                    if ($sortOrders !== $expectedSequence) {
                        $validator->errors()->add(
                            'nav_items',
                            "Navbar item sort orders must form a consecutive sequence from 1 to {$totalItems}."
                        );
                    }
                }
            }
        });
    }

    /**
     * Get validated data with normalized navbar items.
     * Removes the UI-only 'type' field and normalizes non-consecutive keys.
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        if ($key === null && is_array($validated) && isset($validated['nav_items']) && is_array($validated['nav_items'])) {
            $validated['nav_items'] = array_map(function (array $item) {
                return [
                    'label' => trim((string) ($item['label'] ?? '')),
                    'section_key' => filled($item['section_key'] ?? null) ? trim((string) $item['section_key']) : null,
                    'url' => filled($item['url'] ?? null) ? trim((string) $item['url']) : null,
                    'sort_order' => (int) ($item['sort_order'] ?? 1),
                    'is_visible' => filter_var($item['is_visible'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            }, array_values($validated['nav_items']));
        }

        return $validated;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Portfolio name is required.',
            'slug.required' => 'Portfolio slug is required.',
            'slug.alpha_dash' => 'Slug can only contain letters, numbers, dashes and underscores.',
            'slug.unique' => 'This portfolio slug is already in use.',
            'status.required' => 'Portfolio status is required.',
            'status.in' => 'Status must be either draft or published.',
            'full_name.required' => 'Owner full name is required.',
            'email.email' => 'Please provide a valid email address.',

            // Navbar items
            'nav_items.required' => 'Navbar configuration is required.',
            'nav_items.array' => 'Navbar items must be provided as an array.',
            'nav_items.min' => 'At least one navbar item is required.',
            'nav_items.max' => 'Maximum of 10 navbar items allowed.',
            'nav_items.*.label.required' => 'Each navbar item requires a label.',
            'nav_items.*.label.max' => 'Navbar label cannot exceed 80 characters.',
            'nav_items.*.type.required' => 'Each navbar item requires a destination type.',
            'nav_items.*.type.in' => 'Destination type must be either internal section or external web URL.',
            'nav_items.*.section_key.regex' => 'Section key must start with a lowercase letter and contain only lowercase alphanumeric characters, dashes, or underscores.',
            'nav_items.*.section_key.max' => 'Section key cannot exceed 80 characters.',
            'nav_items.*.url.url' => 'External URL must be a valid HTTP or HTTPS address.',
            'nav_items.*.url.max' => 'External URL cannot exceed 255 characters.',
            'nav_items.*.sort_order.required' => 'Sort order is required for each navbar item.',
            'nav_items.*.sort_order.integer' => 'Sort order must be an integer.',
            'nav_items.*.sort_order.min' => 'Sort order must be at least 1.',
            'nav_items.*.sort_order.max' => 'Sort order cannot exceed 10.',
            'nav_items.*.is_visible.required' => 'Visibility state is required for each navbar item.',
            'nav_items.*.is_visible.boolean' => 'Visibility state must be true or false.',
        ];
    }
}
