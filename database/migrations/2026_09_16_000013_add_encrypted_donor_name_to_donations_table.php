<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->boolean('is_anonymous')->default(false)->after('donor_id');
            $table->text('encrypted_donor_name')->nullable()->after('donor_name');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['is_anonymous', 'encrypted_donor_name']);
        });
    }
};
