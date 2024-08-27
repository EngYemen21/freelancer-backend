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
        Schema::table('contracts', function (Blueprint $table) {
            //
            // if (!Schema::hasColumn('contracts', 'payment_id')) {
                $table->unsignedBigInteger('payment_id')->nullable();
            // }

            // تغيير العمود ليكون غير قابل للنول إذا كان موجودًا بالفعل
            // $table->unsignedBigInteger('payment_id')->change();

            // إضافة المفتاح الأجنبي
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            //
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
};
