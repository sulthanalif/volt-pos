<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionItem extends Model
{
    protected $table = 'addition_items';

    protected $fillable = [
        'addition_id',
        'name',
        'price',
    ];

    public function addition(): BelongsTo
    {
        return $this->belongsTo(Addition::class, 'addition_id', 'id');
    }
}
