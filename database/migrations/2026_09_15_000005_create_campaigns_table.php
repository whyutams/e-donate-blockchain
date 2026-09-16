<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('category')->index();
            $table->string('image_path')->nullable();
            $table->unsignedBigInteger('target_amount');
            $table->text('encrypted_collected_amount')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->unsignedInteger('donors_count')->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['draft', 'active', 'goal_reached', 'expired', 'withdrawn'])->default('draft');
            $table->string('wallet_address')->nullable();
            $table->string('blockchain_campaign_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};