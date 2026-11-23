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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->after('package_id')->nullOnDelete();
            $table->string('bank_name')->nullable()->after('bank_id');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_holder')->nullable()->after('bank_account_number');
            $table->string('sender_name')->nullable()->after('bank_account_holder');
            $table->date('transfer_date')->nullable()->after('sender_name');
            $table->string('payment_proof')->nullable()->after('transfer_date');
            $table->foreignId('verified_by')->nullable()->after('payment_proof')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejection_reason')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'bank_id', 'bank_name', 'bank_account_number', 'bank_account_holder',
                'sender_name', 'transfer_date', 'payment_proof', 'verified_by',
                'verified_at', 'rejection_reason',
            ]);
        });
    }
};
