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
    Schema::table('daily_challenges', function (Blueprint $table) {
        $table->unique('key');
    });
}

public function down(): void
{
    Schema::table('daily_challenges', function (Blueprint $table) {
        $table->dropUnique(['key']);
    });
}
};
