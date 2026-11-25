<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Portfolio extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function navItems(): HasMany
    {
        return $this->hasMany(PortfolioNavItem::class)
            ->orderBy('sort_order');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(PortfolioProfile::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(PortfolioProject::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(PortfolioSkill::class);
    }
}
