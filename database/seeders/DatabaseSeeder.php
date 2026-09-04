<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Division;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'user_type' => 'admin',
                'must_reset_password' => false,
            ]
        );

        // 2. Create Default Test User
        $testUser = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'user_type' => 'regular',
                'must_reset_password' => false,
            ]
        );

        // 3. Seed Ticket Statuses
        $statuses = [
            ['name' => 'Open', 'slug' => 'open', 'color_code' => '#F59E0B'],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'color_code' => '#0284C7'],
            ['name' => 'Resolved', 'slug' => 'resolved', 'color_code' => '#10B981'],
            ['name' => 'Closed', 'slug' => 'closed', 'color_code' => '#6B7280'],
        ];
        foreach ($statuses as $status) {
            TicketStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }

        // 4. Seed Ticket Priorities
        $priorities = [
            ['name' => 'Low', 'level' => 1],
            ['name' => 'Medium', 'level' => 2],
            ['name' => 'High', 'level' => 3],
            ['name' => 'Critical', 'level' => 4],
        ];
        foreach ($priorities as $priority) {
            TicketPriority::updateOrCreate(['name' => $priority['name']], $priority);
        }

        // 5. Seed Ticket Types
        $typeModels = [];
        $types = ['Incident', 'Service Request', 'Problem', 'Change Request'];
        foreach ($types as $type) {
            $typeModels[] = TicketType::updateOrCreate(['name' => $type]);
        }

        // 6. Seed Divisions
        $divisionModels = [];
        $divisions = ['IT Division', 'Operations Division', 'Finance Division', 'HR Division'];
        foreach ($divisions as $division) {
            $divisionModels[] = Division::updateOrCreate(['name' => $division]);
        }

        // 7. Seed Departments
        $departments = ['Software Engineering', 'IT Support', 'Customer Operations', 'Accounts Payable'];
        foreach ($departments as $index => $department) {
            $divisionId = $divisionModels[$index % count($divisionModels)]->id;
            Department::updateOrCreate(
                ['name' => $department],
                ['division_id' => $divisionId]
            );
        }

        // 8. Seed Categories
        $categories = [
            ['name' => 'Hardware Issue'],
            ['name' => 'Software Crash'],
            ['name' => 'Network Access'],
            ['name' => 'Billing Inquiry'],
            ['name' => 'Account Creation'],
        ];
        foreach ($categories as $index => $category) {
            $typeId = $typeModels[$index % count($typeModels)]->id;
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['ticket_type_id' => $typeId]
            );
        }
    }
}
