<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
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
    public function guest_is_redirected_from_customer_pages()
    {
        $this->get('/customers')->assertRedirect('/login');
        $this->get('/customers/create')->assertRedirect('/login');
        $this->get('/customers/1/edit')->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_customer_pages()
    {
        $this->actingAs($this->regularUser);
        $customer = Customer::factory()->create();

        $this->get('/customers')->assertForbidden();
        $this->get('/customers/create')->assertForbidden();
        $this->post('/customers', [])->assertForbidden();
        $this->get('/customers/' . $customer->id . '/edit')->assertForbidden();
        $this->put('/customers/' . $customer->id, [])->assertForbidden();
        $this->delete('/customers/' . $customer->id)->assertForbidden();
    }

    #[Test]
    public function admin_can_view_the_customer_list_page()
    {
        $customer = Customer::factory()->create(['name' => 'Pelanggan Setia']);

        $this->actingAs($this->adminUser)
            ->get('/customers')
            ->assertStatus(200)
            ->assertViewIs('customers.index')
            ->assertSee('Pelanggan Setia');
    }

    #[Test]
    public function admin_can_view_the_create_customer_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/customers/create')
            ->assertStatus(200)
            ->assertViewIs('customers.create');
    }

    #[Test]
    public function admin_can_create_a_new_customer()
    {
        $customerData = [
            'name' => 'Toko Barokah',
            'phone' => '081122334455',
            'address' => 'Jl. Pahlawan No. 1',
        ];

        $this->actingAs($this->adminUser)
            ->post('/customers', $customerData)
            ->assertRedirect('/customers')
            ->assertSessionHas('success', 'Pelanggan berhasil ditambahkan.');

        $this->assertDatabaseHas('customers', ['name' => 'Toko Barokah']);
    }

    #[Test]
    public function admin_can_view_the_edit_customer_page()
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->adminUser)
            ->get('/customers/' . $customer->id . '/edit')
            ->assertStatus(200)
            ->assertViewIs('customers.edit')
            ->assertSee($customer->name);
    }

    #[Test]
    public function admin_can_update_a_customer()
    {
        $customer = Customer::factory()->create();
        $updatedData = [
            'name' => 'Usaha Maju Jaya',
            'phone' => '085544332211',
            'address' => 'Jl. Kemerdekaan No. 45',
        ];

        $this->actingAs($this->adminUser)
            ->put('/customers/' . $customer->id, $updatedData)
            ->assertRedirect('/customers')
            ->assertSessionHas('success', 'Data pelanggan berhasil diperbarui.');

        $this->assertDatabaseHas('customers', ['name' => 'Usaha Maju Jaya']);
    }

    #[Test]
    public function admin_can_delete_a_customer()
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->adminUser)
            ->delete('/customers/' . $customer->id)
            ->assertRedirect('/customers')
            ->assertSessionHas('success', 'Pelanggan berhasil dihapus.');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}