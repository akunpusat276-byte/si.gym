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
        if (! Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedInteger('duration');
                $table->decimal('price', 12, 2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            return;
        }

        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasColumn('packages', 'duration')) {
                $table->unsignedInteger('duration')->nullable()->after('description');
            }
        });

        if (Schema::hasColumn('packages', 'duration_days')) {
            DB::statement(
                'UPDATE packages SET duration = GREATEST(1, CEILING(duration_days / 30))',
            );
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
                DB::statement(
                    'ALTER TABLE packages MODIFY duration_days INT UNSIGNED NOT NULL DEFAULT 30',
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
