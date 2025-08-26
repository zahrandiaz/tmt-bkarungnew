<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;
    private ProductCategory $category;
    private ProductType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Admin');
        $this->regularUser = User::factory()->create();

        // Membuat data master yang diperlukan untuk produk
        $this->category = ProductCategory::factory()->create();
        $this->type = ProductType::factory()->create();
    }

    #[Test]
    public function guest_is_redirected_from_product_pages()
    {
        $this->get('/products')->assertRedirect('/login');
        $this->get('/products/create')->assertRedirect('/login');
        $this->get('/products/1/edit')->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_product_pages()
    {
        $this->actingAs($this->regularUser);
        $product = Product::factory()->create();

        $this->get('/products')->assertForbidden();
        $this->get('/products/create')->assertForbidden();
        $this->post('/products', [])->assertForbidden();
        $this->get('/products/' . $product->id . '/edit')->assertForbidden();
        $this->put('/products/' . $product->id, [])->assertForbidden();
        $this->delete('/products/' . $product->id)->assertForbidden();
    }

    #[Test]
    public function admin_can_view_the_product_list_page()
    {
        $product = Product::factory()->create(['name' => 'Produk Tes 123']);

        $this->actingAs($this->adminUser)
            ->get('/products')
            ->assertStatus(200)
            ->assertViewIs('products.index')
            ->assertSee('Produk Tes 123');
    }

    #[Test]
    public function admin_can_view_the_create_product_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/products/create')
            ->assertStatus(200)
            ->assertViewIs('products.create');
    }

    #[Test]
    public function admin_can_create_a_new_product()
    {
        $productData = [
            'name' => 'Karung 50kg Super',
            'sku' => 'KR-50-SP',
            'product_category_id' => $this->category->id,
            'product_type_id' => $this->type->id,
            'purchase_price' => 1500,
            'selling_price' => 2000,
            'stock' => 100,
            'min_stock_level' => 10,
        ];

        $this->actingAs($this->adminUser)
            ->post('/products', $productData)
            ->assertRedirect('/products')
            ->assertSessionHas('success', 'Produk berhasil ditambahkan.');

        $this->assertDatabaseHas('karung_products', ['sku' => 'KR-50-SP']);
    }

    #[Test]
    public function admin_can_view_the_edit_product_page()
    {
        $product = Product::factory()->create();

        $this->actingAs($this->adminUser)
            ->get('/products/' . $product->id . '/edit')
            ->assertStatus(200)
            ->assertViewIs('products.edit')
            ->assertSee($product->name);
    }

    #[Test]
    public function admin_can_update_a_product()
    {
        $product = Product::factory()->create();
        $updatedData = [
            'name' => 'Karung 25kg Istimewa',
            'sku' => 'KR-25-IST',
            'product_category_id' => $this->category->id,
            'product_type_id' => $this->type->id,
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'stock' => 50,
            'min_stock_level' => 5,
        ];

        $this->actingAs($this->adminUser)
            ->put('/products/' . $product->id, $updatedData)
            ->assertRedirect('/products')
            ->assertSessionHas('success', 'Produk berhasil diperbarui.');

        $this->assertDatabaseHas('karung_products', ['sku' => 'KR-25-IST']);
    }

    #[Test]
    public function admin_can_delete_a_product()
    {
        $product = Product::factory()->create();

        $this->actingAs($this->adminUser)
            ->delete('/products/' . $product->id)
            ->assertRedirect('/products')
            ->assertSessionHas('success', 'Produk berhasil dihapus.');

        $this->assertDatabaseMissing('karung_products', ['id' => $product->id]);
    }
}