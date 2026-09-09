<?php

namespace Database\Seeders;

use App\Models\TicketPriority;
use App\Models\Priority;
use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Ticket Statuses
        $statuses = [
            ['name' => 'Open', 'slug' => 'open', 'color_code' => '#F59E0B'],
            ['name' => 'Assigned', 'slug' => 'assigned', 'color_code' => '#0284C7'],
            ['name' => 'Review', 'slug' => 'review', 'color_code' => '#8B5CF6'],
            ['name' => 'Closed', 'slug' => 'closed', 'color_code' => '#6B7280'],
            ['name' => 'Canceled', 'slug' => 'canceled', 'color_code' => '#EF4444'],
        ];
        foreach ($statuses as $status) {
            TicketStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }

        // 2. Seed Ticket Priorities
        $priorities = [
            ['name' => 'Minor', 'level' => 1],
            ['name' => 'Major', 'level' => 2],
        ];
        foreach ($priorities as $priority) {
            TicketPriority::updateOrCreate(['name' => $priority['name']], $priority);
        }

        // 3. Seed Priority Options (Low, High, Critical)
        $priorityOptions = [
            ['name' => 'Low', 'level' => 1],
            ['name' => 'High', 'level' => 2],
            ['name' => 'Critical', 'level' => 3],
        ];
        foreach ($priorityOptions as $option) {
            Priority::updateOrCreate(['name' => $option['name']], $option);
        }
    }
}
