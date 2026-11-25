<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');

        return $portfolio !== null && $this->user()?->can('update', $portfolio) === true;
    }

    public function rules(): array
    {
        $portfolio = $this->route('portfolio');
        $skill = $this->route('skill');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('portfolio_skills', 'name')
                    ->where('portfolio_id', $portfolio->id)
                    ->ignore($skill->id),
            ],
        ];
    }
}
