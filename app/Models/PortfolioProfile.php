<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProfile extends Model
{
    protected $fillable = [
        'portfolio_id',
        'full_name',
        'professional_title',
        'email',
        'location',
        'bio',
        'profile_photo_path',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}