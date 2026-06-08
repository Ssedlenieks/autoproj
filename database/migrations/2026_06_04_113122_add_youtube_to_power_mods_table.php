<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() 
    {
        Schema::table('power_mods', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('notes');
            $table->string('youtube_channel')->nullable()->after('youtube_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('power_mods', function (Blueprint $table) {
            //
        });
    }
};
