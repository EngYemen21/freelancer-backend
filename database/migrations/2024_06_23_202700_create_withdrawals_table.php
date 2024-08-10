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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
  
            $table->decimal('amount', 10, 2);
            $table->string('withdrawal_method');
            $table->date('withdrawal_date');
            // $table->string('withdrawal_status');
            $table->enum('withdrawal_status', ['pending', 'completed', 'canceled'])->default('pending');
            $table->decimal('commission', 10, 2)->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
