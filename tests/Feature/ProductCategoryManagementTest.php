<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
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
    public function guest_is_redirected_from_product_category_pages()
    {
        $this->get('/product-categories')->assertRedirect('/login');
        $this->get('/product-categories/create')->assertRedirect('/login');
        $this->get('/product-categories/1/edit')->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_product_category_pages()
    {
        $this->actingAs($this->regularUser);
        $category = ProductCategory::factory()->create();

        $this->get('/product-categories')->assertForbidden();
        $this->get('/product-categories/create')->assertForbidden();
        $this->post('/product-categories', ['name' => 'Test'])->assertForbidden();
        $this->get('/product-categories/' . $category->id . '/edit')->assertForbidden();
        $this->put('/product-categories/' . $category->id, ['name' => 'Test'])->assertForbidden();
        $this->delete('/product-categories/' . $category->id)->assertForbidden();
    }

    #[Test]
    public function admin_can_view_the_product_category_list_page()
    {
        $category = ProductCategory::factory()->create(['name' => 'Karung Baru']);

        $this->actingAs($this->adminUser)
            ->get('/product-categories')
            ->assertStatus(200)
            ->assertViewIs('product-categories.index')
            ->assertSee('Karung Baru');
    }

    #[Test]
    public function admin_can_view_the_create_product_category_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/product-categories/create')
            ->assertStatus(200)
            ->assertViewIs('product-categories.create');
    }

    #[Test]
    public function admin_can_create_a_new_product_category()
    {
        $this->actingAs($this->adminUser)
            ->post('/product-categories', ['name' => 'Karung Bekas'])
            ->assertRedirect('/product-categories')
            ->assertSessionHas('success', 'Kategori produk berhasil ditambahkan.');

        // PERBAIKAN: Gunakan nama tabel yang benar
        $this->assertDatabaseHas('karung_product_categories', ['name' => 'Karung Bekas']);
    }

    #[Test]
    public function admin_can_view_the_edit_product_category_page()
    {
        $category = ProductCategory::factory()->create();

        $this->actingAs($this->adminUser)
            ->get('/product-categories/' . $category->id . '/edit')
            ->assertStatus(200)
            ->assertViewIs('product-categories.edit')
            ->assertSee($category->name);
    }

    #[Test]
    public function admin_can_update_a_product_category()
    {
        $category = ProductCategory::factory()->create(['name' => 'Laminasi']);

        $this->actingAs($this->adminUser)
            ->put('/product-categories/' . $category->id, ['name' => 'Karung Laminasi'])
            ->assertRedirect('/product-categories')
            ->assertSessionHas('success', 'Kategori produk berhasil diperbarui.');

        // PERBAIKAN: Gunakan nama tabel yang benar
        $this->assertDatabaseHas('karung_product_categories', ['name' => 'Karung Laminasi']);
        $this->assertDatabaseMissing('karung_product_categories', ['name' => 'Laminasi']);
    }

    #[Test]
    public function admin_can_delete_a_product_category()
    {
        $category = ProductCategory::factory()->create();

        $this->actingAs($this->adminUser)
            ->delete('/product-categories/' . $category->id)
            ->assertRedirect('/product-categories')
            ->assertSessionHas('success', 'Kategori produk berhasil dihapus.');

        // PERBAIKAN: Gunakan nama tabel yang benar
        $this->assertDatabaseMissing('karung_product_categories', ['id' => $category->id]);
    }
}