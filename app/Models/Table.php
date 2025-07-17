<?php

namespace App\Models;

use Milon\Barcode\DNS2D;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'qr_code',
        'number',
        'location',
        'capacity',
        'status',
    ];

    protected $appends = [
        'qr_code_image',
        'is_available'
    ];

    public function getQrCodeImageAttribute()
    {
        $path = 'qrcodes/' . $this->qr_code . '.png';
        return base64_encode(Storage::disk('public')->get($path));
    }



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            if (empty($table->qr_code)) {
                // Generate random qr_code
                $date = now()->format('Ym');
                $random = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
                $table->qr_code = "TBL{$date}{$random}";

                // Generate QR code image
                $qrCode = (new DNS2D())->getBarcodePNG(config('app.url'). '?code=' .base64_encode($table->qr_code), 'QRCODE', 5, 5);

                // Ensure directory exists
                $directory = 'qrcodes';
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                // Save QR code image
                $path = $directory . '/' . $table->qr_code . '.png';
                Storage::disk('public')->put($path, base64_decode($qrCode));
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Transaction::class, 'table_id', 'id');
    }

    public function getIsAvailableAttribute()
    {
        return $this->orders()?->where('is_payment', false)->exists() ? 'unavailable' : 'available';
    }
}
