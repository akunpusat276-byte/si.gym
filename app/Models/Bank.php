<?php

namespace App\Models;

use Database\Factories\BankFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    /** @use HasFactory<BankFactory> */
    use HasFactory;

    protected $fillable = [
        'bank_name', 'bank_code', 'account_number', 'account_holder', 'logo', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
