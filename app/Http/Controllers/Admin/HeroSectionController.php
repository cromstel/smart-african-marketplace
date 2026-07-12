<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use App\Models\MediaLibrary;
use App\Models\Setting;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;

class HeroSectionController extends Controller
{
    public function index(Request $request)
    {
        $tab = 'hero';

        $settings = [
            'site_theme' => Setting::get('site_theme', 'system'),
            'brand_color_from' => Setting::get('brand_color_from', '#ea580c'),
            'brand_color_to' => Setting::get('brand_color_to', '#dc2626'),
            'theme_preset' => Setting::get('theme_preset', 'modern-commerce'),
            'theme_accent_color' => Setting::get('theme_accent_color', '#f97316'),
            'theme_link_color' => Setting::get('theme_link_color', '#ea580c'),
            'theme_muted_text_color' => Setting::get('theme_muted_text_color', '#6b7280'),
            'theme_surface_style' => Setting::get('theme_surface_style', 'soft'),
            'theme_content_layout' => Setting::get('theme_content_layout', 'wide'),
            'theme_container_width' => (int) Setting::get('theme_container_width', 1600),
            'theme_font_body' => Setting::get('theme_font_body', 'Inter'),
            'theme_font_heading' => Setting::get('theme_font_heading', 'Poppins'),
            'theme_font_scale' => Setting::get('theme_font_scale', 'md'),
            'theme_button_radius' => (int) Setting::get('theme_button_radius', 12),
            'theme_header_preset' => Setting::get('theme_header_preset', 'centered'),
            'theme_footer_preset' => Setting::get('theme_footer_preset', 'columns'),
            'theme_sticky_header' => (bool) Setting::get('theme_sticky_header', true),
        ];
        $heroSections = HeroSection::orderBy('order')->get();

        return view('admin.appearance.customization', compact('settings', 'heroSections', 'tab'));
    }

    public function create()
    {
        return $this->index(request());
    }

    public function store(Request $request, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'page_identifier' => 'required|string|unique:hero_sections,page_identifier|max:255',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
            'background_media_id' => 'nullable|integer|exists:media_library,id',
            'background_type' => 'required|in:gradient,image',
            'gradient_from' => 'nullable|string|max:7',
            'gradient_to' => 'nullable|string|max:7',
            'statistics' => 'nullable|json',
            'primary_button_text' => 'nullable|string|max:255',
            'primary_button_link' => 'nullable|string|max:255',
            'secondary_button_text' => 'nullable|string|max:255',
            'secondary_button_link' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('background_image')) {
            $result = $imageService->uploadAndOptimize(
                $request->file('background_image'),
                'hero-sections',
                1600,
                900,
                'hero_section'
            );
            $validated['background_image'] = $result['original'];
        } elseif ($request->filled('background_media_id')) {
            $selectedMedia = MediaLibrary::query()
                ->images()
                ->find($request->integer('background_media_id'));
            if ($selectedMedia) {
                $validated['background_image'] = $selectedMedia->file_path;
            }
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['statistics'] = $request->statistics ? json_decode($request->statistics, true) : null;

        HeroSection::create($validated);

        return redirect()->route('admin.hero-sections.index')
            ->with('success', 'Hero section created successfully');
    }

    public function edit(HeroSection $heroSection)
    {
        return $this->index(request());
    }

    public function update(Request $request, HeroSection $heroSection, ImageOptimizationService $imageService)
    {
        $validated = $request->validate([
            'page_identifier' => 'required|string|max:255|unique:hero_sections,page_identifier,'.$heroSection->id,
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
            'background_media_id' => 'nullable|integer|exists:media_library,id',
            'background_type' => 'required|in:gradient,image',
            'gradient_from' => 'nullable|string|max:7',
            'gradient_to' => 'nullable|string|max:7',
            'statistics' => 'nullable|json',
            'primary_button_text' => 'nullable|string|max:255',
            'primary_button_link' => 'nullable|string|max:255',
            'secondary_button_text' => 'nullable|string|max:255',
            'secondary_button_link' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('background_image')) {
            if ($heroSection->background_image) {
                $imageService->deleteImage($heroSection->background_image);
            }
            $result = $imageService->uploadAndOptimize(
                $request->file('background_image'),
                'hero-sections',
                1600,
                900,
                'hero_section',
                $heroSection->id
            );
            $validated['background_image'] = $result['original'];
        } elseif ($request->filled('background_media_id')) {
            $selectedMedia = MediaLibrary::query()
                ->images()
                ->find($request->integer('background_media_id'));
            if ($selectedMedia) {
                $validated['background_image'] = $selectedMedia->file_path;
            }
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['statistics'] = $request->statistics ? json_decode($request->statistics, true) : null;

        $heroSection->update($validated);

        return redirect()->route('admin.hero-sections.index')
            ->with('success', 'Hero section updated successfully');
    }

    public function destroy(HeroSection $heroSection, ImageOptimizationService $imageService)
    {
        if ($heroSection->background_image) {
            $imageService->deleteImage($heroSection->background_image);
        }

        $heroSection->delete();

        return redirect()->route('admin.hero-sections.index')
            ->with('success', 'Hero section deleted successfully');
    }
}
