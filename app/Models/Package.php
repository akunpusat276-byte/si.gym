<?php

namespace App\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    protected $fillable = ['name', 'category', 'description', 'facilities', 'duration', 'duration_days', 'price', 'is_active', 'image_path'];

    protected static function booted(): void
    {
        static::saving(function (Package $package): void {
            if ($package->duration !== null && Schema::hasColumn('packages', 'duration_days')) {
                $package->duration_days = ((int) $package->duration) * 30;
            }
        });
    }

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return str_starts_with($this->image_path, 'images/')
            ? asset($this->image_path)
            : Storage::disk('public')->url($this->image_path);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
