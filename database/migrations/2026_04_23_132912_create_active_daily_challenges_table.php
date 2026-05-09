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
    Schema::create('active_daily_challenges', function (Blueprint $table) {
        $table->id();
        $table->foreignId('challenge_id')->constrained('daily_challenges')->onDelete('cascade');
        $table->date('date');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_daily_challenges');
    }
};
