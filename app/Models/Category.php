<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeSelectableForListings(Builder $query): Builder
    {
        return $query->where(function (Builder $categories): void {
            $categories->where(function (Builder $roots): void {
                $roots->whereNull('parent_id')
                    ->whereNotIn('slug', ['products', 'services']);
            })->orWhereHas('parent', function (Builder $parent): void {
                $parent->whereNull('parent_id')
                    ->whereIn('slug', ['products', 'services']);
            });
        });
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
