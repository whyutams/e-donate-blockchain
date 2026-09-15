<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('withdrawal_transaction_hash')->nullable()->unique()->after('blockchain_campaign_id');
            $table->enum('withdrawal_status', ['not_ready', 'pending', 'confirmed', 'failed'])->default('not_ready')->after('withdrawal_transaction_hash');
            $table->timestamp('withdrawn_at')->nullable()->after('withdrawal_status');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['withdrawal_transaction_hash', 'withdrawal_status', 'withdrawn_at']);
        });
    }
};