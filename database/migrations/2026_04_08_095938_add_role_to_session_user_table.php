<?php

use App\Enums\SessionParticipantRole;
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
        Schema::table('session_user', function (Blueprint $table) {
            $table->string('role', 32)->default(SessionParticipantRole::Voter->value)->after('user_id');
        });

        DB::table('session_user')->update(['role' => SessionParticipantRole::Voter->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_user', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
