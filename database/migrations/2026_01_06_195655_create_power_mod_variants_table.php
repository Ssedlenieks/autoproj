<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('power_mod_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('power_mod_id');
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('engine_id');
            $table->integer('hp_gain')->nullable();
            $table->integer('torque_nm_gain')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('power_mod_id')->references('id')->on('power_mods')->onDelete('cascade');
            $table->foreign('car_id')->references('id')->on('cars')->onDelete('cascade');
            $table->foreign('engine_id')->references('id')->on('engines')->onDelete('cascade');
            $table->index(['car_id', 'engine_id', 'power_mod_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('power_mod_variants');
    }
};
