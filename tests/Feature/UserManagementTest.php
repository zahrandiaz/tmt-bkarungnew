<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
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
    public function guest_is_redirected_from_user_management_pages()
    {
        $this->get('/users')->assertRedirect('/login');
        $this->get('/users/create')->assertRedirect('/login');
        $this->get('/users/1/edit')->assertRedirect('/login');
    }

    #[Test]
    public function regular_user_cannot_access_user_management_pages()
    {
        $this->actingAs($this->regularUser);

        $this->get('/users')->assertForbidden();
        $this->get('/users/create')->assertForbidden();
        $this->post('/users')->assertForbidden();
        $this->get('/users/' . $this->adminUser->id . '/edit')->assertForbidden();
        $this->put('/users/' . $this->adminUser->id)->assertForbidden();
        $this->delete('/users/' . $this->adminUser->id)->assertForbidden();
    }

    #[Test]
    public function admin_can_view_the_user_list_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/users')
            ->assertStatus(200)
            ->assertViewIs('users.index')
            ->assertSee($this->regularUser->name);
    }

    #[Test]
    public function admin_can_view_the_create_user_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/users/create')
            ->assertStatus(200)
            ->assertViewIs('users.create');
    }

    #[Test]
    public function admin_can_create_a_new_user()
    {
        $this->actingAs($this->adminUser);
        $role = Role::findByName('Staf');
        $userData = [
            'name' => 'New Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => $role->id,
        ];

        $response = $this->post('/users', $userData);

        $response->assertRedirect('/users')
            ->assertSessionHas('success', 'Pengguna berhasil ditambahkan.'); // DISESUAIKAN

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $this->assertTrue(User::whereEmail('newuser@example.com')->first()->hasRole('Staf'));
    }

    #[Test]
    public function admin_can_view_the_edit_user_page()
    {
        $this->actingAs($this->adminUser)
            ->get('/users/' . $this->regularUser->id . '/edit')
            ->assertStatus(200)
            ->assertViewIs('users.edit')
            ->assertSee($this->regularUser->name);
    }

    #[Test]
    public function admin_can_update_a_user()
    {
        $this->actingAs($this->adminUser);
        $role = Role::findByName('Manager');
        $updatedData = [
            'name' => 'Updated User Name',
            'email' => $this->regularUser->email,
            'role' => $role->id,
        ];

        $response = $this->put('/users/' . $this->regularUser->id, $updatedData);

        $response->assertRedirect('/users')
            ->assertSessionHas('success', 'Data pengguna berhasil diperbarui.'); // DISESUAIKAN

        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'name' => 'Updated User Name',
        ]);
        $this->assertTrue($this->regularUser->fresh()->hasRole('Manager'));
    }

    #[Test]
    public function admin_can_delete_a_user()
    {
        $userToDelete = User::factory()->create();

        $this->actingAs($this->adminUser)
            ->delete('/users/' . $userToDelete->id)
            ->assertRedirect('/users')
            ->assertSessionHas('success', 'Pengguna berhasil dihapus.'); // DISESUAIKAN

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }
}