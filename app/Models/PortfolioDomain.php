<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioDomain extends Model
{
    public const TYPE_SUBDOMAIN = 'subdomain';
    public const TYPE_CUSTOM = 'custom';

    protected $fillable = [
        'portfolio_id',
        'domain',
        'type',
        'is_primary',
        'is_verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
