<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'booking_id',
        'payment_id',
        'refund_number',
        'amount',
        'reason',
        'method',
        'status',
        'external_id',
        'destination_account',
        'proof_file',
        'approved_by',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'destination_account' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getProofFileUrlAttribute(): string
    {
        return minio_url($this->proof_file);
    }
}
