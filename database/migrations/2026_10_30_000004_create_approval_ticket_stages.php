<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $stages = [
            ['name' => 'Pending Approval', 'slug' => 'pending-approval', 'color_code' => '#6366F1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Approved', 'slug' => 'approved', 'color_code' => '#10B981', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rejected', 'slug' => 'rejected', 'color_code' => '#EF4444', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($stages as $stage) {
            $exists = DB::table('ticket_stages')->where('slug', $stage['slug'])->exists();
            if (! $exists) {
                DB::table('ticket_stages')->insert($stage);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ticket_stages')->whereIn('slug', ['pending-approval', 'approved', 'rejected'])->delete();
    }
};
