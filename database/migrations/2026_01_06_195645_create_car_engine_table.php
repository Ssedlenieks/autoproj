<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('car_engine', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('engine_id');
            $table->integer('power_hp')->nullable();
            $table->integer('torque_nm')->nullable();
            $table->decimal('acceleration_0_100', 4, 2)->nullable();
            $table->integer('top_speed')->nullable();
            $table->timestamps();

            $table->foreign('car_id')->references('id')->on('cars')->onDelete('cascade');
            $table->foreign('engine_id')->references('id')->on('engines')->onDelete('cascade');
            $table->unique(['car_id', 'engine_id']);
            $table->index(['car_id', 'engine_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('car_engine');
    }
};
