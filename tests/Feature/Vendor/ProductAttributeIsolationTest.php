<?php

namespace Tests\Feature\Vendor;

use App\Models\ProductAttribute;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor product attributes.
 *
 * ProductAttributeController checks
 * `$attribute->vendor_id !== auth()->user()->vendor->id` and aborts with 403
 * on an implicitly-bound ProductAttribute. Routes sit behind
 * module.access:multiple_product_category, which MakesIsolatedVendors
 * grants by default.
 */
class ProductAttributeIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_edit_another_vendors_attribute(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirAttribute] = $this->makeVendorWithAttribute();

        $this->actingAs($mine)
            ->get(route('vendor.attributes.edit', $theirAttribute))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_attribute(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirAttribute] = $this->makeVendorWithAttribute();
        $originalName = $theirAttribute->name;

        $this->actingAs($mine)
            ->put(route('vendor.attributes.update', $theirAttribute), [
                'name' => 'Hijacked',
                'type' => 'text',
                'default_sort_order' => 'custom_ordering',
            ])
            ->assertForbidden();

        $this->assertSame(
            $originalName,
            $theirAttribute->fresh()->name,
            "A vendor updated another vendor's product attribute. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_attribute(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirAttribute] = $this->makeVendorWithAttribute();

        $this->actingAs($mine)
            ->delete(route('vendor.attributes.destroy', $theirAttribute))
            ->assertForbidden();

        $this->assertDatabaseHas('product_attributes', ['id' => $theirAttribute->id]);
    }

    public function test_a_vendor_can_still_edit_their_own_attribute(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myAttribute] = $this->makeVendorWithAttribute();

        $this->actingAs($mine)
            ->get(route('vendor.attributes.edit', $myAttribute))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: ProductAttribute}
     */
    private function makeVendorWithAttribute(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $attribute = ProductAttribute::create([
            'vendor_id' => $vendor->id,
            'name' => 'Attribute '.uniqid(),
            'slug' => 'attribute-'.uniqid(),
            'type' => 'text',
            'is_filterable' => true,
            'is_visible' => true,
            'enable_archives' => false,
            'default_sort_order' => 'custom_ordering',
            'sort_order' => 0,
        ]);

        return [$user, $vendor, $attribute];
    }
}
