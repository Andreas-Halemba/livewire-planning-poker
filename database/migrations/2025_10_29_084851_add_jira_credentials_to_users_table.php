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
        Schema::table('users', function (Blueprint $table) {
            $table->string('jira_url')->nullable()->after('password');
            $table->string('jira_user')->nullable()->after('jira_url');
            $table->text('jira_api_key')->nullable()->after('jira_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jira_url', 'jira_user', 'jira_api_key']);
        });
    }
};
