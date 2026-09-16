<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('donor_name')->nullable()->after('donor_id');
            $table->string('donor_email')->nullable()->after('donor_name');
            $table->string('donor_phone')->nullable()->after('donor_email');
            $table->string('payment_method')->default('bca')->after('donor_phone');
            $table->string('reference_code')->nullable()->unique()->after('payment_method');
            $table->string('payment_proof_path')->nullable()->after('reference_code');
            $table->text('donor_note')->nullable()->after('payment_proof_path');
            $table->text('admin_notes')->nullable()->after('donor_note');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('payout_bank_name')->nullable()->after('wallet_address');
            $table->string('payout_account_number')->nullable()->after('payout_bank_name');
            $table->string('payout_account_name')->nullable()->after('payout_account_number');
            $table->unsignedBigInteger('withdrawal_amount')->nullable()->after('withdrawal_transaction_hash');
            $table->string('withdrawal_proof_path')->nullable()->after('withdrawal_amount');
            $table->text('withdrawal_notes')->nullable()->after('withdrawal_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn([
                'donor_name',
                'donor_email',
                'donor_phone',
                'payment_method',
                'reference_code',
                'payment_proof_path',
                'donor_note',
                'admin_notes',
            ]);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'payout_bank_name',
                'payout_account_number',
                'payout_account_name',
                'withdrawal_amount',
                'withdrawal_proof_path',
                'withdrawal_notes',
            ]);
        });
    }
};
