<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
    ];

    public function additions(): BelongsToMany
    {
        return $this->belongsToMany(Addition::class, 'addition_category', 'category_id', 'addition_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
