<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorTheme;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function index(): View
    {
        $vendor = auth()->user()->vendor;
        $theme = $vendor->theme ?: new VendorTheme([
            'vendor_id' => $vendor->id,
            'primary_color' => '#ea580c',
            'secondary_color' => '#dc2626',
            'accent_color' => '#f97316',
            'text_color' => '#1f2937',
            'background_color' => '#ffffff',
            'font_family' => 'Inter',
            'font_heading' => 'Inter',
            'layout_style' => 'grid',
            'show_categories' => true,
            'show_filters' => true,
            'show_search' => true,
            'is_active' => true,
        ]);

        return view('vendor.theme.index', compact('theme'));
    }

    public function update(Request $request, ImageOptimizationService $imageService): RedirectResponse
    {
        $vendor = auth()->user()->vendor;

        $validated = $request->validate([
            'primary_color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'required|string|max:100',
            'font_heading' => 'required|string|max:100',
            'banner_image' => 'nullable|image|max:2048',
            'logo' => 'nullable|image|max:1024',
            'favicon' => 'nullable|image|max:512',
            'custom_css' => 'nullable|string|max:10000',
            'custom_js' => 'nullable|string|max:10000',
            'layout_style' => 'required|in:grid,list,masonry',
            'show_categories' => 'boolean',
            'show_filters' => 'boolean',
            'show_search' => 'boolean',
            'about_text' => 'nullable|string|max:5000',
            'contact_info' => 'nullable|string|max:1000',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
            'social_links.linkedin' => 'nullable|url',
            'social_links.whatsapp' => 'nullable|string',
            'is_active' => 'boolean',
            'profile_image' => 'nullable|image|max:1024',
        ]);

        DB::beginTransaction();

        try {
            $theme = $vendor->theme ?: new VendorTheme(['vendor_id' => $vendor->id]);

            if ($request->hasFile('banner_image')) {
                if ($theme->banner_image) {
                    Storage::disk('public')->delete($theme->banner_image);
                }

                $result = $imageService->optimizeAndStore($request->file('banner_image'), 'themes/banners');
                $validated['banner_image'] = $result['original'];
            }

            if ($request->hasFile('logo')) {
                if ($theme->logo) {
                    Storage::disk('public')->delete($theme->logo);
                }

                $result = $imageService->optimizeAndStore($request->file('logo'), 'themes/logos');
                $validated['logo'] = $result['original'];

                if ($vendor->logo && $vendor->logo !== $theme->logo) {
                    Storage::disk('public')->delete($vendor->logo);
                }
                $vendor->update(['logo' => $validated['logo']]);
            }

            if ($request->hasFile('profile_image')) {
                if ($vendor->profile_image) {
                    Storage::disk('public')->delete($vendor->profile_image);
                }

                $result = $imageService->optimizeAndStore($request->file('profile_image'), 'vendors/profile-images');
                $vendor->update(['profile_image' => $result['original']]);
            }

            if ($request->hasFile('favicon')) {
                if ($theme->favicon) {
                    Storage::disk('public')->delete($theme->favicon);
                }

                $result = $imageService->optimizeAndStore($request->file('favicon'), 'themes/favicons');
                $validated['favicon'] = $result['original'];
            }

            $validated['social_links'] = array_filter($validated['social_links'] ?? []);

            if ($theme->exists) {
                $theme->update($validated);
            } else {
                $theme->fill($validated);
                $theme->save();
            }

            DB::commit();

            return redirect()->route('vendor.theme.index')
                ->with('success', 'Store theme updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'An error occurred while updating your theme.'])->withInput();
        }
    }

    public function deleteImage(Request $request, string $type): RedirectResponse
    {
        $vendor = auth()->user()->vendor;
        $theme = $vendor->theme;

        $allowedTypes = ['banner_image', 'logo', 'favicon', 'profile_image'];

        if (! in_array($type, $allowedTypes)) {
            return back()->withErrors(['error' => 'Invalid image type.']);
        }

        if ($type === 'profile_image') {
            if ($vendor->profile_image) {
                Storage::disk('public')->delete($vendor->profile_image);
                $vendor->update(['profile_image' => null]);

                return back()->with('success', 'Profile image deleted successfully.');
            }

            return back()->withErrors(['error' => 'No image to delete.']);
        }

        if (! $theme) {
            return back()->withErrors(['error' => 'No theme found.']);
        }

        if ($theme->$type) {
            Storage::disk('public')->delete($theme->$type);
            $theme->update([$type => null]);

            if ($type === 'logo') {
                $vendor->update(['logo' => null]);
            }

            return back()->with('success', ucfirst(str_replace('_', ' ', $type)).' deleted successfully.');
        }

        return back()->withErrors(['error' => 'No image to delete.']);
    }
}
