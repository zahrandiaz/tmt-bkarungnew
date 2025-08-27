<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;
use Pest\Faker\Faker;
use PHPUnit\Framework\Attributes\Test;

class SystemHealthTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the necessary data for tests
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(SettingSeeder::class);

        // Get the admin user
        $this->admin = User::where('name', 'Admin TMT')->first();
        $this->assertNotNull($this->admin, "Admin user not found.");
    }

    #[Test]
    public function admin_can_access_dashboard_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    #[Test]
    public function admin_can_access_all_report_pages_without_errors(): void
    {
        $reportRoutes = [
            'reports.sales',
            'reports.purchases',
            'reports.stock',
            'reports.profit-loss',
            'reports.deposits',
            'reports.cash-flow',
        ];

        foreach ($reportRoutes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertStatus(200, "Failed to access route: {$route}");
        }
    }

    #[Test]
    public function admin_can_export_all_main_reports_without_errors(): void
    {
        // [TAMBAHAN BARU] Tes untuk semua rute ekspor utama
        $exportRoutes = [
            'reports.sales.export.csv',
            'reports.sales.export.pdf',
            'reports.purchases.export.csv',
            'reports.purchases.export.pdf',
            'reports.stock.export', // Asumsi ini untuk CSV
            'reports.stock.export.pdf',
            'reports.deposits.export.csv',
            'reports.deposits.export.pdf',
            'reports.profit-loss.export.csv',
            'reports.profit-loss.export.pdf',
        ];

        foreach ($exportRoutes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertStatus(200, "Failed to export route: {$route}");
        }
    }

    #[Test]
    public function admin_can_access_and_manage_activity_log(): void
    {
        // 1. Test access to the index page
        $this->actingAs($this->admin)->get(route('activity-log.index'))
            ->assertStatus(200)
            ->assertViewIs('activity_log.index');
        
        Activity::query()->delete();

        // 2. Create a dummy log to ensure the table is not empty
        activity()->log('Test log entry');
        $this->assertDatabaseCount('activity_log', 1);

        // 3. Test the reset functionality
        $this->actingAs($this->admin)->delete(route('activity-log.reset'))
            ->assertRedirect(route('activity-log.index'))
            ->assertSessionHas('success');

        // 4. Verify that the log has been cleared
        $this->assertDatabaseCount('activity_log', 0);
    }


    #[Test]
    public function admin_can_access_and_update_settings(): void
    {
        // 1. Test access to the settings page
        $this->actingAs($this->admin)->get(route('settings.index'))
            ->assertStatus(200)
            ->assertViewIs('settings.index');

        // 2. Prepare the payload for updating settings
        $payload = [
            'store_name' => 'Toko Karung Baru',
            'store_address' => 'Jalan Perubahan No. 123',
            'store_phone' => '081234567890',
            'invoice_footer_notes' => 'Terima kasih telah berbelanja!',
            'enable_automatic_stock' => '1',
        ];

        // 3. Test the update functionality
        $this->actingAs($this->admin)->post(route('settings.update'), $payload)
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success');

        // 4. Verify that the settings have been updated in the database
        $this->assertDatabaseHas('settings', [
            'key' => 'store_name',
            'value' => 'Toko Karung Baru'
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'enable_automatic_stock',
            'value' => '1'
        ]);
    }
}