<?php

namespace App\Observers;

use App\Models\MediaFolder;
use App\Models\Vendor;

class VendorObserver
{
    /**
     * Handle the Vendor "created" event.
     * Creates a dedicated media folder for the vendor.
     */
    public function created(Vendor $vendor): void
    {
        $this->createVendorMediaFolder($vendor);
    }

    /**
     * Create vendor's root media folder.
     */
    protected function createVendorMediaFolder(Vendor $vendor): void
    {
        // Check if folder already exists
        $existingFolder = MediaFolder::where('vendor_id', $vendor->id)
            ->whereNull('parent_id')
            ->first();

        if ($existingFolder) {
            return;
        }

        // Create vendor's root folder
        $folderName = "Vendor {$vendor->id}";

        // Try to use vendor name if available
        if ($vendor->name) {
            $folderName = $vendor->name;
        }

        MediaFolder::create([
            'name' => $folderName,
            'slug' => 'vendor-'.$vendor->id,
            'parent_id' => null,
            'vendor_id' => $vendor->id,
            'owner_type' => 'App\\Models\\Vendor',
            'owner_id' => $vendor->id,
            'created_by_id' => $vendor->user_id,
        ]);
    }

    /**
     * Handle the Vendor "deleted" event.
     */
    public function deleted(Vendor $vendor): void
    {
        // Optionally handle vendor deletion - could archive folders instead
        // For now, we keep the folders for audit purposes
    }
}
