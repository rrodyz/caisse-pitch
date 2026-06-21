<?php

namespace Tests\Unit\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Products\app\Models\Product;
use Modules\Stock\app\Enums\MovementType;
use Modules\Stock\app\Models\StockMovement;
use Modules\Stock\app\Services\StockService;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockService();
        $this->user    = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function makeProduct(int $stock = 0): Product
    {
        return Product::create([
            'code'           => 'TST-' . uniqid(),
            'name'           => 'Test Product',
            'purchase_price' => 1000,
            'selling_price'  => 1500,
            'stock_quantity' => $stock,
            'min_stock'      => 0,
            'unit'           => 'unité',
            'is_active'      => true,
        ]);
    }

    public function test_add_stock_increases_quantity(): void
    {
        $product = $this->makeProduct(10);

        $movement = $this->service->addStock($product->id, 5, MovementType::ManualIn);

        $this->assertSame(15, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => MovementType::ManualIn->value,
            'quantity'        => 5,
            'quantity_before' => 10,
            'quantity_after'  => 15,
            'user_id'         => $this->user->id,
        ]);
        $this->assertInstanceOf(StockMovement::class, $movement);
    }

    public function test_deduct_stock_decreases_quantity(): void
    {
        $product = $this->makeProduct(10);

        $this->service->deductStock($product->id, 3, MovementType::SaleOut);

        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => MovementType::SaleOut->value,
            'quantity_before' => 10,
            'quantity_after'  => 7,
        ]);
    }

    public function test_deduct_stock_never_goes_below_zero(): void
    {
        $product = $this->makeProduct(2);

        $this->service->deductStock($product->id, 10, MovementType::SaleOut);

        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    public function test_adjust_stock_sets_absolute_quantity(): void
    {
        $product = $this->makeProduct(10);

        $movement = $this->service->adjustStock($product->id, 25, 'Inventaire juin');

        $this->assertSame(25, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => MovementType::InventoryAdjustment->value,
            'quantity_before' => 10,
            'quantity_after'  => 25,
            'quantity'        => 15,
            'notes'           => 'Inventaire juin',
        ]);
    }

    public function test_adjust_stock_records_negative_delta_as_absolute_quantity(): void
    {
        $product = $this->makeProduct(20);

        $this->service->adjustStock($product->id, 12);

        $this->assertSame(12, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id'      => $product->id,
            'type'            => MovementType::InventoryAdjustment->value,
            'quantity_before' => 20,
            'quantity_after'  => 12,
            'quantity'        => 8,
        ]);
    }
}
