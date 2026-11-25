<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProject extends Model
{
    protected $fillable = [
        'portfolio_id',
        'title',
        'description',
        'thumbnail',
        'live_url',
        'github_url',
        'technologies',
        'is_featured',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'is_featured' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }


    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
