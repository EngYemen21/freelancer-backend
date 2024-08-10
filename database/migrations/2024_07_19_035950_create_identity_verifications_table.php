<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('front_image');
            $table->string('back_image');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->dateTime('created_at')->nullable()->default(Carbon::now()->format('Y-m-d H:i:s'));
            $table->dateTime('updated_at')->nullable()->default(Carbon::now()->format('Y-m-d H:i:s'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_verifications');
    }
};
