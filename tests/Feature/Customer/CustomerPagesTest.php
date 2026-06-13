<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    protected function customerUser(): User
    {
        return User::where('email', 'customer@plywood.com')->firstOrFail();
    }

    public function test_customer_can_access_sidebar_pages(): void
    {
        $user = $this->customerUser();

        $this->actingAs($user)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Getting started');

        $this->actingAs($user)
            ->get(route('customer.catalog.index'))
            ->assertOk()
            ->assertSee('Browse Catalog')
            ->assertSee('Available products');

        $this->actingAs($user)
            ->get(route('customer.inquiry-cart.index'))
            ->assertOk()
            ->assertSee('Inquiry Cart');

        $this->actingAs($user)
            ->get(route('customer.inquiries.index'))
            ->assertOk()
            ->assertSee('My Inquiries');

        $this->actingAs($user)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Orders');
    }

    public function test_customer_pages_only_show_own_records(): void
    {
        $user = $this->customerUser();
        $otherCustomer = User::factory()->create(['name' => 'Other Customer']);
        $otherCustomer->assignRole('customer');

        $profile = DistributorProfile::first();

        Inquiry::create([
            'customer_id' => $user->id,
            'distributor_profile_id' => $profile->id,
            'inquiry_number' => 'INQ-OWN-001',
            'status' => 'pending',
            'delivery_city' => 'Delhi',
            'delivery_pincode' => '110001',
        ]);

        Inquiry::create([
            'customer_id' => $otherCustomer->id,
            'distributor_profile_id' => $profile->id,
            'inquiry_number' => 'INQ-OTHER-001',
            'status' => 'pending',
            'delivery_city' => 'Mumbai',
            'delivery_pincode' => '400001',
        ]);

        $this->actingAs($user)
            ->get(route('customer.inquiries.index'))
            ->assertOk()
            ->assertSee('INQ-OWN-001')
            ->assertDontSee('INQ-OTHER-001');
    }

    public function test_customer_catalog_shows_admin_added_products(): void
    {
        $user = $this->customerUser();
        $profile = DistributorProfile::first();

        $product = Product::create([
            'distributor_profile_id' => $profile->id,
            'category_id' => Category::first()->id,
            'name' => 'Admin Listed Ply 18mm',
            'brand' => 'CenturyPly',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'in_stock' => true,
        ]);

        $this->actingAs($user)
            ->get(route('customer.catalog.index'))
            ->assertOk()
            ->assertSee('Admin Listed Ply 18mm')
            ->assertSee('CenturyPly')
            ->assertSee('Add to cart');

        $this->actingAs($user)
            ->post(route('customer.catalog.add-to-cart', $product), ['quantity' => 5])
            ->assertRedirect(route('customer.inquiry-cart.index'))
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->get(route('customer.inquiry-cart.index'))
            ->assertSee('Admin Listed Ply 18mm')
            ->assertSee($profile->business_name);
    }

    public function test_distributor_cannot_access_customer_inquiries_page(): void
    {
        $distributor = User::where('email', 'distributor@plywood.com')->firstOrFail();

        $this->actingAs($distributor)
            ->get(route('customer.inquiries.index'))
            ->assertForbidden();
    }
}
