<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SaleTransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Product $product;
    private Customer $customer;

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

        $this->product = Product::factory()->create(['stock' => 100]);
        $this->customer = Customer::find(1);
    }

    private function createSaleData(int $quantity): array
    {
        $totalAmount = $quantity * $this->product->selling_price;
        return [
            'sale_date' => now()->format('Y-m-d\TH:i'),
            'customer_id' => $this->customer->id,
            'payment_method' => 'tunai', // FINAL FIX: Sesuai aturan validasi
            'payment_status' => 'lunas',
            'down_payment' => 0,
            'notes' => 'Test sale transaction.',
            'items' => [['product_id' => $this->product->id, 'quantity' => $quantity, 'sale_price' => $this->product->selling_price]],
            'total_amount' => $totalAmount
        ];
    }

    #[Test]
    public function it_can_create_sale_and_stock_is_decremented_when_stock_management_is_enabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $initialStock = $this->product->stock;
        $quantityToSell = 5;
        $saleData = $this->createSaleData($quantityToSell);
        
        $response = $this->actingAs($this->user)->post(route('sales.store'), $saleData);
        
        $response->assertRedirect(route('sales.index', ['status' => 'selesai']));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('sales', ['customer_id' => $this->customer->id, 'payment_status' => 'Lunas']);
        $this->product->refresh();
        $this->assertEquals($initialStock - $quantityToSell, $this->product->stock);
    }

    #[Test]
    public function it_cannot_create_sale_if_stock_is_insufficient_when_stock_management_is_enabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $initialStock = 5;
        $this->product->update(['stock' => $initialStock]);
        $quantityToSell = 10;
        $saleData = $this->createSaleData($quantityToSell);

        $response = $this->actingAs($this->user)->post(route('sales.store'), $saleData);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('sales', 0);
        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);
    }

    #[Test]
    public function stock_is_incremented_when_sale_is_cancelled_and_stock_management_is_enabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $quantityToSell = 10;
        $sale = Sale::factory()->create();
        $sale->details()->create(['product_id' => $this->product->id, 'quantity' => $quantityToSell, 'sale_price' => $this->product->selling_price, 'purchase_price' => $this->product->purchase_price]);
        $this->product->decrement('stock', $quantityToSell);
        $stockBeforeCancellation = $this->product->stock;
        
        $response = $this->actingAs($this->admin)->delete(route('sales.cancel', $sale));

        $response->assertRedirect(route('sales.index', ['status' => 'selesai']));
        $response->assertSessionHas('success');
        $this->product->refresh();
        $this->assertEquals($stockBeforeCancellation + $quantityToSell, $this->product->stock);
        $this->assertSoftDeleted('sales', ['id' => $sale->id]);
    }

    #[Test]
    public function stock_is_decremented_when_cancelled_sale_is_restored_and_stock_management_is_enabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '1']);
        $quantityToSell = 15;
        $this->product->update(['stock' => 20]);
        $sale = Sale::factory()->create();
        $sale->details()->create(['product_id' => $this->product->id, 'quantity' => $quantityToSell, 'sale_price' => $this->product->selling_price, 'purchase_price' => $this->product->purchase_price]);
        $sale->delete();
        $stockBeforeRestore = $this->product->stock;

        $response = $this->actingAs($this->admin)->post(route('sales.restore', $sale->id));

        $response->assertRedirect(route('sales.index', ['status' => 'dibatalkan']));
        $response->assertSessionHas('success');
        $this->product->refresh();
        $this->assertEquals($stockBeforeRestore - $quantityToSell, $this->product->stock);
        $this->assertNotSoftDeleted('sales', ['id' => $sale->id]);
    }

    #[Test]
    public function it_can_create_sale_and_stock_is_unchanged_when_stock_management_is_disabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '0']);
        $initialStock = $this->product->stock;
        $quantityToSell = 5;
        $saleData = $this->createSaleData($quantityToSell);

        $response = $this->actingAs($this->user)->post(route('sales.store'), $saleData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('sales', ['customer_id' => $this->customer->id, 'payment_status' => 'Lunas']);
        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);
    }

    #[Test]
    public function stock_is_unchanged_when_sale_is_cancelled_and_stock_management_is_disabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '0']);
        $sale = Sale::factory()->create();
        $sale->details()->create(['product_id' => $this->product->id, 'quantity' => 10, 'sale_price' => $this->product->selling_price, 'purchase_price' => $this->product->purchase_price]);
        $stockBeforeCancellation = $this->product->stock;

        $response = $this->actingAs($this->admin)->delete(route('sales.cancel', $sale));

        $response->assertRedirect();
        $this->product->refresh();
        $this->assertEquals($stockBeforeCancellation, $this->product->stock);
        $this->assertSoftDeleted('sales', ['id' => $sale->id]);
    }

    #[Test]
    public function stock_is_unchanged_when_cancelled_sale_is_restored_and_stock_management_is_disabled(): void
    {
        Setting::where('key', 'enable_automatic_stock')->update(['value' => '0']);
        $sale = Sale::factory()->create();
        $sale->details()->create(['product_id' => $this->product->id, 'quantity' => 15, 'sale_price' => $this->product->selling_price, 'purchase_price' => $this->product->purchase_price]);
        $sale->delete();
        $stockBeforeRestore = $this->product->stock;

        $response = $this->actingAs($this->admin)->post(route('sales.restore', $sale->id));

        $response->assertRedirect();
        $this->product->refresh();
        $this->assertEquals($stockBeforeRestore, $this->product->stock);
        $this->assertNotSoftDeleted('sales', ['id' => $sale->id]);
    }
}