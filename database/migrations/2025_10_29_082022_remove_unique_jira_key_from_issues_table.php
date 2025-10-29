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
            // Remove the unique constraint on jira_key
            $table->dropUnique(['jira_key']);

            // Add a unique constraint on the combination of jira_key and session_id
            // This allows the same ticket in different sessions but prevents duplicates within a session
            $table->unique(['jira_key', 'session_id'], 'issues_jira_key_session_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            // Drop the combined unique constraint
            $table->dropUnique('issues_jira_key_session_unique');

            // Restore the unique constraint on jira_key only
            $table->unique('jira_key');
        });
    }
};
