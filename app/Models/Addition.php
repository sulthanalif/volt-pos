<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Addition extends Model
{
    protected $table = 'additions';

    protected $fillable = [
        'label',
        'is_required',
        'is_multiple',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'addition_category', 'addition_id',  'category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AdditionItem::class, 'addition_id', 'id');
    }
}
