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
        Schema::table('chatprojects', function (Blueprint $table) {
            //
            $table->foreignId('chatAcceptedBid_id')->nullable()->constrained('accepted_bids')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatprojects', function (Blueprint $table) {
            //
        });
    }
};
