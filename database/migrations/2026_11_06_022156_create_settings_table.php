<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'key')) {
                $table->string('key')->nullable()->after('id');
            }
            if (! Schema::hasColumn('settings', 'value')) {
                $table->text('value')->nullable()->after('key');
            }
        });

        if (Schema::hasColumn('settings', 'gym_name')) {
            DB::statement(
                "UPDATE settings SET `key` = 'gym_name', `value` = gym_name WHERE `key` IS NULL",
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
