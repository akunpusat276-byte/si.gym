<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'member_code', 'phone', 'gender', 'birth_date', 'address', 'status'];

    protected static function booted(): void
    {
        static::saving(function (Member $member): void {
            if (! Schema::hasTable('members') || ! Schema::hasColumn('members', 'name')) {
                return;
            }

            $user = $member->user()->first();
            $member->setAttribute('name', $user?->name ?? $member->member_code);
            $member->setAttribute('email', $user?->email);
            $member->setAttribute('gender', $member->gender ?: ($user?->gender ?: 'Laki-laki'));

            if (Schema::hasColumn('members', 'package_id') && ! $member->package_id) {
                $member->setAttribute('package_id', Package::query()->value('id'));
            }
        });
    }

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }
}
