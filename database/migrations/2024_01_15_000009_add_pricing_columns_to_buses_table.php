<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->enum('pricing_type', ['daily', 'distance'])->default('daily');
            $table->decimal('price_per_day', 10, 2)->nullable();
            $table->decimal('price_per_km', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'price_per_day', 'price_per_km']);
        });
    }
};
