<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
