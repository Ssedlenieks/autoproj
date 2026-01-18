<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER DATABASE car_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE makes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE models CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE cars CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE engines CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE car_engine CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE power_mods CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        DB::statement('ALTER TABLE power_mod_variants CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
    }

    public function down(): void
    {
        // Revert if needed
    }
};
