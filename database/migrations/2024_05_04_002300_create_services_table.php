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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            // $table->integer('user_id')->unsigned(); // Foreign key referencing 'users' table
            $table->string('title');
            $table->longText('description');
            $table->integer('price',10,2);//decimal('price',10,2)
            $table->date('delivery_time');
            $table->string('image');
            $table->timestamps();
            $table->foreignId('category_id')->constrained("categories");
            $table->foreignId('user_id')->constrained('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
