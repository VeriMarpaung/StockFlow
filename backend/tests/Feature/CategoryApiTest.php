<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/categories')->assertStatus(401);
    }

    public function test_authenticated_user_can_list_categories(): void
    {
        Category::create(['name' => 'Cat A']);
        Category::create(['name' => 'Cat B']);

        $response = $this->actingAs($this->admin)->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/categories', [
            'name'        => 'Electronics',
            'description' => 'Electronic items',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Electronics']);

        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    }

    public function test_create_category_validates_required_name(): void
    {
        $this->actingAs($this->admin)->postJson('/api/categories', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    public function test_authenticated_user_can_update_category(): void
    {
        $category = Category::create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)->putJson("/api/categories/{$category->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'New Name']);
    }

    public function test_authenticated_user_can_delete_category(): void
    {
        $category = Category::create(['name' => 'To Delete']);

        $this->actingAs($this->admin)->deleteJson("/api/categories/{$category->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
