<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_authenticated_user_can_list_notifications(): void
    {
        Sanctum::actingAs($this->user);

        // Global notification (null user_id = for all admins)
        Notification::create([
            'user_id' => null,
            'type'    => 'low_stock',
            'title'   => 'Stok Rendah: Produk A',
            'message' => 'Stok di bawah threshold',
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_authenticated_user_can_mark_notification_as_read(): void
    {
        Sanctum::actingAs($this->user);

        $notification = Notification::create([
            'user_id' => null,
            'type'    => 'low_stock',
            'title'   => 'Stok Rendah: Produk A',
            'message' => 'Stok di bawah threshold',
        ]);

        $response = $this->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertNotNull(Notification::find($notification->id)->read_at);
    }

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }
}
