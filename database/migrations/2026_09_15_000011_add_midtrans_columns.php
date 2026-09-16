<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_bank_settings', function (Blueprint $table) {
            $table->boolean('midtrans_is_active')->default(true)->after('is_active');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_is_active');
            $table->string('midtrans_server_key')->nullable()->after('midtrans_is_production');
            $table->string('midtrans_client_key')->nullable()->after('midtrans_server_key');
            $table->string('midtrans_merchant_id')->nullable()->after('midtrans_client_key');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('reference_code');
            $table->string('snap_redirect_url')->nullable()->after('snap_token');
            $table->string('midtrans_transaction_id')->nullable()->after('snap_redirect_url');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_id');
            $table->json('midtrans_response')->nullable()->after('midtrans_payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('admin_bank_settings', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_is_active',
                'midtrans_is_production',
                'midtrans_server_key',
                'midtrans_client_key',
                'midtrans_merchant_id',
            ]);
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn([
                'snap_token',
                'snap_redirect_url',
                'midtrans_transaction_id',
                'midtrans_payment_type',
                'midtrans_response',
            ]);
        });
    }
};
