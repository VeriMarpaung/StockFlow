<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::create([
            'name'        => 'Test Category',
            'description' => null,
        ]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }

    public function test_authenticated_user_can_list_products(): void
    {
        Product::factory()->count(3)->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_authenticated_user_can_create_product(): void
    {
        $payload = [
            'category_id' => $this->category->id,
            'name'        => 'Test Product',
            'sku'         => 'TEST-001',
            'price'       => 100000,
            'stock'       => 50,
            'threshold'   => 10,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/products', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Test Product', 'sku' => 'TEST-001']);

        $this->assertDatabaseHas('products', ['sku' => 'TEST-001']);
    }

    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/products', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'sku', 'price', 'category_id']);
    }

    public function test_create_product_rejects_duplicate_sku(): void
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'sku'         => 'DUPE-001',
        ]);

        $response = $this->actingAs($this->admin)->postJson('/api/products', [
            'category_id' => $this->category->id,
            'name'        => 'Another Product',
            'sku'         => 'DUPE-001',
            'price'       => 50000,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['sku']);
    }

    public function test_authenticated_user_can_view_single_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_viewing_nonexistent_product_returns_404(): void
    {
        $this->actingAs($this->admin)->getJson('/api/products/99999')
             ->assertStatus(404);
    }

    public function test_authenticated_user_can_update_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)->putJson("/api/products/{$product->id}", [
            'name'  => 'Updated Name',
            'price' => 999000,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Name']);
    }

    public function test_authenticated_user_can_delete_product(): void
    {
        $product = Product::factory()->create(['category_id' => $this->category->id]);

        $this->actingAs($this->admin)->deleteJson("/api/products/{$product->id}")
             ->assertStatus(200);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
