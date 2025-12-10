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
        Schema::create('daily_savings', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('hourly_rate', 12, 2);
            $table->decimal('hours_saved', 8, 2);
            $table->decimal('amount_saved', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_savings');
    }
};
