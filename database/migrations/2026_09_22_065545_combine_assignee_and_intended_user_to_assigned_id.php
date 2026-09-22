<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('assigned_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        // Migrate data prioritizing assigned_to (assignee) over to_user_id (intended user)
        DB::statement('UPDATE tickets SET assigned_id = COALESCE(assigned_to, to_user_id)');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['to_user_id']);
            $table->dropColumn(['assigned_to', 'to_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
        });

        DB::statement('UPDATE tickets SET assigned_to = assigned_id');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_id']);
            $table->dropColumn('assigned_id');
        });
    }
};
