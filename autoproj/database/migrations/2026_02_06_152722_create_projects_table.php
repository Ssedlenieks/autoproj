<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->foreignId('engine_id')->constrained()->onDelete('cascade');
            $table->string('project_name')->default('My Build');
            $table->text('description')->nullable();
            $table->integer('base_hp');
            $table->integer('base_torque');
            $table->integer('total_hp_gain')->default(0);
            $table->integer('total_torque_gain')->default(0);
            $table->integer('final_hp')->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->boolean('is_public')->default(false);
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
