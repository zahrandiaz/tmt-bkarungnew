<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->manager = User::factory()->create();
        $managerRole = Role::findByName('Manager');

        $permissions = ['finance-view', 'finance-manage-payment', 'finance-crud-expense'];
        foreach ($permissions as $permissionName) {
            if ($permission = Permission::where('name', $permissionName)->first()) {
                $managerRole->givePermissionTo($permission);
            }
        }
        $this->manager->assignRole($managerRole);
    }

    #[Test]
    public function user_can_add_payment_to_receivable_and_it_becomes_paid_off(): void
    {
        $sale = Sale::factory()->create(['total_amount' => 100000, 'payment_status' => 'Belum Lunas', 'down_payment' => 20000, 'total_paid' => 20000]);
        $sale->payments()->create(['amount' => 20000, 'payment_date' => now(), 'user_id' => $this->admin->id]);

        $paymentData = ['payment_date' => now()->format('Y-m-d'), 'amount' => 80000, 'payment_method' => 'transfer', 'notes' => 'Pelunasan piutang'];

        $response = $this->actingAs($this->manager)->post(route('receivables.payments.store', $sale), $paymentData);

        $response->assertRedirect(route('receivables.index', ['status' => 'lunas']));
        $response->assertSessionHas('success');
        $sale->refresh();
        $this->assertEquals(100000, $sale->total_paid);
        $this->assertEquals('Lunas', $sale->payment_status);
    }

    #[Test]
    public function user_can_add_payment_to_debt_and_it_becomes_paid_off(): void
    {
        $purchase = Purchase::factory()->create(['total_amount' => 500000, 'payment_status' => 'Belum Lunas', 'down_payment' => 100000, 'total_paid' => 100000]);
        $purchase->payments()->create(['amount' => 100000, 'payment_date' => now(), 'user_id' => $this->admin->id]);

        $paymentData = ['payment_date' => now()->format('Y-m-d'), 'amount' => 400000, 'payment_method' => 'transfer', 'notes' => 'Pelunasan utang'];

        $response = $this->actingAs($this->manager)->post(route('debts.payments.store', $purchase), $paymentData);

        $response->assertRedirect(route('debts.index', ['status' => 'lunas']));
        $response->assertSessionHas('success');
        $purchase->refresh();
        $this->assertEquals(500000, $purchase->total_paid);
        $this->assertEquals('Lunas', $purchase->payment_status);
    }

    #[Test]
    public function user_can_perform_crud_on_expenses(): void
    {
        $category = ExpenseCategory::factory()->create();

        // FINAL FIX: Tambahkan field 'name' yang wajib diisi
        $expenseData = [
            'name' => 'Biaya Listrik',
            'expense_date' => now()->format('Y-m-d'),
            'expense_category_id' => $category->id,
            'amount' => 50000,
            'description' => 'Biaya listrik bulan ini'
        ];
        $this->actingAs($this->manager)->post(route('expenses.store'), $expenseData)->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', ['description' => 'Biaya listrik bulan ini']);

        $expense = Expense::latest()->first();

        $updatedData = [
            'name' => 'Biaya Listrik (Revisi)',
            'expense_date' => now()->format('Y-m-d'),
            'expense_category_id' => $category->id,
            'amount' => 55000,
            'description' => 'Biaya listrik bulan ini (revisi)'
        ];
        $this->actingAs($this->manager)->put(route('expenses.update', $expense), $updatedData)->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', ['name' => 'Biaya Listrik (Revisi)']);

        $this->actingAs($this->manager)->delete(route('expenses.destroy', $expense))->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}