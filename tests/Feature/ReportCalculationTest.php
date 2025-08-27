<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ReportCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(SettingSeeder::class);
        $this->admin = User::where('name', 'Admin TMT')->first();
    }

    private function createTestScenario()
    {
        $product = Product::factory()->create([
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 100,
        ]);

        // Penjualan Lunas
        $salePaid = Sale::factory()->create([
            'payment_status' => 'Lunas',
            'total_amount' => 30000,
            'sale_date' => Carbon::now(),
        ]);
        $salePaid->details()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'sale_price' => 15000,
            'subtotal' => 30000,
            'purchase_price' => 10000,
        ]);
        $salePaid->payments()->create(['amount' => 30000, 'payment_date' => Carbon::now(), 'user_id' => $this->admin->id]);

        // Penjualan Kredit dengan DP
        $saleUnpaid = Sale::factory()->create([
            'payment_status' => 'Belum Lunas',
            'total_amount' => 15000,
            'down_payment' => 5000,
            'sale_date' => Carbon::now(),
        ]);
        $saleUnpaid->details()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'sale_price' => 15000,
            'subtotal' => 15000,
            'purchase_price' => 10000,
        ]);
        $saleUnpaid->payments()->create(['amount' => 5000, 'payment_date' => Carbon::now(), 'user_id' => $this->admin->id]);

        // Pembelian Lunas
        $purchase = Purchase::factory()->create([
            'payment_status' => 'Lunas',
            'total_amount' => 50000,
            'purchase_date' => Carbon::now(),
        ]);
        $purchase->payments()->create(['amount' => 50000, 'payment_date' => Carbon::now(), 'user_id' => $this->admin->id]);

        // Biaya Operasional
        Expense::factory()->create([
            'name' => 'Biaya Test',
            'amount' => 7500,
            'expense_date' => Carbon::now(),
        ]);
    }

    #[Test]
    public function it_calculates_sales_report_correctly(): void
    {
        $this->createTestScenario();
        $response = $this->actingAs($this->admin)->get(route('reports.sales'));

        $response->assertStatus(200);
        $this->assertEquals(45000, $response->viewData('totalRevenue'));
        $this->assertEquals(30000, $response->viewData('totalCogs')); // [PENYELARASAN] Menggunakan 'totalCogs'
        $this->assertEquals(15000, $response->viewData('grossProfit'));
    }

    #[Test]
    public function it_calculates_purchases_report_correctly(): void
    {
        $this->createTestScenario();
        $response = $this->actingAs($this->admin)->get(route('reports.purchases'));

        $response->assertStatus(200);
        $this->assertEquals(50000, $response->viewData('totalExpenditure')); // [PENYELARASAN] Menggunakan 'totalExpenditure'
    }

    #[Test]
    public function it_calculates_deposits_report_correctly(): void
    {
        $this->createTestScenario();
        $response = $this->actingAs($this->admin)->get(route('reports.deposits'));

        $response->assertStatus(200);
        $this->assertEquals(30000, $response->viewData('totalDeposit')); // [PENYELARASAN] Menggunakan 'totalDeposit'
    }

    #[Test]
    public function it_calculates_profit_and_loss_report_correctly(): void
    {
        $this->createTestScenario();
        $response = $this->actingAs($this->admin)->get(route('reports.profit-loss'));

        $response->assertStatus(200);
        $this->assertEquals(45000, $response->viewData('totalRevenue'));
        $this->assertEquals(30000, $response->viewData('totalCostOfGoods')); // [PENYELARASAN] Menggunakan 'totalCostOfGoods'
        $this->assertEquals(15000, $response->viewData('grossProfit'));
        $this->assertEquals(7500, $response->viewData('totalExpenses'));
        $this->assertEquals(7500, $response->viewData('netProfit'));
    }

    #[Test]
    public function it_calculates_cash_flow_report_correctly(): void
    {
        $this->createTestScenario();
        $response = $this->actingAs($this->admin)->get(route('reports.cash-flow'));

        $response->assertStatus(200);
        $this->assertEquals(35000, $response->viewData('totalInflow')); // [PENYELARASAN] Menggunakan 'totalInflow'
        $this->assertEquals(57500, $response->viewData('totalOutflow')); // [PENYELARASAN] Menggunakan 'totalOutflow'
        $this->assertEquals(-22500, $response->viewData('netCashFlow'));
    }
}