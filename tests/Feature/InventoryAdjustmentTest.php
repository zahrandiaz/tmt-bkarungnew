<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->manager = User::factory()->create();
        $managerRole = Role::findByName('Manager');

        $permissions = ['adjustment-stock', 'adjustment-price'];
        foreach ($permissions as $permissionName) {
            if ($permission = Permission::where('name', $permissionName)->first()) {
                $managerRole->givePermissionTo($permission);
            }
        }
        $this->manager->assignRole($managerRole);

        $this->productA = Product::factory()->create(['stock' => 100, 'selling_price' => 5000]);
        $this->productB = Product::factory()->create(['stock' => 50, 'selling_price' => 10000]);
    }

    #[Test]
    public function user_can_perform_stock_adjustment(): void
    {
        $initialStock = $this->productA->stock;
        // FIX: Sesuaikan payload dengan Form Request
        $adjustmentData = ['product_id' => $this->productA->id, 'type' => 'increment', 'quantity' => 25, 'reason' => 'Penambahan stok opname'];

        $this->actingAs($this->manager)->post(route('stock-adjustments.store'), $adjustmentData)->assertRedirect(route('stock-adjustments.index'));
        $this->productA->refresh();
        $this->assertEquals($initialStock + 25, $this->productA->stock);

        $currentStock = $this->productA->stock;
        // FIX: Sesuaikan payload dengan Form Request
        $adjustmentDataReduce = ['product_id' => $this->productA->id, 'type' => 'decrement', 'quantity' => 10, 'reason' => 'Barang rusak'];

        $this->actingAs($this->manager)->post(route('stock-adjustments.store'), $adjustmentDataReduce)->assertRedirect(route('stock-adjustments.index'));
        $this->productA->refresh();
        $this->assertEquals($currentStock - 10, $this->productA->stock);
    }

    #[Test]
    public function user_can_perform_bulk_price_adjustment(): void
    {
        // FIX: Sesuaikan struktur payload dengan Form Request
        $updateData = [
            'products' => [
                ['id' => $this->productA->id, 'selling_price' => 5500],
                ['id' => $this->productB->id, 'selling_price' => 12000],
            ],
        ];

        $this->actingAs($this->manager)->post(route('price-adjustments.store'), $updateData)->assertRedirect(route('price-adjustments.index'));
        $this->productA->refresh();
        $this->productB->refresh();
        $this->assertEquals(5500, $this->productA->selling_price);
        $this->assertEquals(12000, $this->productB->selling_price);
    }
}