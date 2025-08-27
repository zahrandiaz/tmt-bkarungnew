<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReturnTransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $staf;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->staf = User::factory()->create();
        $stafRole = Role::findByName('Staf');

        $permissions = ['return-view', 'return-create', 'return-delete'];
        foreach ($permissions as $permissionName) {
            if ($permission = Permission::where('name', $permissionName)->first()) {
                $stafRole->givePermissionTo($permission);
            }
        }
        $this->staf->assignRole($stafRole);

        $this->product = Product::factory()->create(['stock' => 100, 'selling_price' => 10000, 'purchase_price' => 8000]);
    }

    #[Test]
    public function user_can_create_sale_return_and_stock_is_adjusted(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $sale = Sale::factory()->create();
        $sale->details()->create(['product_id' => $this->product->id, 'quantity' => 20, 'sale_price' => 10000, 'purchase_price' => 8000]);
        $initialStock = $this->product->stock;

        $returnData = ['sale_id' => $sale->id, 'return_date' => now()->format('Y-m-d'), 'notes' => 'Retur', 'items' => [['product_id' => $this->product->id, 'return_quantity' => 5, 'unit_price' => 10000]]];

        $this->actingAs($this->staf)->post(route('sale-returns.store'), $returnData)->assertRedirect(route('sale-returns.index'));
        $this->assertDatabaseHas('sale_returns', ['sale_id' => $sale->id]);
        $this->product->refresh();
        $this->assertEquals($initialStock + 5, $this->product->stock);
    }

    #[Test]
    public function user_can_cancel_sale_return_and_stock_is_reverted(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $saleReturn = SaleReturn::factory()->create();
        // FINAL FIX: Tambahkan 'subtotal' yang wajib diisi
        $saleReturn->details()->create(['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => $this->product->selling_price, 'subtotal' => 5 * $this->product->selling_price]);
        $this->product->increment('stock', 5);
        $stockBeforeCancellation = $this->product->stock;

        $this->actingAs($this->staf)->delete(route('sale-returns.destroy', $saleReturn))->assertRedirect(route('sale-returns.index'));
        $this->product->refresh();
        $this->assertEquals($stockBeforeCancellation - 5, $this->product->stock);
        $this->assertDatabaseMissing('sale_returns', ['id' => $saleReturn->id]);
    }

    #[Test]
    public function user_can_create_purchase_return_and_stock_is_adjusted(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $purchase = Purchase::factory()->create();
        $purchase->details()->create(['product_id' => $this->product->id, 'quantity' => 30, 'purchase_price' => 8000]);
        $initialStock = $this->product->stock;

        $returnData = ['purchase_id' => $purchase->id, 'return_date' => now()->format('Y-m-d'), 'notes' => 'Retur', 'items' => [['product_id' => $this->product->id, 'return_quantity' => 10, 'unit_price' => 8000]]];

        $this->actingAs($this->staf)->post(route('purchase-returns.store'), $returnData)->assertRedirect(route('purchase-returns.index'));
        $this->assertDatabaseHas('purchase_returns', ['purchase_id' => $purchase->id]);
        $this->product->refresh();
        $this->assertEquals($initialStock - 10, $this->product->stock);
    }

    #[Test]
    public function user_can_cancel_purchase_return_and_stock_is_reverted(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $purchaseReturn = PurchaseReturn::factory()->create();
        // FINAL FIX: Tambahkan 'subtotal' yang wajib diisi
        $purchaseReturn->details()->create(['product_id' => $this->product->id, 'quantity' => 8, 'unit_price' => $this->product->purchase_price, 'subtotal' => 8 * $this->product->purchase_price]);
        $this->product->decrement('stock', 8);
        $stockBeforeCancellation = $this->product->stock;

        $this->actingAs($this->staf)->delete(route('purchase-returns.destroy', $purchaseReturn))->assertRedirect(route('purchase-returns.index'));
        $this->product->refresh();
        $this->assertEquals($stockBeforeCancellation + 8, $this->product->stock);
        $this->assertDatabaseMissing('purchase_returns', ['id' => $purchaseReturn->id]);
    }
}