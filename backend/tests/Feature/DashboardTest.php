<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_dashboard_summary(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/dashboard/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_products',
                'low_stock_count',
                'total_categories',
                'transactions_today',
                'unread_notifications',
            ]);
    }

    public function test_unauthenticated_user_cannot_get_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard/summary');
        $response->assertStatus(401);
    }
}
