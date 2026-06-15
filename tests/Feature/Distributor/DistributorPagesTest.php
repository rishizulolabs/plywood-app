<?php

namespace Tests\Feature\Distributor;

use App\Models\Category;
use App\Models\DistributorProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    protected function distributorUser(): User
    {
        return User::where('email', 'distributor@plywood.com')->firstOrFail();
    }

    public function test_distributor_can_access_sidebar_pages(): void
    {
        $user = $this->distributorUser();

        $this->actingAs($user)
            ->get(route('distributor.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('distributor.products.index'))
            ->assertOk()
            ->assertSee('Assigned products');

        $this->actingAs($user)
            ->get(route('distributor.orders.index'))
            ->assertOk()
            ->assertSee('Customer Orders');

        $this->actingAs($user)
            ->get(route('distributor.purchase-orders.index'))
            ->assertOk()
            ->assertSee('Purchase Orders');

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertRedirect(route('distributor.dashboard'));
    }

    public function test_distributor_products_page_shows_only_assigned_products(): void
    {
        $user = $this->distributorUser();
        $profile = $user->distributorProfile;

        $assignedProduct = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Assigned Catalog Ply',
            'brand' => 'CenturyPly',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'in_stock' => true,
        ]);

        $unassignedProduct = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Unassigned Catalog Ply',
            'brand' => 'CenturyPly',
            'thickness' => '12mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWR',
            'in_stock' => true,
        ]);

        $profile->offeredProducts()->attach($assignedProduct->id, ['price' => 1500]);

        $this->actingAs($user)
            ->get(route('distributor.products.index'))
            ->assertOk()
            ->assertSee('Assigned products')
            ->assertSee('Assigned Catalog Ply')
            ->assertSee('CenturyPly')
            ->assertSee('₹1,500.00')
            ->assertSee('Total quantity')
            ->assertSee('Customer order')
            ->assertSee('Balance')
            ->assertSee('Restock')
            ->assertDontSee('Unassigned Catalog Ply');
    }

    public function test_distributor_can_place_restock_order_to_admin(): void
    {
        $user = $this->distributorUser();
        $profile = $user->distributorProfile;

        $product = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Restock Test Ply',
            'brand' => 'CenturyPly',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
            'min_order_qty' => 5,
        ]);

        $profile->offeredProducts()->attach($product->id, ['price' => 7000]);

        $this->actingAs($user)
            ->post(route('distributor.products.restock', $product), ['quantity' => 10])
            ->assertRedirect(route('distributor.purchase-orders.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('restock_requests', [
            'distributor_profile_id' => $profile->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('distributor_product', [
            'distributor_profile_id' => $profile->id,
            'product_id' => $product->id,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($user)
            ->get(route('distributor.products.index'))
            ->assertOk()
            ->assertSee('Restock Test Ply')
            ->assertSee('10');

        $this->actingAs($user)
            ->get(route('distributor.purchase-orders.index'))
            ->assertOk()
            ->assertSee('Restock Test Ply')
            ->assertSee('Pending');

        $admin = User::where('email', 'admin@plywood.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.distributor-orders.index'))
            ->assertOk()
            ->assertSee('Distributor Orders')
            ->assertSee('Restock Test Ply')
            ->assertSee('Pending');

        $restockRequest = \App\Models\RestockRequest::where('product_id', $product->id)->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.distributor-orders.status', $restockRequest), [
                'status' => 'approved',
            ])
            ->assertRedirect(route('admin.distributor-orders.index'))
            ->assertSessionHas('success');

        $this->assertEquals('approved', $restockRequest->fresh()->status);
    }

    public function test_distributor_products_only_show_own_assignments(): void
    {
        $user = $this->distributorUser();
        $profile = $user->distributorProfile;

        $otherUser = User::factory()->create();
        $otherUser->assignRole('distributor');
        $otherProfile = DistributorProfile::create([
            'user_id' => $otherUser->id,
            'business_name' => 'Other Distributor',
            'is_approved' => true,
        ]);

        $ownAssignedProduct = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Own Assigned Ply',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
        ]);

        $otherAssignedProduct = Product::create([
            'distributor_profile_id' => null,
            'category_id' => Category::first()->id,
            'name' => 'Other Assigned Ply',
            'thickness' => '12mm',
            'size' => '8ft x 4ft',
            'grade' => 'MR',
        ]);

        $profile->offeredProducts()->attach($ownAssignedProduct->id, ['price' => 1200]);
        $otherProfile->offeredProducts()->attach($otherAssignedProduct->id, ['price' => 900]);

        $this->actingAs($user)
            ->get(route('distributor.products.index'))
            ->assertOk()
            ->assertSee('Own Assigned Ply')
            ->assertDontSee('Other Assigned Ply');
    }

    public function test_distributor_can_update_order_status(): void
    {
        $user = $this->distributorUser();
        $profile = $user->distributorProfile;
        $customer = \App\Models\User::where('email', 'customer@plywood.com')->firstOrFail();

        $inquiry = \App\Models\Inquiry::create([
            'customer_id' => $customer->id,
            'distributor_profile_id' => $profile->id,
            'inquiry_number' => 'INQ-STATUS-001',
            'status' => 'converted',
            'delivery_city' => 'Delhi',
            'delivery_pincode' => '110001',
        ]);

        $order = \App\Models\Order::create([
            'inquiry_id' => $inquiry->id,
            'customer_id' => $customer->id,
            'distributor_profile_id' => $profile->id,
            'order_number' => 'ORD-STATUS-001',
            'total_amount' => 5000,
            'payment_status' => 'pending',
            'fulfillment_status' => 'processing',
            'delivery_address' => 'Delhi',
        ]);

        $this->actingAs($user)
            ->get(route('distributor.orders.index'))
            ->assertOk()
            ->assertSee('ORD-STATUS-001')
            ->assertSee('Processing');

        $this->actingAs($user)
            ->patch(route('distributor.orders.status', $order), [
                'fulfillment_status' => 'dispatched',
            ])
            ->assertRedirect(route('distributor.orders.index'))
            ->assertSessionHas('success');

        $this->assertSame('dispatched', $order->fresh()->fulfillment_status);

        $this->actingAs($user)
            ->get(route('distributor.orders.index'))
            ->assertSee('Dispatched');
    }

    public function test_admin_cannot_access_distributor_products_page(): void
    {
        $admin = User::where('email', 'admin@plywood.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('distributor.products.index'))
            ->assertForbidden();
    }
}
