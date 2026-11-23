<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $timestamp = now();

        DB::table('settings')->insertOrIgnore([
            ['key' => 'gym_name', 'value' => 'SI-GYM', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'gym_phone', 'value' => '081329170566', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'gym_address', 'value' => "Jl KH Subahan Ma'kmun, Ds Luwungragi, Brebes", 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'gym_hours', 'value' => '08.00 – 22.00 WIB', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['key' => 'gym_facilities', 'value' => "Alat fitness dan angkat beban\nTreadmill\nSepeda statis\nArea latihan kardio\nArea angkat beban\nRuang ganti", 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserve gym settings in case an administrator has edited these defaults.
    }
};
