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
            $table->unsignedBigInteger('issue_report_id')->nullable();
            $table->foreign('issue_report_id')
                  ->references('id')->on('issue_reports')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversationes', function (Blueprint $table) {
            //
            $table->dropForeign(['issue_report_id']);
            $table->dropColumn('issue_report_id');
        });
    }
};
