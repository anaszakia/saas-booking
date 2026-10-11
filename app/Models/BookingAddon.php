<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingAddon extends Model
{
    use SoftDeletes, HasAuditColumns, HasUlid;

    protected $fillable = [
        'booking_id',
        'ulid',
        'addon_id',
        'addon_name',
        'price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
