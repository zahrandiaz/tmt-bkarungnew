<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
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
    public function guest_is_redirected_from_role_management_pages()
    {
        $this->get('/roles')->assertRedirect('/login');
        $this->get('/roles/create')->assertRedirect('/login');
        $this->get('/roles/1/edit')->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_role_management_pages()
    {
        $this->actingAs($this->regularUser);
        $role = Role::first();

        $this->get('/roles')->assertForbidden();
        $this->get('/roles/create')->assertForbidden();
        $this->post('/roles')->assertForbidden();
        $this->get('/roles/' . $role->id . '/edit')->assertForbidden();
        $this->put('/roles/' . $role->id)->assertForbidden();
        $this->delete('/roles/' . $role->id)->assertForbidden();
    }

    #[Test]
    public function admin_can_view_the_role_list_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/roles')
            ->assertStatus(200)
            ->assertViewIs('roles.index')
            ->assertSee('Admin');
    }

    #[Test]
    public function admin_can_view_the_create_role_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/roles/create')
            ->assertStatus(200)
            ->assertViewIs('roles.create');
    }

    #[Test]
    public function admin_can_create_a_new_role_with_permissions()
    {
        $permission = Permission::where('name', 'user-view')->first();
        $roleData = [
            'name' => 'Operator',
            'permissions' => [$permission->id],
        ];

        $this->actingAs($this->adminUser)
            ->post('/roles', $roleData)
            ->assertRedirect('/roles')
            ->assertSessionHas('success', 'Peran baru berhasil ditambahkan.'); // DISESUAIKAN

        $this->assertDatabaseHas('roles', ['name' => 'Operator']);
        $this->assertTrue(Role::findByName('Operator')->hasPermissionTo('user-view'));
    }

    #[Test]
    public function admin_can_view_the_edit_role_page()
    {
        $role = Role::findByName('Staf');

        $this->actingAs($this->adminUser)
            ->get('/roles/' . $role->id . '/edit')
            ->assertStatus(200)
            ->assertViewIs('roles.edit')
            ->assertSee($role->name);
    }

    #[Test]
    public function admin_can_update_a_role()
    {
        $role = Role::findByName('Staf');
        $permission = Permission::where('name', 'product-delete')->first();
        $updatedData = [
            'name' => 'Staf Gudang',
            'permissions' => [$permission->id],
        ];

        $this->actingAs($this->adminUser)
            ->put('/roles/' . $role->id, $updatedData)
            ->assertRedirect('/roles')
            ->assertSessionHas('success', 'Peran berhasil diperbarui.');

        $this->assertDatabaseHas('roles', ['name' => 'Staf Gudang']);
        $this->assertTrue($role->fresh()->hasPermissionTo('product-delete'));
    }

    #[Test]
    public function admin_cannot_delete_the_admin_role()
    {
        $adminRole = Role::findByName('Admin');

        $this->actingAs($this->adminUser)
            ->delete('/roles/' . $adminRole->id)
            ->assertRedirect('/roles')
            ->assertSessionHas('error', 'Peran inti tidak dapat dihapus.'); // DISESUAIKAN

        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
    }

    #[Test]
    public function admin_can_delete_another_role()
    {
        $roleToDelete = Role::create(['name' => 'Temporary Role']);

        $this->actingAs($this->adminUser)
            ->delete('/roles/' . $roleToDelete->id)
            ->assertRedirect('/roles')
            ->assertSessionHas('success', 'Peran berhasil dihapus.');

        $this->assertDatabaseMissing('roles', ['name' => 'Temporary Role']);
    }
}