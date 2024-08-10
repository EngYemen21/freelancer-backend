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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('client_id');
            $table->integer('quality_score')->unsigned()->between(1, 5);
            $table->integer('delivery_speed_score')->unsigned()->between(1, 5);
            $table->integer('communication_score')->unsigned()->between(1, 5);
            $table->integer('deadline_adherence_score')->unsigned()->between(1, 5);
            $table->float('overall_score');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services');
            $table->foreign('client_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_ratings');
    }
};
