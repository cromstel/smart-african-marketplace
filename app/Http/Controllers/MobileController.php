<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $userAgent = $request->userAgent() ?? '';
        $deviceType = $this->detectDeviceType($userAgent);

        $androidVersion = Setting::getValue('mobile_android_version', '1.0.0');
        $androidBuild = Setting::getValue('mobile_android_build', '1');
        $androidUrl = Setting::getValue('mobile_android_url', '#');
        $androidMinOs = Setting::getValue('mobile_android_min_os', '5.0');

        $iosVersion = Setting::getValue('mobile_ios_version', '1.0.0');
        $iosBuild = Setting::getValue('mobile_ios_build', '1');
        $iosAppStoreUrl = Setting::getValue('mobile_ios_app_store_url', '#');
        $iosMinOs = Setting::getValue('mobile_ios_min_os', '12.0');

        $changelogUrl = Setting::getValue('mobile_changelog_url', '#');
        $installInstructionsUrl = Setting::getValue('mobile_install_instructions_url', '#');
        $contactSupportUrl = Setting::getValue('mobile_contact_support_url', '#');

        $preferredOrder = $deviceType === 'ios' ? ['ios', 'android'] : ['android', 'ios'];

        return view('mobile.index', compact(
            'deviceType',
            'androidVersion',
            'androidBuild',
            'androidUrl',
            'androidMinOs',
            'iosVersion',
            'iosBuild',
            'iosAppStoreUrl',
            'iosMinOs',
            'changelogUrl',
            'installInstructionsUrl',
            'contactSupportUrl',
            'preferredOrder'
        ));
    }

    public function releases(): JsonResponse
    {
        $android = [
            'version' => Setting::getValue('mobile_android_version', '1.0.0'),
            'build' => Setting::getValue('mobile_android_build', '1'),
            'url' => Setting::getValue('mobile_android_url'),
            'sha256' => Setting::getValue('mobile_android_sha256'),
            'minimum_os' => Setting::getValue('mobile_android_min_os', '5.0'),
        ];

        $ios = [
            'version' => Setting::getValue('mobile_ios_version', '1.0.0'),
            'build' => Setting::getValue('mobile_ios_build', '1'),
            'app_store_url' => Setting::getValue('mobile_ios_app_store_url'),
            'minimum_os' => Setting::getValue('mobile_ios_min_os', '12.0'),
        ];

        $publishedAt = Setting::getValue('mobile_published_at', now()->toIso8601String());

        return response()->json([
            'android' => $android,
            'ios' => $ios,
            'published_at' => $publishedAt,
        ]);
    }

    private function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'android')) {
            return 'android';
        }

        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad') || str_contains($userAgent, 'ipod')) {
            return 'ios';
        }

        return 'other';
    }
}
