<?php

namespace Tests\Feature\Vendor;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * The vendor and admin media libraries store uploads as-is on the `public`
 * disk at a predictable, directly-browsable URL. Both used to allow `svg`
 * alongside raster image formats -- an SVG can carry an onload handler or
 * an inline <script>, which the browser executes if that URL is ever
 * opened directly (not just referenced via <img src>). Any vendor could
 * upload one through this shared library, making it a stored-XSS vector
 * against whoever later opens the file's URL. This proves svg uploads are
 * now rejected while legitimate raster formats still pass.
 */
class MediaUploadSecurityTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_upload_an_svg_through_the_media_library(): void
    {
        [$vendor] = $this->makeVendor(['vendor_media_library']);

        $this->actingAs($vendor)
            ->post(route('vendor.media.upload'), [
                'files' => [UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml')],
            ])
            ->assertSessionHasErrors('files.0');

        $this->assertDatabaseCount('media_library', 0);
    }

    public function test_a_vendor_can_still_upload_a_png_through_the_media_library(): void
    {
        [$vendor] = $this->makeVendor(['vendor_media_library']);

        $this->actingAs($vendor)
            ->post(route('vendor.media.upload'), [
                'files' => [UploadedFile::fake()->image('logo.png', 100, 100)],
            ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseCount('media_library', 1);
    }
}
