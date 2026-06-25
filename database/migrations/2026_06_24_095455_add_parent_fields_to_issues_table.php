<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->string('parent_key')->nullable()->after('issue_type');
            $table->string('parent_title')->nullable()->after('parent_key');
            $table->string('parent_url')->nullable()->after('parent_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn(['parent_key', 'parent_title', 'parent_url']);
        });
    }
};
