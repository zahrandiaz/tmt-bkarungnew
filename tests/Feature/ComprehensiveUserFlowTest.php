<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleDetail; // [PERBAIKAN] Import namespace yang benar
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ComprehensiveUserFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

    public function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Tambahkan semua permission yang dibutuhkan
        Permission::create(['name' => 'product-view']);
        Permission::create(['name' => 'finance-crud-expense']);
        Permission::create(['name' => 'user-view']);
        Permission::create(['name' => 'transaction-view']);
        Permission::create(['name' => 'finance-view']);
        Permission::create(['name' => 'transaction-create']);
        Permission::create(['name' => 'report-view-all']);
        Permission::create(['name' => 'log-view']);

        // Create Roles
        $adminRole = Role::create(['name' => 'Admin']);
        $staffRole = Role::create(['name' => 'Staf']);

        $staffRole->givePermissionTo(['product-view', 'transaction-view']);
        $adminRole->syncPermissions(Permission::all());

        // Create Users
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
        
        $this->staff = User::factory()->create();
        $this->staff->assignRole($staffRole);
    }

    // ============== DATA PROVIDERS ==============
    public static function searchableModulesProvider(): array
    {
        return [
            'Products'    => ['model' => Product::class, 'routeName' => 'products.index', 'searchColumn' => 'name', 'uniqueKeyword' => 'Barang Unik XYZ', 'commonKeyword' => 'Produk Umum'],
            'Suppliers'   => ['model' => Supplier::class, 'routeName' => 'suppliers.index', 'searchColumn' => 'name', 'uniqueKeyword' => 'Supplier Langka', 'commonKeyword' => 'Supplier Biasa'],
            'Customers'   => ['model' => Customer::class, 'routeName' => 'customers.index', 'searchColumn' => 'name', 'uniqueKeyword' => 'Pelanggan VIP', 'commonKeyword' => 'Pelanggan Reguler'],
            'Expenses'    => ['model' => Expense::class, 'routeName' => 'expenses.index', 'searchColumn' => 'name', 'uniqueKeyword' => 'Biaya Tak Terduga', 'commonKeyword' => 'Biaya Rutin'],
            'Users'       => ['model' => User::class, 'routeName' => 'users.index', 'searchColumn' => 'name', 'uniqueKeyword' => 'Pengguna Spesial', 'commonKeyword' => 'Pengguna Umum'],
            'Stock Report' => ['model' => Product::class, 'routeName' => 'reports.stock', 'searchColumn' => 'name', 'uniqueKeyword' => 'Stok Item Langka', 'commonKeyword' => 'Stok Item Umum'],
        ];
    }

    public static function searchableRelationalModulesProvider(): array
    {
        return [
            'Sales by Customer'       => ['mainModel' => Sale::class, 'relationModel' => Customer::class, 'routeName' => 'sales.index', 'relationName' => 'customer', 'relationColumn' => 'name', 'uniqueKeyword' => 'Pelanggan Istimewa'],
            'Purchases by Supplier'   => ['mainModel' => Purchase::class, 'relationModel' => Supplier::class, 'routeName' => 'purchases.index', 'relationName' => 'supplier', 'relationColumn' => 'name', 'uniqueKeyword' => 'Supplier Utama'],
            'Receivables by Customer' => ['mainModel' => Sale::class, 'relationModel' => Customer::class, 'routeName' => 'receivables.index', 'relationName' => 'customer', 'relationColumn' => 'name', 'uniqueKeyword' => 'Debitur Spesial'],
            'Debts by Supplier'       => ['mainModel' => Purchase::class, 'relationModel' => Supplier::class, 'routeName' => 'debts.index', 'relationName' => 'supplier', 'relationColumn' => 'name', 'uniqueKeyword' => 'Kreditur Unggulan'],
        ];
    }

    // ============== PILAR 1: UI/UX TESTS ==============
    #[Test]
    #[DataProvider('searchableModulesProvider')]
    public function search_and_pagination_on_standard_pages_works_correctly(string $model, string $routeName, string $searchColumn, string $uniqueKeyword, string $commonKeyword)
    {
        $model::factory(12)->create([$searchColumn => $commonKeyword . ' ' . Str::random(5)]);
        $model::factory()->create([$searchColumn => $uniqueKeyword]);

        $response = $this->actingAs($this->admin)->get(route($routeName, ['search' => $uniqueKeyword]));
        $response->assertStatus(200)->assertSee($uniqueKeyword)->assertDontSee($commonKeyword);

        $response = $this->actingAs($this->admin)->get(route($routeName, ['search' => $commonKeyword, 'page' => 2]));
        $response->assertStatus(200)->assertSee($commonKeyword)->assertSee('value="' . $commonKeyword . '"', false);
    }

    #[Test]
    #[DataProvider('searchableRelationalModulesProvider')]
    public function search_and_pagination_on_relational_pages_works_correctly(string $mainModel, string $relationModel, string $routeName, string $relationName, string $relationColumn, string $uniqueKeyword)
    {
        $uniqueRelatedModel = $relationModel::factory()->create([$relationColumn => $uniqueKeyword]);
        $mainModel::factory()->create([$relationName . '_id' => $uniqueRelatedModel->id]);

        $commonRelatedModel = $relationModel::factory()->create([$relationColumn => 'Common Relation']);
        $mainModel::factory(20)->create([$relationName . '_id' => $commonRelatedModel->id]);

        $response = $this->actingAs($this->admin)->get(route($routeName, ['search' => $uniqueKeyword]));
        $response->assertStatus(200)->assertSee($uniqueKeyword)->assertDontSee('Common Relation');
    }

    #[Test]
    public function search_and_pagination_on_activity_log_page_works_correctly()
    {
        activity()->log('Log unik yang akan dicari');
        for ($i = 0; $i < 25; $i++) {
            activity()->log('Log umum untuk paginasi');
        }

        $response = $this->actingAs($this->admin)->get(route('activity-log.index', ['search' => 'unik']));
        $response->assertStatus(200)->assertSee('Log unik yang akan dicari')->assertDontSee('Log umum untuk paginasi');
        
        $response = $this->actingAs($this->admin)->get(route('activity-log.index', ['search' => 'umum', 'page' => 2]));
        $response->assertStatus(200)->assertSee('Log umum untuk paginasi')->assertSee('value="umum"', false);
    }

    #[Test]
    public function it_can_process_a_dynamic_sale_form_submission()
    {
        $customer = Customer::factory()->create();
        $productA = Product::factory()->create(['selling_price' => 10000, 'stock' => 100]);
        $productB = Product::factory()->create(['selling_price' => 25000, 'stock' => 100]);

        $this->actingAs($this->admin)->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'sale_date' => now()->format('Y-m-d\TH:i'),
            'payment_status' => 'lunas',
            'payment_method' => 'tunai',
            'total_amount' => 95000,
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 2, 'sale_price' => 10000],
                ['product_id' => $productB->id, 'quantity' => 3, 'sale_price' => 25000],
            ],
        ]);

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_details', 2);
        $sale = Sale::first();
        $this->assertEquals(95000, $sale->total_amount);
    }
    
    #[Test]
    public function it_can_process_a_dynamic_purchase_form_submission()
    {
        $supplier = Supplier::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $this->actingAs($this->admin)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->format('Y-m-d\TH:i'),
            'payment_status' => 'lunas',
            'payment_method' => 'tunai',
            'total_amount' => 110000,
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 5, 'purchase_price' => 10000],
                ['product_id' => $productB->id, 'quantity' => 4, 'purchase_price' => 15000],
            ],
        ]);

        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('purchase_details', 2);
        $purchase = Purchase::first();
        $this->assertEquals(110000, $purchase->total_amount);
    }

    // ============== PILAR 2: SECURITY TESTS ==============
    #[Test]
    public function it_hides_admin_only_ui_elements_from_staff_user()
    {
        $response = $this->actingAs($this->staff)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Manajemen Pengguna');
        $response->assertDontSee(route('users.index'));
    }

    #[Test]
    public function it_blocks_direct_route_access_for_unauthorized_users()
    {
        $response = $this->actingAs($this->staff)->get(route('users.index'));
        $response->assertStatus(403);
    }
    
    // ============== PILAR 3: API ENDPOINT TESTS ==============
    #[Test]
    public function it_validates_api_endpoints_integrity()
    {
        // 1. Arrange
        $product = Product::factory()->create(['name' => 'Produk API Test']);
        // [PERBAIKAN] Gunakan namespace yang benar
        $sale = Sale::factory()->has(SaleDetail::factory()->count(1), 'details')->create();

        // 2. Act & Assert: Test successful search
        $this->actingAs($this->admin)
            ->getJson(route('api.products.search', ['q' => 'API Test']))
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'Produk API Test']);

        // 3. Act & Assert: Test successful details fetch
        $this->actingAs($this->admin)
            ->getJson(route('api.reports.sale-details', ['id' => $sale->id]))
            ->assertStatus(200)
            ->assertJsonCount(1);

        // 4. Act & Assert: Test not found case
        $this->actingAs($this->admin)
            ->getJson(route('api.reports.sale-details', ['id' => 9999]))
            ->assertStatus(404);
    }
}