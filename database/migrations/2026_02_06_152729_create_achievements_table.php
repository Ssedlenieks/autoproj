<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->default('🏆');
            $table->string('category'); // builds, parts, hp, community
            $table->integer('points')->default(10);
            $table->string('requirement_type'); // count, threshold, special
            $table->integer('requirement_value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('achievements');
    }
};
