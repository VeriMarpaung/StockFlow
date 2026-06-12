<?php

namespace Tests\Feature;

use App\Jobs\StockUpdatedJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StockApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->user    = User::factory()->create(['role' => 'admin']);
        $category      = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'stock'       => 20,
            'threshold'   => 5,
            'version'     => 0,
        ]);
    }

    public function test_authenticated_user_can_stock_in(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/products/{$this->product->id}/stock-in", [
            'quantity' => 10,
            'note'     => 'Restocking',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('stock_after', 30);

        $this->assertDatabaseHas('stock_transactions', [
            'product_id'  => $this->product->id,
            'type'        => 'in',
            'quantity'    => 10,
            'stock_before' => 20,
            'stock_after' => 30,
        ]);

        Queue::assertPushed(StockUpdatedJob::class);
    }

    public function test_stock_in_validates_required_quantity(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/products/{$this->product->id}/stock-in", []);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);
    }

    public function test_authenticated_user_can_stock_out(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/products/{$this->product->id}/stock-out", [
            'quantity' => 5,
            'version'  => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('stock_after', 15);

        $this->assertDatabaseHas('stock_transactions', [
            'product_id'  => $this->product->id,
            'type'        => 'out',
            'quantity'    => 5,
            'stock_before' => 20,
            'stock_after' => 15,
        ]);

        $this->assertDatabaseHas('products', [
            'id'      => $this->product->id,
            'version' => 1,
        ]);

        Queue::assertPushed(StockUpdatedJob::class);
    }

    public function test_stock_out_invalidates_product_list_cache(): void
    {
        Sanctum::actingAs($this->user);

        // Warm the 'products:all' cache with the pre-mutation snapshot.
        $this->getJson('/api/products')->assertStatus(200);

        // Mutate stock through the optimistic-locking endpoint.
        $this->postJson("/api/products/{$this->product->id}/stock-out", [
            'quantity' => 5,
            'version'  => 0,
        ])->assertStatus(200);

        // The cached list must now reflect the new stock & version, not stale data.
        $listed = collect($this->getJson('/api/products')->json('data'))
            ->firstWhere('id', $this->product->id);

        $this->assertSame(15, $listed['stock'], 'product list cache was not invalidated after stock-out');
        $this->assertSame(1, $listed['version'], 'version in cached list is stale after stock-out');
    }

    public function test_stock_out_fails_when_insufficient_stock(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/products/{$this->product->id}/stock-out", [
            'quantity' => 100,
            'version'  => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 'INSUFFICIENT_STOCK');
    }

    public function test_stock_out_returns_409_on_version_conflict(): void
    {
        Sanctum::actingAs($this->user);

        // Product version is 0, we send stale version=99
        $response = $this->postJson("/api/products/{$this->product->id}/stock-out", [
            'quantity' => 5,
            'version'  => 99,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('code', 'STOCK_CONFLICT');
    }

    public function test_authenticated_user_can_adjust_stock(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/products/{$this->product->id}/adjust-stock", [
            'quantity' => 50,
            'note'     => 'Inventory count correction',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('stock_after', 50);

        $this->assertDatabaseHas('stock_transactions', [
            'product_id' => $this->product->id,
            'type'       => 'adjustment',
            'stock_after' => 50,
        ]);
    }

    public function test_can_get_product_transactions(): void
    {
        Sanctum::actingAs($this->user);

        StockTransaction::create([
            'product_id'  => $this->product->id,
            'user_id'     => $this->user->id,
            'type'        => 'in',
            'quantity'    => 5,
            'stock_before' => 20,
            'stock_after' => 25,
        ]);

        $response = $this->getJson("/api/products/{$this->product->id}/transactions");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_unauthenticated_user_cannot_access_stock_endpoints(): void
    {
        $response = $this->postJson("/api/products/{$this->product->id}/stock-in", [
            'quantity' => 10,
        ]);

        $response->assertStatus(401);
    }
}
