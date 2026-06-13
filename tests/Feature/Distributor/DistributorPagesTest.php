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
            ->assertSee('Your products');

        $this->actingAs($user)
            ->get(route('distributor.inquiries.index'))
            ->assertOk()
            ->assertSee('Inquiries');

        $this->actingAs($user)
            ->get(route('distributor.orders.index'))
            ->assertOk()
            ->assertSee('Orders');
    }

    public function test_distributor_pages_only_show_own_records(): void
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

        $ownProduct = Product::create([
            'distributor_profile_id' => $profile->id,
            'category_id' => Category::first()->id,
            'name' => 'Own Plywood Sheet',
            'thickness' => '18mm',
            'size' => '8ft x 4ft',
            'grade' => 'BWP',
        ]);

        Product::create([
            'distributor_profile_id' => $otherProfile->id,
            'category_id' => Category::first()->id,
            'name' => 'Other Distributor Product',
            'thickness' => '12mm',
            'size' => '8ft x 4ft',
            'grade' => 'MR',
        ]);

        $this->actingAs($user)
            ->get(route('distributor.products.index'))
            ->assertOk()
            ->assertSee($ownProduct->name)
            ->assertDontSee('Other Distributor Product');
    }

    public function test_admin_cannot_access_distributor_products_page(): void
    {
        $admin = User::where('email', 'admin@plywood.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('distributor.products.index'))
            ->assertForbidden();
    }
}
