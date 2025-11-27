<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('status');
        });

        // Bestehende Issues mit Position versehen (nach ID sortiert)
        DB::statement('SET @pos := 0');
        DB::statement('UPDATE issues SET position = (@pos := @pos + 1) ORDER BY session_id, id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
