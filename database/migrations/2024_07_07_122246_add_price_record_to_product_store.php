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
        Schema::table('product_store', function (Blueprint $table) {
            $table->integer('best_price')->unsigned()->nullable()->after('price');
            $table->timestamp('best_price_date')->nullable()->after('best_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_store', function (Blueprint $table) {
            $table->dropColumn('best_price');
            $table->dropColumn('best_price_date');
        });
    }
};
