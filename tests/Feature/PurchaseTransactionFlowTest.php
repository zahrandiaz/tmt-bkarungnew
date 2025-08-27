<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PurchaseTransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Product $product;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->user = User::factory()->create();
        $userRole = Role::findByName('Staf');

        $permissions = ['transaction-view', 'transaction-create', 'transaction-cancel'];
        foreach ($permissions as $permissionName) {
            if ($permission = Permission::where('name', $permissionName)->first()) {
                $userRole->givePermissionTo($permission);
            }
        }
        $this->user->assignRole($userRole);

        $this->product = Product::factory()->create(['stock' => 50]);
        $this->supplier = Supplier::find(1);
    }

    private function createPurchaseData(int $quantity): array
    {
        $totalAmount = $quantity * $this->product->purchase_price;
        return [
            'purchase_date' => now()->format('Y-m-d\TH:i'),
            'supplier_id' => $this->supplier->id,
            'payment_method' => 'tunai', // FINAL FIX: Sesuai aturan validasi
            'payment_status' => 'lunas',
            'down_payment' => 0,
            'notes' => 'Test purchase transaction.',
            'items' => [['product_id' => $this->product->id, 'quantity' => $quantity, 'purchase_price' => $this->product->purchase_price]],
            'total_amount' => $totalAmount
        ];
    }

    #[Test]
    public function it_can_create_purchase_and_stock_is_incremented_when_stock_management_is_enabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $initialStock = $this->product->stock;
        $quantityToPurchase = 20;
        $purchaseData = $this->createPurchaseData($quantityToPurchase);

        $response = $this->actingAs($this->user)->post(route('purchases.store'), $purchaseData);

        $response->assertRedirect(route('purchases.index', ['status' => 'selesai']));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('purchases', ['supplier_id' => $this->supplier->id, 'payment_status' => 'Lunas']);
        $this->product->refresh();
        $this->assertEquals($initialStock + $quantityToPurchase, $this->product->stock);
    }

    #[Test]
    public function stock_is_decremented_when_purchase_is_cancelled_and_stock_management_is_enabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $quantityToPurchase = 10;
        $this->product->update(['stock' => 50]);
        $purchase = Purchase::factory()->create();
        $purchase->details()->create(['product_id' => $this->product->id, 'quantity' => $quantityToPurchase, 'purchase_price' => $this->product->purchase_price]);
        $this->product->increment('stock', $quantityToPurchase);
        $stockBeforeCancellation = $this->product->stock;

        $response = $this->actingAs($this->admin)->delete(route('purchases.cancel', $purchase));

        $response->assertRedirect(route('purchases.index', ['status' => 'selesai']));
        $response->assertSessionHas('success');
        $this->product->refresh();
        $this->assertEquals($stockBeforeCancellation - $quantityToPurchase, $this->product->stock);
        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
    }

    #[Test]
    public function stock_is_incremented_when_cancelled_purchase_is_restored_and_stock_management_is_enabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $quantityToPurchase = 25;
        $purchase = Purchase::factory()->create();
        $purchase->details()->create(['product_id' => $this->product->id, 'quantity' => $quantityToPurchase, 'purchase_price' => $this->product->purchase_price]);
        $purchase->delete();
        $stockBeforeRestore = $this->product->stock;

        $response = $this->actingAs($this->admin)->post(route('purchases.restore', $purchase->id));

        $response->assertRedirect(route('purchases.index', ['status' => 'dibatalkan']));
        $response->assertSessionHas('success');
        $this->product->refresh();
        $this->assertEquals($stockBeforeRestore + $quantityToPurchase, $this->product->stock);
        $this->assertNotSoftDeleted('purchases', ['id' => $purchase->id]);
    }
    
    #[Test]
    public function it_can_create_purchase_and_stock_is_unchanged_when_stock_management_is_disabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '0']);
        $initialStock = $this->product->stock;
        $quantityToPurchase = 20;
        $purchaseData = $this->createPurchaseData($quantityToPurchase);

        $response = $this->actingAs($this->user)->post(route('purchases.store'), $purchaseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchases', ['supplier_id' => $this->supplier->id, 'payment_status' => 'Lunas']);
        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);
    }

    #[Test]
    public function stock_is_unchanged_when_purchase_is_cancelled_and_stock_management_is_disabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '0']);
        $purchase = Purchase::factory()->create();
        $purchase->details()->create(['product_id' => $this->product->id, 'quantity' => 10, 'purchase_price' => $this->product->purchase_price]);
        $stockBeforeCancellation = $this->product->stock;

        $response = $this->actingAs($this->admin)->delete(route('purchases.cancel', $purchase));

        $response->assertRedirect();
        $this->product->refresh();
        $this->assertEquals($stockBeforeCancellation, $this->product->stock);
        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
    }

    #[Test]
    public function stock_is_unchanged_when_cancelled_purchase_is_restored_and_stock_management_is_disabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '0']);
        $purchase = Purchase::factory()->create();
        $purchase->details()->create(['product_id' => $this->product->id, 'quantity' => 25, 'purchase_price' => $this->product->purchase_price]);
        $purchase->delete();
        $stockBeforeRestore = $this->product->stock;

        $response = $this->actingAs($this->admin)->post(route('purchases.restore', $purchase->id));

        $response->assertRedirect();
        $this->product->refresh();
        $this->assertEquals($stockBeforeRestore, $this->product->stock);
        $this->assertNotSoftDeleted('purchases', ['id' => $purchase->id]);
    }
}