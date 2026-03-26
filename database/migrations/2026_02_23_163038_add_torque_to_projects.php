<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'final_torque'))
                $table->integer('final_torque')->nullable()->after('base_torque');
        });
    }

    public function down() {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'final_torque'))
                $table->dropColumn('final_torque');
        });
    }
};
