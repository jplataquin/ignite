<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Priority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\Division;
use App\Models\Department;
use App\Models\Category;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable CSRF verification for testing POST requests
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
    }

    /**
     * Helper to create a user and a ticket for notifications.
     */
    protected function createTestData()
    {
        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);

        $user = User::factory()->create([
            'user_type' => 'user',
            'is_approved' => true,
            'division_id' => $division->id,
            'department_id' => $department->id,
        ]);

        $this->actingAs($user);

        $status = TicketStatus::create([
            'name' => 'Open',
            'slug' => 'open',
            'is_default' => true,
            'color_code' => '#1',
        ]);

        $priorityOption = Priority::create([
            'name' => 'Medium',
            'level' => 2,
        ]);

        $ticketType = TicketType::create([
            'name' => 'Support',
            'slug' => 'support',
        ]);

        $category = Category::create([
            'name' => 'Software',
            'ticket_type_id' => $ticketType->id
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'INC-12345',
            'title' => 'Test Ticket',
            'description' => 'Test description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'created_by' => $user->id,
            'category_1_id' => $category->id,
        ]);

        return [$user, $ticket];
    }

    /**
     * Unauthenticated users are redirected/unauthorized.
     */
    public function test_unauthenticated_users_cannot_access_notifications_endpoints(): void
    {
        $this->getJson('/api/notifications')
            ->assertStatus(401);

        $this->postJson('/api/notifications/some-id/read')
            ->assertStatus(401);

        $this->postJson('/api/notifications/read-all')
            ->assertStatus(401);
    }

    /**
     * Authenticated users can fetch their notifications.
     */
    public function test_authenticated_users_can_fetch_notifications(): void
    {
        [$user, $ticket] = $this->createTestData();

        // Send a notification to the user
        $user->notifyNow(new TicketUpdatedNotification($ticket, 'Your ticket was updated.'));

        $response = $this->actingAs($user)->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'unread_count',
                'notifications' => [
                    '*' => [
                        'id',
                        'type',
                        'data',
                        'message',
                        'read_at',
                        'created_at_human',
                        'link',
                    ]
                ]
            ]);

        $this->assertEquals(1, $response->json('unread_count'));
        $this->assertEquals('Your ticket was updated.', $response->json('notifications.0.message'));
    }

    /**
     * Authenticated users can mark a notification as read.
     */
    public function test_authenticated_users_can_mark_notification_as_read(): void
    {
        [$user, $ticket] = $this->createTestData();

        $user->notifyNow(new TicketUpdatedNotification($ticket, 'Update message.'));
        $notification = $user->unreadNotifications->first();

        $response = $this->actingAs($user)->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertNull($user->fresh()->unreadNotifications->first());
    }

    /**
     * Authenticated users can mark all notifications as read.
     */
    public function test_authenticated_users_can_mark_all_notifications_as_read(): void
    {
        [$user, $ticket] = $this->createTestData();

        $user->notifyNow(new TicketUpdatedNotification($ticket, 'Update 1.'));
        $user->notifyNow(new TicketUpdatedNotification($ticket, 'Update 2.'));

        $this->assertEquals(2, $user->fresh()->unreadNotifications->count());

        $response = $this->actingAs($user)->postJson('/api/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 0,
            ]);

        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());
    }

    /**
     * Intended user receives notification if ticket is created with open status.
     */
    public function test_intended_user_receives_notification_on_open_ticket_creation(): void
    {
        $creator = User::factory()->create(['user_type' => 'admin', 'is_approved' => true]);
        $intendedUser = User::factory()->create(['user_type' => 'user', 'is_approved' => true]);

        $division = Division::create(['name' => 'IT']);
        $department = Department::create(['name' => 'Support', 'division_id' => $division->id]);
        $status = TicketStatus::create(['name' => 'Open', 'slug' => 'open', 'color_code' => '#1']);
        $priorityOption = Priority::create(['name' => 'Medium', 'level' => 2]);
        $ticketType = TicketType::create(['name' => 'Support', 'slug' => 'support']);
        $category = Category::create(['name' => 'Software', 'ticket_type_id' => $ticketType->id]);

        $response = $this->actingAs($creator)->post('/tickets', [
            'title' => 'Ticket for intended user',
            'description' => 'Detailed test description',
            'ticket_type_id' => $ticketType->id,
            'priority_option_id' => $priorityOption->id,
            'status_id' => $status->id,
            'division_id' => $division->id,
            'department_id' => $department->id,
            'category_1_id' => $category->id,
            'to_user_id' => $intendedUser->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        // Assert intendedUser has received the notification
        $this->assertEquals(1, $intendedUser->fresh()->unreadNotifications->count());
        $notification = $intendedUser->fresh()->unreadNotifications->first();
        $this->assertEquals(\App\Notifications\TicketUpdatedNotification::class, $notification->type);
    }

    /**
     * User receives notification when a comment is added to their ticket.
     */
    public function test_creator_receives_notification_on_ticket_comment(): void
    {
        [$user, $ticket] = $this->createTestData(); // $user is the creator of $ticket and logged in

        $otherUser = User::factory()->create(['user_type' => 'admin', 'is_approved' => true]);

        // Put $otherUser as the acting user
        $this->actingAs($otherUser);

        // Add a comment
        $this->post("/tickets/{$ticket->id}/comments", [
            'content' => 'This is a new test comment.',
        ])->assertRedirect();

        // Creator should receive a notification
        $this->assertEquals(1, $user->fresh()->unreadNotifications->count());
        $notification = $user->fresh()->unreadNotifications->first();
        $this->assertEquals("New comment on Ticket {$ticket->ticket_number} by {$otherUser->name}.", $notification->data['message']);
    }

    /**
     * Users receive a specific notification when SLA threshold is exceeded and ticket is marked as lapsed.
     */
    public function test_notification_on_sla_lapsed(): void
    {
        [$user, $ticket] = $this->createTestData(); // $user is the creator

        $lapsedStatus = TicketStatus::create([
            'name' => 'Lapsed',
            'slug' => 'lapsed',
            'color_code' => '#FF0000',
        ]);

        // Logout current user to simulate system cron/CLI execution
        \Illuminate\Support\Facades\Auth::logout();

        $ticket->status_id = $lapsedStatus->id;
        $ticket->save();

        $this->assertEquals(1, $user->fresh()->unreadNotifications->count());
        $notification = $user->fresh()->unreadNotifications->first();
        $this->assertEquals("Ticket {$ticket->ticket_number} has lapsed due to SLA threshold.", $notification->data['message']);
    }
}
