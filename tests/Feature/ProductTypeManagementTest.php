<?php

namespace Tests\Feature;

use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTypeManagementTest extends TestCase
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
    public function guest_is_redirected_from_product_type_pages()
    {
        $this->get('/product-types')->assertRedirect('/login');
        $this->get('/product-types/create')->assertRedirect('/login');
        $this->get('/product-types/1/edit')->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_product_type_pages()
    {
        $this->actingAs($this->regularUser);
        $type = ProductType::factory()->create();

        $this->get('/product-types')->assertForbidden();
        $this->get('/product-types/create')->assertForbidden();
        $this->post('/product-types', ['name' => 'Test'])->assertForbidden();
        $this->get('/product-types/' . $type->id . '/edit')->assertForbidden();
        $this->put('/product-types/' . $type->id, ['name' => 'Test'])->assertForbidden();
        $this->delete('/product-types/' . $type->id)->assertForbidden();
    }

    #[Test]
    public function admin_can_view_the_product_type_list_page()
    {
        $type = ProductType::factory()->create(['name' => 'Polos']);

        $this->actingAs($this->adminUser)
            ->get('/product-types')
            ->assertStatus(200)
            ->assertViewIs('product-types.index')
            ->assertSee('Polos');
    }

    #[Test]
    public function admin_can_view_the_create_product_type_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/product-types/create')
            ->assertStatus(200)
            ->assertViewIs('product-types.create');
    }

    #[Test]
    public function admin_can_create_a_new_product_type()
    {
        $this->actingAs($this->adminUser)
            ->post('/product-types', ['name' => 'Transparan'])
            ->assertRedirect('/product-types')
            ->assertSessionHas('success', 'Jenis produk berhasil ditambahkan.');

        $this->assertDatabaseHas('karung_product_types', ['name' => 'Transparan']);
    }

    #[Test]
    public function admin_can_view_the_edit_product_type_page()
    {
        $type = ProductType::factory()->create();

        $this->actingAs($this->adminUser)
            ->get('/product-types/' . $type->id . '/edit')
            ->assertStatus(200)
            ->assertViewIs('product-types.edit')
            ->assertSee($type->name);
    }

    #[Test]
    public function admin_can_update_a_product_type()
    {
        $type = ProductType::factory()->create(['name' => 'Printing']);

        $this->actingAs($this->adminUser)
            ->put('/product-types/' . $type->id, ['name' => 'Sablon'])
            ->assertRedirect('/product-types')
            ->assertSessionHas('success', 'Jenis produk berhasil diperbarui.');

        $this->assertDatabaseHas('karung_product_types', ['name' => 'Sablon']);
        $this->assertDatabaseMissing('karung_product_types', ['name' => 'Printing']);
    }

    #[Test]
    public function admin_can_delete_a_product_type()
    {
        $type = ProductType::factory()->create();

        $this->actingAs($this->adminUser)
            ->delete('/product-types/' . $type->id)
            ->assertRedirect('/product-types')
            ->assertSessionHas('success', 'Jenis produk berhasil dihapus.');

        $this->assertDatabaseMissing('karung_product_types', ['id' => $type->id]);
    }
}