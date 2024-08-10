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
        Schema::create('balances', function (Blueprint $table) {

                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->decimal('total_balance', 10, 2);
                $table->decimal('withdrawable_balance', 10, 2);
                $table->decimal('pending_balance', 10, 2);
                $table->decimal('total_earnings', 10, 2);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
