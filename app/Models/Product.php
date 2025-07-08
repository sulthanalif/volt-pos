<?php

namespace App\Models;

use Milon\Barcode\DNS1D;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'barcode',
        'name',
        'description',
        'price_buy',
        'price_sell',
        'stock',
        'category_id',
        'unit_id',
        'supplier_id',
        'status',
        'image',
    ];

    protected $appends = [
        'barcode_image',
    ];

    public function getBarcodeImageAttribute()
    {
        return base64_encode(Storage::disk('public')->get('barcodes/' . $this->barcode . '.png'));
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->barcode)) {
                // Generate random barcode with format: PRDYYYYMMXXXXX
                $date = now()->format('Ym');
                $random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
                $product->barcode = "PRD{$date}{$random}";

                // Generate Barcode image
                $barcode = (new DNS1D())->getBarcodePNG($product->barcode, 'C39+');
                
                // Ensure directory exists
                $directory = 'barcodes';
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                // Save QR code image
                $path = $directory . '/' . $product->barcode . '.png';
                Storage::disk('public')->put($path, base64_decode($barcode));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
