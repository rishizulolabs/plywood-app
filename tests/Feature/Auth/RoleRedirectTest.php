<?php

namespace Tests\Feature\Auth;

use App\Models\DistributorProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_customer_registration_redirects_to_customer_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'customer',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->hasRole('customer'));
    }

    public function test_distributor_registration_redirects_to_distributor_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Distributor',
            'email' => 'distributor@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'distributor',
            'business_name' => 'Test Ply Co',
        ]);

        $response->assertRedirect(route('distributor.dashboard'));
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->hasRole('distributor'));
        $this->assertDatabaseHas('distributor_profiles', [
            'business_name' => 'Test Ply Co',
            'is_approved' => false,
        ]);
    }

    public function test_admin_login_redirects_to_filament_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_customer_cannot_access_distributor_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $response = $this->actingAs($user)->get(route('distributor.dashboard'));

        $response->assertForbidden();
    }

    public function test_distributor_cannot_access_customer_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('distributor');
        DistributorProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Dist Co',
        ]);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertForbidden();
    }
}
