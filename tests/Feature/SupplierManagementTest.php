<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Admin');
        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function guest_is_redirected_from_supplier_pages()
    {
        $this->get('/suppliers')->assertRedirect('/login');
        $this->get('/suppliers/create')->assertRedirect('/login');
        $this->get('/suppliers/1/edit')->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_supplier_pages()
    {
        $this->actingAs($this->regularUser);
        $supplier = Supplier::factory()->create();

        $this->get('/suppliers')->assertForbidden();
        $this->get('/suppliers/create')->assertForbidden();
        $this->post('/suppliers', [])->assertForbidden();
        $this->get('/suppliers/' . $supplier->id . '/edit')->assertForbidden();
        $this->put('/suppliers/' . $supplier->id, [])->assertForbidden();
        $this->delete('/suppliers/' . $supplier->id)->assertForbidden();
    }

    #[Test]
    public function admin_can_view_the_supplier_list_page()
    {
        $supplier = Supplier::factory()->create(['name' => 'Supplier Jaya Abadi']);

        $this->actingAs($this->adminUser)
            ->get('/suppliers')
            ->assertStatus(200)
            ->assertViewIs('suppliers.index')
            ->assertSee('Supplier Jaya Abadi');
    }

    #[Test]
    public function admin_can_view_the_create_supplier_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/suppliers/create')
            ->assertStatus(200)
            ->assertViewIs('suppliers.create');
    }

    #[Test]
    public function admin_can_create_a_new_supplier()
    {
        $supplierData = [
            'name' => 'Sumber Rejeki',
            'phone' => '081234567890',
            'address' => 'Jl. Makmur No. 10',
        ];

        $this->actingAs($this->adminUser)
            ->post('/suppliers', $supplierData)
            ->assertRedirect('/suppliers')
            ->assertSessionHas('success', 'Supplier berhasil ditambahkan.');

        $this->assertDatabaseHas('suppliers', ['name' => 'Sumber Rejeki']);
    }

    #[Test]
    public function admin_can_view_the_edit_supplier_page()
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->adminUser)
            ->get('/suppliers/' . $supplier->id . '/edit')
            ->assertStatus(200)
            ->assertViewIs('suppliers.edit')
            ->assertSee($supplier->name);
    }

    #[Test]
    public function admin_can_update_a_supplier()
    {
        $supplier = Supplier::factory()->create();
        $updatedData = [
            'name' => 'Berkah Selalu',
            'phone' => '089876543210',
            'address' => 'Jl. Sejahtera No. 20',
        ];

        $this->actingAs($this->adminUser)
            ->put('/suppliers/' . $supplier->id, $updatedData)
            ->assertRedirect('/suppliers')
            // [PERBAIKAN] Sesuaikan teks agar cocok dengan controller
            ->assertSessionHas('success', 'Data supplier berhasil diperbarui.');

        $this->assertDatabaseHas('suppliers', ['name' => 'Berkah Selalu']);
    }

    #[Test]
    public function admin_can_delete_a_supplier()
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->adminUser)
            ->delete('/suppliers/' . $supplier->id)
            ->assertRedirect('/suppliers')
            ->assertSessionHas('success', 'Supplier berhasil dihapus.');

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}