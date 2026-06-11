<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    private function fakeLlmSuccess(string $content = 'Insight: Stok Mineral Water kritis.'): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [['message' => ['content' => $content]]]
            ], 200),
        ]);
    }

    public function test_unauthenticated_cannot_access_insights(): void
    {
        $response = $this->getJson('/api/analytics/insights');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_regenerate(): void
    {
        $response = $this->postJson('/api/analytics/insights/regenerate');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_insights(): void
    {
        $this->fakeLlmSuccess();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/analytics/insights');

        $response->assertStatus(200)
                 ->assertJsonStructure(['insights', 'generated_at', 'cached'])
                 ->assertJsonPath('cached', false);
    }

    public function test_second_call_returns_cached_result(): void
    {
        $this->fakeLlmSuccess('First generated insight.');
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/analytics/insights')->assertStatus(200)->assertJsonPath('cached', false);

        $response = $this->getJson('/api/analytics/insights');
        $response->assertStatus(200)
                 ->assertJsonPath('cached', true)
                 ->assertJsonPath('insights', 'First generated insight.');
    }

    public function test_regenerate_bypasses_cache_and_returns_fresh_insights(): void
    {
        $this->fakeLlmSuccess('Fresh regenerated insight.');
        Sanctum::actingAs(User::factory()->create());

        Cache::put('analytics:insights', 'Old cached insight', 3600);

        $response = $this->postJson('/api/analytics/insights/regenerate');

        $response->assertStatus(200)
                 ->assertJsonPath('insights', 'Fresh regenerated insight.')
                 ->assertJsonPath('cached', false);

        $this->assertEquals('Fresh regenerated insight.', Cache::get('analytics:insights'));
    }
}
