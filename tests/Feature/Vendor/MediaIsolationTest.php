<?php

namespace Tests\Feature\Vendor;

use App\Models\MediaLibrary;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for the vendor media library.
 *
 * MediaController::show()/edit()/update()/destroy() all check
 * `$media->vendor_id !== $vendor->id` and abort with 403 on an
 * implicitly-bound MediaLibrary item. Routes sit behind
 * module.access:vendor_media_library, which is not one of
 * MakesIsolatedVendors' default feature keys, so it is requested
 * explicitly here.
 */
class MediaIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_media(): void
    {
        [$mine] = $this->makeVendor(['vendor_media_library']);
        [, , $theirMedia] = $this->makeVendorWithMedia();

        $this->actingAs($mine)
            ->get(route('vendor.media.show', $theirMedia))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_edit_another_vendors_media(): void
    {
        [$mine] = $this->makeVendor(['vendor_media_library']);
        [, , $theirMedia] = $this->makeVendorWithMedia();

        $this->actingAs($mine)
            ->get(route('vendor.media.edit', $theirMedia))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_media(): void
    {
        [$mine] = $this->makeVendor(['vendor_media_library']);
        [, , $theirMedia] = $this->makeVendorWithMedia();
        $originalTitle = $theirMedia->title;

        $this->actingAs($mine)
            ->put(route('vendor.media.update', $theirMedia), ['title' => 'Hijacked'])
            ->assertForbidden();

        $this->assertSame(
            $originalTitle,
            $theirMedia->fresh()->title,
            "A vendor updated another vendor's media metadata. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_media(): void
    {
        [$mine] = $this->makeVendor(['vendor_media_library']);
        [, , $theirMedia] = $this->makeVendorWithMedia();

        $this->actingAs($mine)
            ->delete(route('vendor.media.destroy', $theirMedia))
            ->assertForbidden();

        $this->assertDatabaseHas('media_library', ['id' => $theirMedia->id]);
    }

    public function test_a_vendor_can_still_view_their_own_media(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myMedia] = $this->makeVendorWithMedia();

        $this->actingAs($mine)
            ->get(route('vendor.media.show', $myMedia))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: MediaLibrary}
     */
    private function makeVendorWithMedia(): array
    {
        [$user, $vendor] = $this->makeVendor(['vendor_media_library']);

        $media = MediaLibrary::create([
            'title' => 'Fixture image',
            'file_name' => 'fixture-'.uniqid().'.jpg',
            'original_name' => 'fixture.jpg',
            'file_path' => 'media/vendors/'.$vendor->id.'/fixture-'.uniqid().'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12345,
            'type' => 'image',
            'uploaded_by_type' => 'App\\Models\\User',
            'uploaded_by_id' => $user->id,
            'vendor_id' => $vendor->id,
        ]);

        return [$user, $vendor, $media];
    }
}
