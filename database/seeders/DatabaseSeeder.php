<?php

namespace Database\Seeders;

use App\Models\Priority;
use App\Models\TicketStatus;
use App\Models\Setting;
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

        // 2. Seed Priority Options (Low, High, Critical)
        $priorityOptions = [
            ['name' => 'Low', 'level' => 1],
            ['name' => 'High', 'level' => 2],
            ['name' => 'Critical', 'level' => 3],
        ];
        foreach ($priorityOptions as $option) {
            Priority::updateOrCreate(['name' => $option['name']], $option);
        }

        // 3. Seed Default System Settings
        $settings = [
            'sla_days_low' => '5',
            'sla_days_high' => '3',
            'sla_days_critical' => '1',
            'sla_days_assign_low' => '2',
            'sla_days_assign_high' => '1',
            'sla_days_assign_critical' => '0',
        ];
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
