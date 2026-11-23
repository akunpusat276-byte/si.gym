<?php

namespace App\Models;

use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    protected $fillable = [
        'member_id', 'package_id', 'payment_id', 'start_date', 'end_date', 'duration', 'status',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function getCalculatedStatusAttribute(): string
    {
        if (! $this->start_date || ! $this->end_date) {
            return 'Menunggu Pembayaran';
        }
        if ($this->end_date->lt(today())) {
            return 'Kedaluwarsa';
        }
        if ($this->end_date->lte(today()->addDays(7))) {
            return 'Akan Berakhir';
        }

        return 'Aktif';
    }

    public function getRemainingDaysAttribute(): int
    {
        return $this->end_date ? max(0, today()->diffInDays($this->end_date, false)) : 0;
    }
}
