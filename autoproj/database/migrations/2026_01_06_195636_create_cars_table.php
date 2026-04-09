<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->year('year');
            $table->string('trim');
            $table->string('body_style');
            $table->string('drive_type');
            $table->integer('weight_kg')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->foreign('model_id')->references('id')->on('models')->onDelete('cascade');
            $table->index(['model_id', 'year']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('cars');
    }
};
