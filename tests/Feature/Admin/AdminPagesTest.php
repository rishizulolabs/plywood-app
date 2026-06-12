<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_access_user_and_catalog_pages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $pages = [
            route('admin.users.index'),
            route('admin.customers.index'),
            route('admin.distributors.index'),
            route('admin.products.index'),
            route('admin.categories.index'),
            route('admin.settings.index'),
        ];

        foreach ($pages as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_save_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'support_email' => 'help@plywood.com',
                'support_phone' => '9999999999',
                'require_distributor_approval' => '1',
            ])
            ->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('help@plywood.com');
    }

    public function test_admin_can_add_search_edit_and_delete_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New User',
                'email' => 'newuser@plywood.com',
                'role' => 'customer',
                'phone' => '9000000001',
                'city' => 'Delhi',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $user = User::where('email', 'newuser@plywood.com')->first();
        $this->assertTrue($user->hasRole('customer'));

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'New User']))
            ->assertOk()
            ->assertSee('New User');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated User',
                'email' => 'newuser@plywood.com',
                'role' => 'customer',
                'phone' => '9000000002',
                'city' => 'Mumbai',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertEquals('Updated User', $user->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_add_search_edit_and_delete_product(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->seed(\Database\Seeders\CategoriesSeeder::class);

        $distributorUser = User::factory()->create(['name' => 'Product Dist']);
        $distributorUser->assignRole('distributor');
        $profile = \App\Models\DistributorProfile::create([
            'user_id' => $distributorUser->id,
            'business_name' => 'Product Dist',
            'is_approved' => true,
        ]);

        $category = \App\Models\Category::first();

        $payload = [
            'name' => 'CenturyPly BWP 18mm',
            'category_id' => $category->id,
            'distributor_profile_id' => $profile->id,
            'description' => 'Marine grade plywood',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'core_type' => 'Hardwood core',
            'number_of_plies' => '9 ply',
            'is_standard' => 'IS 710',
            'is_isi_marked' => '1',
            'brand' => 'CenturyPly',
            'warranty' => '15 years',
            'finish_surface' => 'Both side polished',
            'density' => '650',
            'termite_borer_treatment' => '1',
            'weight_per_sheet' => '30 kg',
            'application' => 'Furniture, kitchen cabinets',
            'glue_type' => 'Phenol Formaldehyde (PF)',
            'country_of_origin' => 'India',
            'min_order_qty' => 10,
            'unit' => 'sheet',
            'in_stock' => '1',
            'is_featured' => '1',
        ];

        $this->actingAs($admin)
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $product = \App\Models\Product::where('name', 'CenturyPly BWP 18mm')->first();
        $this->assertNotNull($product);
        $this->assertEquals('BWP', $product->grade);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['search' => 'CenturyPly']))
            ->assertOk()
            ->assertSee('CenturyPly BWP 18mm');

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), array_merge($payload, [
                'name' => 'CenturyPly BWP 19mm',
                'thickness' => '19mm',
            ]))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertEquals('19mm', $product->fresh()->thickness);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_admin_can_add_search_edit_and_delete_category(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Shuttering Ply',
                'description' => 'For concrete shuttering work',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $category = \App\Models\Category::where('name', 'Shuttering Ply')->first();
        $this->assertNotNull($category);

        $this->actingAs($admin)
            ->get(route('admin.categories.index', ['search' => 'Shuttering']))
            ->assertOk()
            ->assertSee('Shuttering Ply');

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Shuttering Board',
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertEquals('shuttering-board', $category->fresh()->slug);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_can_add_order_with_stats_on_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->seed(\Database\Seeders\CategoriesSeeder::class);

        $customer = User::factory()->create([
            'name' => 'Order Customer',
            'city' => 'Delhi',
            'pincode' => '110001',
        ]);
        $customer->assignRole('customer');

        $distributorUser = User::factory()->create(['name' => 'Order Dist']);
        $distributorUser->assignRole('distributor');
        $profile = \App\Models\DistributorProfile::create([
            'user_id' => $distributorUser->id,
            'business_name' => 'Order Dist',
            'is_approved' => true,
        ]);

        $product = \App\Models\Product::create([
            'distributor_profile_id' => $profile->id,
            'category_id' => \App\Models\Category::first()->id,
            'name' => 'Test Ply 18mm',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'core_type' => 'Hardwood core',
            'number_of_plies' => '9 ply',
            'is_standard' => 'IS 710',
            'brand' => 'CenturyPly',
            'warranty' => '15 years',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Total orders')
            ->assertSee('Completed')
            ->assertSee('Pending')
            ->assertDontSee('Add Order');

        $this->actingAs($admin)
            ->post(route('admin.orders.store'), [
                'customer_id' => $customer->id,
                'distributor_profile_id' => $profile->id,
                'product_id' => $product->id,
                'quantity' => 5,
                'total_amount' => 25000,
                'delivery_address' => '123 Market Road, Delhi',
                'payment_status' => 'pending',
                'fulfillment_status' => 'processing',
            ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'distributor_profile_id' => $profile->id,
            'total_amount' => 25000,
        ]);
    }

    public function test_admin_can_add_customer(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $distributorUser = User::factory()->create(['name' => 'Dist Partner']);
        $distributorUser->assignRole('distributor');

        $profile = \App\Models\DistributorProfile::create([
            'user_id' => $distributorUser->id,
            'business_name' => 'Dist Partner',
            'is_approved' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.customers.store'), [
                'name' => 'New Customer',
                'email' => 'newcustomer@plywood.com',
                'phone' => '9988776655',
                'city' => 'Noida',
                'distributor_profile_id' => $profile->id,
            ])
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newcustomer@plywood.com',
            'phone' => '9988776655',
            'city' => 'Noida',
            'distributor_profile_id' => $profile->id,
        ]);
    }

    public function test_admin_can_search_edit_and_delete_customer(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $distributorUser = User::factory()->create(['name' => 'Aman Dist']);
        $distributorUser->assignRole('distributor');
        $profile = \App\Models\DistributorProfile::create([
            'user_id' => $distributorUser->id,
            'business_name' => 'Aman Dist',
            'is_approved' => true,
        ]);

        $customer = User::factory()->create([
            'name' => 'Sham Buyer',
            'phone' => '9815457660',
            'city' => 'Ghumar mandi',
            'distributor_profile_id' => $profile->id,
        ]);
        $customer->assignRole('customer');

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['search' => 'Sham']))
            ->assertOk()
            ->assertSee('Sham Buyer')
            ->assertDontSee('Demo Customer');

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['search' => 'Aman']))
            ->assertOk()
            ->assertSee('Sham Buyer');

        $this->actingAs($admin)
            ->put(route('admin.customers.update', $customer), [
                'name' => 'Sham Updated',
                'phone' => '9815457660',
                'city' => 'Delhi',
                'distributor_profile_id' => $profile->id,
            ])
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success');

        $this->assertEquals('Sham Updated', $customer->fresh()->name);
        $this->assertEquals('Delhi', $customer->fresh()->city);

        $this->actingAs($admin)
            ->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }

    public function test_admin_can_add_distributor(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.distributors.store'), [
                'name' => 'New Ply Co',
                'email' => 'newdist@plywood.com',
                'phone' => '9876543210',
                'location' => 'Mumbai',
                'status' => 'approved',
            ])
            ->assertRedirect(route('admin.distributors.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newdist@plywood.com',
            'phone' => '9876543210',
            'city' => 'Mumbai',
        ]);

        $this->assertDatabaseHas('distributor_profiles', [
            'business_name' => 'New Ply Co',
            'is_approved' => true,
        ]);
    }

    public function test_admin_can_quick_edit_distributor_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $distributorUser = User::factory()->create([
            'name' => 'Dist User',
            'phone' => '1111111111',
            'city' => 'Delhi',
        ]);
        $distributorUser->assignRole('distributor');

        $profile = \App\Models\DistributorProfile::create([
            'user_id' => $distributorUser->id,
            'business_name' => 'Dist User',
            'is_approved' => false,
            'service_cities' => ['Delhi'],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.distributors.update', $profile), [
                'name' => 'Dist User',
                'phone' => '1111111111',
                'location' => 'Mumbai',
                'status' => 'approved',
            ])
            ->assertRedirect(route('admin.distributors.index'))
            ->assertSessionHas('success');

        $this->assertTrue($profile->fresh()->is_approved);
        $this->assertEquals('Mumbai', $distributorUser->fresh()->city);
    }

    public function test_admin_can_update_distributor_status_from_dropdown(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $distributorUser = User::factory()->create();
        $distributorUser->assignRole('distributor');

        $profile = \App\Models\DistributorProfile::create([
            'user_id' => $distributorUser->id,
            'business_name' => 'Status Test',
            'is_approved' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.distributors.status', $profile), [
                'status' => 'approved',
            ])
            ->assertRedirect(route('admin.distributors.index'))
            ->assertSessionHas('success');

        $this->assertTrue($profile->fresh()->is_approved);
    }

    public function test_admin_can_search_distributors_by_name_phone_and_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $approvedUser = User::factory()->create([
            'name' => 'Raj Sharma',
            'phone' => '7894561235',
        ]);
        $approvedUser->assignRole('distributor');
        \App\Models\DistributorProfile::create([
            'user_id' => $approvedUser->id,
            'business_name' => 'Raj Sharma',
            'is_approved' => true,
        ]);

        $pendingUser = User::factory()->create([
            'name' => 'Aman Singh',
            'phone' => '4578962140',
        ]);
        $pendingUser->assignRole('distributor');
        \App\Models\DistributorProfile::create([
            'user_id' => $pendingUser->id,
            'business_name' => 'Aman Singh',
            'is_approved' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.distributors.index', ['search' => 'Raj']))
            ->assertOk()
            ->assertSee('Raj Sharma')
            ->assertDontSee('Aman Singh');

        $this->actingAs($admin)
            ->get(route('admin.distributors.index', ['search' => '4578']))
            ->assertOk()
            ->assertSee('Aman Singh')
            ->assertDontSee('Raj Sharma');

        $this->actingAs($admin)
            ->get(route('admin.distributors.index', ['search' => 'approved']))
            ->assertOk()
            ->assertSee('Raj Sharma')
            ->assertDontSee('Aman Singh');
    }

    public function test_admin_can_delete_distributor(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $distributorUser = User::factory()->create();
        $distributorUser->assignRole('distributor');

        $profile = \App\Models\DistributorProfile::create([
            'user_id' => $distributorUser->id,
            'business_name' => 'To Delete',
            'is_approved' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.distributors.destroy', $profile))
            ->assertRedirect(route('admin.distributors.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('distributor_profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('users', ['id' => $distributorUser->id]);
    }

    public function test_customer_cannot_access_admin_users_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
