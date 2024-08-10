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
        Schema::table('conversationes', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('project_id')->nullable()->after('id');
            $table->unsignedBigInteger('service_id')->nullable()->after('project_id');

            // إضافة الفهارس للأعمدة الجديدة
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('conversationes', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['service_id']);
            $table->dropColumn(['project_id', 'service_id']);
        });
    }

};
