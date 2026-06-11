<?php

namespace Tests\Feature;

use App\Jobs\LowStockNotificationJob;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_notification_when_stock_below_threshold(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'name'        => 'Mineral Water',
            'stock'       => 3,
            'threshold'   => 5,
        ]);

        (new LowStockNotificationJob($product->id))->handle();

        $this->assertDatabaseHas('notifications', [
            'type'  => 'low_stock',
            'title' => 'Stok Rendah: Mineral Water',
        ]);
    }

    public function test_job_does_not_create_duplicate_unread_notification(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'stock'       => 3,
            'threshold'   => 5,
        ]);

        $job = new LowStockNotificationJob($product->id);
        $job->handle();
        $job->handle(); // second run with same unread notification

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_job_skips_when_stock_above_threshold(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'stock'       => 10,
            'threshold'   => 5,
        ]);

        (new LowStockNotificationJob($product->id))->handle();

        $this->assertDatabaseCount('notifications', 0);
    }
}
