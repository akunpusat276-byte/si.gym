<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'user_id', 'registration_id', 'package_id',
        'amount', 'payment_date', 'method', 'status', 'notes',
        'bank_id', 'bank_name', 'bank_account_number', 'bank_account_holder',
        'sender_name', 'transfer_date', 'payment_proof', 'verified_by',
        'verified_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'transfer_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function membership()
    {
        return $this->hasOne(Membership::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
