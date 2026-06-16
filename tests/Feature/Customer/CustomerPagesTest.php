<?php

namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Inquiry;
use App\Models\Order;
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
            ->get(route('customer.cart.index'))
            ->assertOk()
            ->assertSee('Cart');

        $this->actingAs($user)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Orders');
    }

    public function test_customer_orders_only_show_own_records(): void
    {
        $user = $this->customerUser();
        $otherCustomer = User::factory()->create(['name' => 'Other Customer']);
        $otherCustomer->assignRole('customer');

        $profile = DistributorProfile::first();

        $ownInquiry = Inquiry::create([
            'customer_id' => $user->id,
            'distributor_profile_id' => $profile->id,
            'inquiry_number' => 'INQ-OWN-001',
            'status' => 'converted',
            'delivery_city' => 'Delhi',
            'delivery_pincode' => '110001',
        ]);

        Order::create([
            'inquiry_id' => $ownInquiry->id,
            'customer_id' => $user->id,
            'distributor_profile_id' => $profile->id,
            'order_number' => 'ORD-OWN-001',
            'total_amount' => 1000,
            'payment_status' => 'pending',
            'fulfillment_status' => 'processing',
            'delivery_address' => 'Delhi',
        ]);

        $otherInquiry = Inquiry::create([
            'customer_id' => $otherCustomer->id,
            'distributor_profile_id' => $profile->id,
            'inquiry_number' => 'INQ-OTHER-001',
            'status' => 'converted',
            'delivery_city' => 'Mumbai',
            'delivery_pincode' => '400001',
        ]);

        Order::create([
            'inquiry_id' => $otherInquiry->id,
            'customer_id' => $otherCustomer->id,
            'distributor_profile_id' => $profile->id,
            'order_number' => 'ORD-OTHER-001',
            'total_amount' => 2000,
            'payment_status' => 'pending',
            'fulfillment_status' => 'processing',
            'delivery_address' => 'Mumbai',
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('ORD-OWN-001')
            ->assertDontSee('ORD-OTHER-001');
    }

    public function test_customer_catalog_hides_unassigned_products(): void
    {
        $user = $this->customerUser();

        Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Platform Ply 16mm',
            'brand' => 'CenturyPly',
            'thickness' => '16mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'in_stock' => true,
        ]);

        $this->actingAs($user)
            ->get(route('customer.catalog.index'))
            ->assertOk()
            ->assertDontSee('Platform Ply 16mm');
    }

    public function test_customer_catalog_only_shows_products_from_assigned_distributor(): void
    {
        $user = $this->customerUser();
        $assignedProfile = DistributorProfile::firstOrFail();

        $otherDistributorUser = User::factory()->create([
            'name' => 'Other Distributor User',
            'email' => 'other-distributor@plywood.com',
        ]);
        $otherDistributorUser->assignRole('distributor');

        $otherProfile = DistributorProfile::create([
            'user_id' => $otherDistributorUser->id,
            'business_name' => 'Other Plywood Co',
            'service_cities' => ['Mumbai'],
            'is_approved' => true,
        ]);

        $user->update(['distributor_profile_id' => $assignedProfile->id]);

        $visibleProduct = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Assigned Distributor Ply',
            'brand' => 'CenturyPly',
            'thickness' => '16mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'in_stock' => true,
        ]);

        $hiddenProduct = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Other Distributor Ply',
            'brand' => 'CenturyPly',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'in_stock' => true,
        ]);

        $assignedProfile->offeredProducts()->attach($visibleProduct->id, ['price' => 1500]);
        $otherProfile->offeredProducts()->attach($hiddenProduct->id, ['price' => 1600]);

        $this->actingAs($user)
            ->get(route('customer.catalog.index'))
            ->assertOk()
            ->assertSee('Assigned Distributor Ply')
            ->assertDontSee('Other Distributor Ply');
    }

    public function test_customer_catalog_is_empty_without_assigned_distributor(): void
    {
        $user = $this->customerUser();
        $profile = DistributorProfile::firstOrFail();
        $user->update(['distributor_profile_id' => null]);

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Unlinked Customer Ply',
            'brand' => 'CenturyPly',
            'thickness' => '16mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 1500]);

        $this->actingAs($user)
            ->get(route('customer.catalog.index'))
            ->assertOk()
            ->assertDontSee('Unlinked Customer Ply')
            ->assertSee('not linked to a distributor', false);
    }

    public function test_customer_can_view_product_detail_page(): void
    {
        $user = $this->customerUser();
        $profile = DistributorProfile::firstOrFail();
        $user->update(['distributor_profile_id' => $profile->id]);

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Detail Page Ply 12mm',
            'brand' => 'CenturyPly',
            'thickness' => '12mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'description' => 'Full product description for detail page.',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 1100]);

        $this->actingAs($user)
            ->get(route('customer.catalog.show', $product))
            ->assertOk()
            ->assertSee('Detail Page Ply 12mm')
            ->assertSee('Full product description for detail page.')
            ->assertSee('Back to catalog')
            ->assertSee('Add to cart');

        $this->actingAs($user)
            ->get(route('customer.catalog.index'))
            ->assertOk()
            ->assertSee(route('customer.catalog.show', $product), false);
    }

    public function test_customer_catalog_shows_admin_added_products(): void
    {
        $user = $this->customerUser();
        $profile = DistributorProfile::firstOrFail();
        $user->update(['distributor_profile_id' => $profile->id]);

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Admin Listed Ply 18mm',
            'brand' => 'CenturyPly',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 1800]);

        $this->actingAs($user)
            ->get(route('customer.catalog.index'))
            ->assertOk()
            ->assertSee('Admin Listed Ply 18mm')
            ->assertSee('CenturyPly')
            ->assertSee('Add to cart');

        $this->actingAs($user)
            ->post(route('customer.catalog.add-to-cart', $product), ['quantity' => 5])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->get(route('customer.cart.index'))
            ->assertSee('Admin Listed Ply 18mm')
            ->assertSee($profile->business_name);
    }

    public function test_customer_can_proceed_cart_to_distributor(): void
    {
        $customer = $this->customerUser();
        $profile = DistributorProfile::firstOrFail();
        $customer->update(['distributor_profile_id' => $profile->id]);

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Proceed Test Ply',
            'brand' => 'CenturyPly',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 1200]);

        $this->actingAs($customer)
            ->post(route('customer.catalog.add-to-cart', $product), ['quantity' => 2])
            ->assertRedirect(route('customer.cart.index'));

        $this->actingAs($customer)
            ->post(route('customer.cart.proceed'))
            ->assertRedirect(route('customer.orders.index'))
            ->assertSessionHas('success');

        $inquiry = Inquiry::query()
            ->where('customer_id', $customer->id)
            ->where('distributor_profile_id', $profile->id)
            ->latest()
            ->first();

        $this->assertNotNull($inquiry);
        $this->assertSame('converted', $inquiry->status);
        $this->assertDatabaseHas('inquiry_items', [
            'inquiry_id' => $inquiry->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('orders', [
            'inquiry_id' => $inquiry->id,
            'customer_id' => $customer->id,
            'distributor_profile_id' => $profile->id,
            'total_amount' => 2400,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.orders.index'))
            ->assertOk()
            ->assertSee('Proceed Test Ply');

        $this->actingAs($customer)
            ->get(route('customer.cart.index'))
            ->assertSee('Proceed Test Ply');

        $distributorUser = User::where('email', 'distributor@plywood.com')->firstOrFail();

        $this->actingAs($distributorUser)
            ->get(route('distributor.orders.index'))
            ->assertOk()
            ->assertSee('Proceed Test Ply');
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        $customer = $this->customerUser();
        $profile = DistributorProfile::firstOrFail();
        $customer->update(['distributor_profile_id' => $profile->id]);

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Removable Ply',
            'brand' => 'CenturyPly',
            'thickness' => '12mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 900]);

        $this->actingAs($customer)
            ->post(route('customer.catalog.add-to-cart', $product), ['quantity' => 1])
            ->assertRedirect(route('customer.cart.index'));

        $this->actingAs($customer)
            ->get(route('customer.cart.index'))
            ->assertSee('Removable Ply');

        $this->actingAs($customer)
            ->delete(route('customer.cart.remove', $product))
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->get(route('customer.cart.index'))
            ->assertSee('0 items')
            ->assertSee('Your cart is empty');
    }

    public function test_customer_can_update_cart_item(): void
    {
        $customer = $this->customerUser();
        $profile = DistributorProfile::firstOrFail();
        $customer->update(['distributor_profile_id' => $profile->id]);

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Editable Ply',
            'brand' => 'CenturyPly',
            'thickness' => '12mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 900]);

        $this->actingAs($customer)
            ->post(route('customer.catalog.add-to-cart', $product), ['quantity' => 1])
            ->assertRedirect(route('customer.cart.index'));

        $this->actingAs($customer)
            ->put(route('customer.cart.update', $product), [
                'quantity' => 11,
                'notes' => 'Deliver before noon',
            ])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHas('success');

        $this->actingAs($customer)
            ->get(route('customer.cart.index'))
            ->assertSee('Editable Ply')
            ->assertSee('11')
            ->assertSee('Deliver before noon');

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 11,
            'notes' => 'Deliver before noon',
        ]);
    }

    public function test_logout_preserves_customer_cart(): void
    {
        $user = $this->customerUser();
        $profile = DistributorProfile::firstOrFail();
        $user->update(['distributor_profile_id' => $profile->id]);

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Cart Test Ply',
            'brand' => 'CenturyPly',
            'thickness' => '12mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 800]);

        $this->actingAs($user)
            ->post(route('customer.catalog.add-to-cart', $product), ['quantity' => 3])
            ->assertRedirect(route('customer.cart.index'));

        $this->actingAs($user)
            ->get(route('customer.cart.index'))
            ->assertSee('Cart Test Ply');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->actingAs($user)
            ->get(route('customer.cart.index'))
            ->assertSee('Cart Test Ply')
            ->assertSee('3');
    }
}
