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
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('package_id')->constrained()->restrictOnDelete();
                $table->decimal('amount', 12, 2);
                $table->date('payment_date')->nullable();
                $table->string('method')->default('Cash');
                $table->string('status')->default('Menunggu');
                $table->text('notes')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('invoice_number');
            }
            if (! Schema::hasColumn('payments', 'registration_id')) {
                $table->unsignedBigInteger('registration_id')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('payments', 'method')) {
                $table->string('method')->default('Cash')->after('payment_date');
            }
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                "ALTER TABLE payments MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Menunggu'",
            );
        }
        if (Schema::hasColumn('payments', 'payment_method')) {
            DB::statement(
                'UPDATE payments SET method = payment_method WHERE method IS NULL OR method = \'Cash\'',
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
