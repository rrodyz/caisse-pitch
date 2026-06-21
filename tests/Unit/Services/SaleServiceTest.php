<?php

namespace Tests\Unit\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Products\app\Models\Product;
use Modules\Recipes\app\Services\RecipeService;
use Modules\Sales\app\Enums\PaymentMode;
use Modules\Sales\app\Enums\SaleStatus;
use Modules\Sales\app\Models\Sale;
use Modules\Sales\app\Services\SaleService;
use Modules\Settings\app\Models\Setting;
use Modules\Stock\app\Enums\MovementType;
use Modules\Stock\app\Services\StockService;
use RuntimeException;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SaleService(new RecipeService(), new StockService());
        $this->user    = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function makeProduct(int $stock = 50, float $price = 2000): Product
    {
        return Product::create([
            'code'           => 'P-' . uniqid(),
            'name'           => 'Bière',
            'purchase_price' => 1000,
            'selling_price'  => $price,
            'stock_quantity' => $stock,
            'min_stock'      => 0,
            'unit'           => 'bouteille',
            'is_active'      => true,
        ]);
    }

    // ── discountExceedsLimit ─────────────────────────────────────────────────

    public function test_discount_within_limit_returns_false(): void
    {
        Setting::firstOrCreate([]);  // defaults: max_discount_percent = 10

        $result = $this->service->discountExceedsLimit(500, 10000);  // 5%

        $this->assertFalse($result);
    }

    public function test_discount_exceeds_limit_returns_true(): void
    {
        Setting::firstOrCreate([]);  // defaults: max_discount_percent = 10

        $result = $this->service->discountExceedsLimit(1500, 10000);  // 15%

        $this->assertTrue($result);
    }

    public function test_discount_check_respects_custom_threshold(): void
    {
        $settings = Setting::firstOrCreate([]);
        $settings->update(['max_discount_percent' => 20]);

        $this->assertFalse($this->service->discountExceedsLimit(1500, 10000));  // 15% < 20%
        $this->assertTrue($this->service->discountExceedsLimit(2500, 10000));   // 25% > 20%
    }

    public function test_discount_check_returns_false_when_no_limit_set(): void
    {
        $settings = Setting::firstOrCreate([]);
        $settings->update(['max_discount_percent' => 0]);

        $this->assertFalse($this->service->discountExceedsLimit(9999, 10000));
    }

    // ── createFromCart ───────────────────────────────────────────────────────

    public function test_create_from_cart_creates_sale_and_items(): void
    {
        $product = $this->makeProduct(10);

        $cartItems = [[
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'unit_price'   => 2000,
            'quantity'     => 2,
            'discount'     => 0,
            'total_price'  => 4000,
        ]];

        $payment = [
            'mode'           => 'cash',
            'payment_status' => 'paid',
            'discount'       => 0,
            'notes'          => '',
            'customer_id'    => null,
        ];

        $sale = $this->service->createFromCart($cartItems, $payment, null);

        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertSame(SaleStatus::Completed, $sale->status);
        $this->assertSame(PaymentMode::Cash, $sale->payment_mode);
        $this->assertEquals(4000, $sale->total_amount);
        $this->assertCount(1, $sale->items);

        $this->assertDatabaseHas('sale_items', [
            'sale_id'      => $sale->id,
            'product_id'   => $product->id,
            'quantity'     => 2,
            'total_price'  => 4000,
        ]);

        // Stock deducted (no recipe → direct deduction of product itself)
        $this->assertSame(8, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type'       => MovementType::SaleOut->value,
            'quantity'   => 2,
        ]);
    }

    public function test_create_from_cart_applies_discount(): void
    {
        $product = $this->makeProduct(10, 5000);

        $cartItems = [[
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'unit_price'   => 5000,
            'quantity'     => 1,
            'discount'     => 0,
            'total_price'  => 5000,
        ]];

        $payment = [
            'mode'           => 'cash',
            'payment_status' => 'paid',
            'discount'       => 500,
            'notes'          => '',
            'customer_id'    => null,
        ];

        $sale = $this->service->createFromCart($cartItems, $payment, null);

        $this->assertEquals(5000, $sale->subtotal);
        $this->assertEquals(500, $sale->discount_amount);
        $this->assertEquals(4500, $sale->total_amount);
    }

    // ── cancel ───────────────────────────────────────────────────────────────

    public function test_cancel_restores_stock_and_marks_cancelled(): void
    {
        $product = $this->makeProduct(10);

        $cartItems = [[
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'unit_price'   => 2000,
            'quantity'     => 3,
            'discount'     => 0,
            'total_price'  => 6000,
        ]];

        $sale = $this->service->createFromCart($cartItems, [
            'mode'           => 'cash',
            'payment_status' => 'paid',
            'discount'       => 0,
            'notes'          => '',
            'customer_id'    => null,
        ], null);

        $this->assertSame(7, $product->fresh()->stock_quantity);

        $cancelled = $this->service->cancel($sale, 'Erreur de commande');

        $this->assertSame(SaleStatus::Cancelled, $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
        $this->assertSame('Erreur de commande', $cancelled->cancel_reason);

        // Stock restored
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_cancel_throws_if_already_cancelled(): void
    {
        $product = $this->makeProduct(10);

        $sale = $this->service->createFromCart([[
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'unit_price'   => 2000,
            'quantity'     => 1,
            'discount'     => 0,
            'total_price'  => 2000,
        ]], [
            'mode'           => 'cash',
            'payment_status' => 'paid',
            'discount'       => 0,
            'notes'          => '',
            'customer_id'    => null,
        ], null);

        $this->service->cancel($sale, 'Première annulation');

        $this->expectException(RuntimeException::class);
        $this->service->cancel($sale->fresh(), 'Deuxième annulation');
    }
}
