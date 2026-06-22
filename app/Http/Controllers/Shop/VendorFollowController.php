<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorFollower;

class VendorFollowController extends Controller
{
    public function toggle($vendorId)
    {
        $vendor = User::where('role', 'vendor')->findOrFail($vendorId);

        $existing = VendorFollower::where('user_id', auth()->id())
            ->where('vendor_id', $vendorId)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Unfollowed vendor successfully.';
            $following = false;
        } else {
            VendorFollower::create([
                'user_id' => auth()->id(),
                'vendor_id' => $vendorId,
            ]);
            $message = 'Following vendor successfully.';
            $following = true;
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'following' => $following,
                'followers_count' => $vendor->followers()->count(),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function followers()
    {
        $followers = VendorFollower::where('vendor_id', auth()->id())
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('vendor.followers.index', compact('followers'));
    }

    public function following()
    {
        $following = VendorFollower::where('user_id', auth()->id())
            ->with('vendor')
            ->latest()
            ->paginate(20);

        return view('customer.following.index', compact('following'));
    }
}
