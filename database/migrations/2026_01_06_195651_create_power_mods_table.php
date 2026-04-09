<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('power_mods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('category');
            $table->boolean('is_estimate')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('category');
        });
    }
    public function down(): void {
        Schema::dropIfExists('power_mods');
    }
};
