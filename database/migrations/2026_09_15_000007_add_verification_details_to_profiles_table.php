<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_profiles', function (Blueprint $table) {
            $table->text('individual_nik')->nullable()->after('legal_name');
            $table->text('individual_address')->nullable()->after('individual_nik');
            $table->string('individual_call_center')->nullable()->after('individual_address');
            $table->string('individual_selfie_path')->nullable()->after('individual_call_center');
            $table->string('individual_ktp_path')->nullable()->after('individual_selfie_path');
            $table->string('foundation_responsible_name')->nullable()->after('individual_ktp_path');
            $table->text('foundation_npwp')->nullable()->after('foundation_responsible_name');
            $table->string('foundation_call_center')->nullable()->after('foundation_npwp');
            $table->string('foundation_legal_document_path')->nullable()->after('foundation_call_center');
        });
    }

    public function down(): void
    {
        Schema::table('verification_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'individual_nik', 'individual_address', 'individual_call_center',
                'individual_selfie_path', 'individual_ktp_path', 'foundation_responsible_name',
                'foundation_npwp', 'foundation_call_center', 'foundation_legal_document_path',
            ]);
        });
    }
};