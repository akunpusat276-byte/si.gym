<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status')->default('Aktif')->after('role');
                $table->string('avatar')->nullable()->after('address');
            });
        } elseif (! Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('address');
            });
        }

        if (! Schema::hasColumn('packages', 'facilities')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->text('facilities')->nullable()->after('description');
            });
        }

        if (! Schema::hasColumn('members', 'status')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('status')->default('Aktif')->after('address');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('avatar'));
        }
        if (Schema::hasColumn('users', 'status')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('status'));
        }
        if (Schema::hasColumn('packages', 'facilities')) {
            Schema::table('packages', fn (Blueprint $table) => $table->dropColumn('facilities'));
        }
        if (Schema::hasColumn('members', 'status')) {
            Schema::table('members', fn (Blueprint $table) => $table->dropColumn('status'));
        }
    }
};
