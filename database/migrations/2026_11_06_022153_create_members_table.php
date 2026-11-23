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
        if (! Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('member_code')->unique();
                $table->string('phone')->nullable();
                $table->string('gender')->nullable();
                $table->date('birth_date')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('members', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('gender');
            }
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                'ALTER TABLE members MODIFY name VARCHAR(255) NULL',
            );
            DB::statement(
                'ALTER TABLE members MODIFY gender ENUM(\'Laki-laki\', \'Perempuan\') NULL',
            );
            DB::statement(
                'ALTER TABLE members MODIFY package_id BIGINT UNSIGNED NULL',
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
