<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_bank_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->default('Bank Central Asia (BCA)');
            $table->string('bank_code')->default('BCA');
            $table->string('account_number')->default('8830192841');
            $table->string('account_name')->default('Yayasan SafeGive Kebaikan Indonesia');
            $table->string('qris_image_path')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_bank_settings');
    }
};
