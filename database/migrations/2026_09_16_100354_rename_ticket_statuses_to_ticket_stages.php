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
        // 1. Rename the lookup table
        Schema::rename('ticket_statuses', 'ticket_stages');

        // 2. Modify columns on tickets table
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the old foreign key constraint
            $table->dropForeign(['status_id']);
            // Rename column
            $table->renameColumn('status_id', 'stage_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            // Re-establish foreign key to ticket_stages
            $table->foreign('stage_id')->references('id')->on('ticket_stages')->restrictOnDelete();
            // Add system-controlled status
            $table->string('status')->default('Valid')->after('stage_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['stage_id']);
            $table->dropColumn('status');
            $table->renameColumn('stage_id', 'status_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('status_id')->references('id')->on('ticket_statuses')->restrictOnDelete();
        });

        Schema::rename('ticket_stages', 'ticket_statuses');
    }
};
