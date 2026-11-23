<?php

namespace Tests\Feature\Vendor;

use App\Models\DataExportRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor data export requests.
 *
 * Unlike the other controllers in this suite, DataExportRequestController
 * scopes ownership by `requested_by` (a real `users.id`, set from
 * `Auth::id()` on creation) rather than by vendor id -- there is no
 * vendor-id mix-up bug here, but the ownership check is still worth a
 * regression test since download() also gates on a token and a `ready`
 * status.
 */
class DataExportRequestIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_download_another_vendors_export_request(): void
    {
        Storage::fake('local');

        [$mine] = $this->makeVendor();
        [, , $theirRequest] = $this->makeVendorWithReadyExport();

        $this->actingAs($mine)
            ->get(route('vendor.data-export.download', [
                'dataExportRequest' => $theirRequest,
                'token' => $theirRequest->token,
            ]))
            ->assertForbidden();
    }

    public function test_a_vendor_can_still_download_their_own_ready_export(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        Storage::fake('local');

        [$owner, , $myRequest] = $this->makeVendorWithReadyExport();

        $this->actingAs($owner)
            ->get(route('vendor.data-export.download', [
                'dataExportRequest' => $myRequest,
                'token' => $myRequest->token,
            ]))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: DataExportRequest}
     */
    private function makeVendorWithReadyExport(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $archivePath = 'exports/export-'.uniqid().'.zip';
        Storage::disk('local')->put($archivePath, 'isolation fixture archive contents');

        $request = DataExportRequest::create([
            'vendor_id' => $vendor->id,
            'requested_by' => $user->id,
            'reason' => 'Isolation fixture.',
            'status' => DataExportRequest::STATUS_READY,
            'archive_path' => $archivePath,
            'token' => bin2hex(random_bytes(16)),
        ]);

        return [$user, $vendor, $request];
    }
}
