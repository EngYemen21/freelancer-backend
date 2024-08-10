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
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
        // $table->integer('user_id')->unsigned(); // Foreign key referencing 'users' table
        $table->string('title');
        $table->date('deadline')->nullable();
        $table->text('description')->nullable();
        $table->decimal('budget', 10, 2)->nullable(); // Assuming budget is in decimal format
        $table->enum('status', ['pending', 'in_progress', 'completed', 'canceled'])->default('pending'); // Project status
        $table->dateTime('dateTime')->nullable();
        $table->timestamps(); // Created_at and updated_at timestamps
        // $table->foreignId('user_id');
        $table->foreignId('user_id')->constrained('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
