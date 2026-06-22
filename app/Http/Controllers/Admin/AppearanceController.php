<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use App\Models\Setting;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppearanceController extends Controller
{
    public function theme(): View
    {
        $settings = [
            'site_theme' => Setting::get('site_theme', 'system'),
            'theme_preset' => Setting::get('theme_preset', 'modern-commerce'),
            'theme_header_preset' => Setting::get('theme_header_preset', 'centered'),
            'theme_footer_preset' => Setting::get('theme_footer_preset', 'columns'),
            'theme_content_layout' => Setting::get('theme_content_layout', 'wide'),
            'theme_container_width' => (int) Setting::get('theme_container_width', 1600),
            'theme_font_body' => Setting::get('theme_font_body', 'Inter'),
            'theme_font_heading' => Setting::get('theme_font_heading', 'Poppins'),
        ];

        return view('admin.appearance.theme', compact('settings'));
    }

    public function customization(Request $request): View
    {
        $tab = (string) $request->query('tab', 'general');
        if (! in_array($tab, ['general', 'colors', 'typography', 'layout', 'hero'], true)) {
            $tab = 'general';
        }

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

    public function updateCustomization(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_theme' => 'nullable|in:light,dark,system',
            'brand_color_from' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'brand_color_to' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_preset' => 'nullable|in:modern-commerce,minimal-editorial,dark-pro,classic',
            'theme_accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_link_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_muted_text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_surface_style' => 'nullable|in:soft,flat,glass',
            'theme_content_layout' => 'nullable|in:wide,boxed',
            'theme_container_width' => 'nullable|integer|min:1024|max:1600',
            'theme_font_body' => 'nullable|string|max:80',
            'theme_font_heading' => 'nullable|string|max:80',
            'theme_font_scale' => 'nullable|in:sm,md,lg',
            'theme_button_radius' => 'nullable|integer|min:0|max:28',
            'theme_header_preset' => 'nullable|in:centered,split,minimal',
            'theme_footer_preset' => 'nullable|in:columns,minimal,dense',
            'theme_sticky_header' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'theme_sticky_header') {
                Setting::set($key, (bool) $value);

                continue;
            }
            Setting::set($key, $value);
        }

        // Checkbox can be absent when unchecked.
        if (! $request->has('theme_sticky_header')) {
            Setting::set('theme_sticky_header', false);
        }

        return back()->with('success', 'Appearance updated successfully.');
    }

    public function applyThemePreset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preset' => ['required', Rule::in(['modern-commerce', 'minimal-editorial', 'dark-pro', 'classic'])],
        ]);

        $presetDefaults = match ($validated['preset']) {
            'minimal-editorial' => [
                'theme_preset' => 'minimal-editorial',
                'site_theme' => 'light',
                'brand_color_from' => '#b45309',
                'brand_color_to' => '#78350f',
                'theme_accent_color' => '#b45309',
                'theme_link_color' => '#92400e',
                'theme_muted_text_color' => '#78716c',
                'theme_surface_style' => 'flat',
                'theme_content_layout' => 'wide',
                'theme_container_width' => 1240,
                'theme_font_body' => 'Lora',
                'theme_font_heading' => 'Playfair Display',
                'theme_font_scale' => 'md',
                'theme_button_radius' => 6,
                'theme_header_preset' => 'minimal',
                'theme_footer_preset' => 'minimal',
                'theme_sticky_header' => true,
            ],
            'dark-pro' => [
                'theme_preset' => 'dark-pro',
                'site_theme' => 'dark',
                'brand_color_from' => '#fb7185',
                'brand_color_to' => '#9333ea',
                'theme_accent_color' => '#f43f5e',
                'theme_link_color' => '#fda4af',
                'theme_muted_text_color' => '#94a3b8',
                'theme_surface_style' => 'glass',
                'theme_content_layout' => 'wide',
                'theme_container_width' => 1320,
                'theme_font_body' => 'Inter',
                'theme_font_heading' => 'Montserrat',
                'theme_font_scale' => 'md',
                'theme_button_radius' => 14,
                'theme_header_preset' => 'split',
                'theme_footer_preset' => 'dense',
                'theme_sticky_header' => true,
            ],
            'classic' => [
                'theme_preset' => 'classic',
                'site_theme' => 'system',
                'brand_color_from' => '#2563eb',
                'brand_color_to' => '#1d4ed8',
                'theme_accent_color' => '#2563eb',
                'theme_link_color' => '#1e40af',
                'theme_muted_text_color' => '#6b7280',
                'theme_surface_style' => 'soft',
                'theme_content_layout' => 'boxed',
                'theme_container_width' => 1200,
                'theme_font_body' => 'Roboto',
                'theme_font_heading' => 'Roboto',
                'theme_font_scale' => 'sm',
                'theme_button_radius' => 4,
                'theme_header_preset' => 'centered',
                'theme_footer_preset' => 'columns',
                'theme_sticky_header' => false,
            ],
            default => [
                'theme_preset' => 'modern-commerce',
                'site_theme' => 'system',
                'brand_color_from' => '#ea580c',
                'brand_color_to' => '#dc2626',
                'theme_accent_color' => '#f97316',
                'theme_link_color' => '#ea580c',
                'theme_muted_text_color' => '#6b7280',
                'theme_surface_style' => 'soft',
                'theme_content_layout' => 'wide',
                'theme_container_width' => 1600,
                'theme_font_body' => 'Inter',
                'theme_font_heading' => 'Poppins',
                'theme_font_scale' => 'md',
                'theme_button_radius' => 12,
                'theme_header_preset' => 'centered',
                'theme_footer_preset' => 'columns',
                'theme_sticky_header' => true,
            ],
        };

        foreach ($presetDefaults as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()
            ->route('admin.appearance.customization', ['tab' => 'general'])
            ->with('success', 'Theme preset applied successfully.');
    }

    public function storeHero(Request $request, ImageOptimizationService $imageService): RedirectResponse
    {
        $validated = $this->validateHeroSection($request);
        $validated = $this->resolveHeroMedia($request, $validated, $imageService);
        $validated['is_active'] = $request->has('is_active');
        $validated['statistics'] = $request->filled('statistics') ? json_decode((string) $request->input('statistics'), true) : null;

        HeroSection::create($validated);

        return redirect()
            ->route('admin.appearance.customization', ['tab' => 'hero'])
            ->with('success', 'Hero section created successfully.');
    }

    public function updateHero(Request $request, HeroSection $heroSection, ImageOptimizationService $imageService): RedirectResponse
    {
        $validated = $this->validateHeroSection($request, $heroSection->id);
        $validated = $this->resolveHeroMedia($request, $validated, $imageService, $heroSection);
        $validated['is_active'] = $request->has('is_active');
        $validated['statistics'] = $request->filled('statistics') ? json_decode((string) $request->input('statistics'), true) : null;

        $heroSection->update($validated);

        return redirect()
            ->route('admin.appearance.customization', ['tab' => 'hero'])
            ->with('success', 'Hero section updated successfully.');
    }

    public function destroyHero(HeroSection $heroSection, ImageOptimizationService $imageService): RedirectResponse
    {
        if ($heroSection->background_image) {
            $imageService->deleteImage($heroSection->background_image);
        }

        $heroSection->delete();

        return redirect()
            ->route('admin.appearance.customization', ['tab' => 'hero'])
            ->with('success', 'Hero section deleted successfully.');
    }

    private function validateHeroSection(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'page_identifier' => ['required', 'string', 'max:255', Rule::unique('hero_sections', 'page_identifier')->ignore($ignoreId)],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'background_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,avif', 'max:2048'],
            'background_type' => ['required', 'in:gradient,image'],
            'gradient_from' => ['nullable', 'string', 'max:7'],
            'gradient_to' => ['nullable', 'string', 'max:7'],
            'statistics' => ['nullable', 'json'],
            'primary_button_text' => ['nullable', 'string', 'max:255'],
            'primary_button_link' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:255'],
            'secondary_button_link' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
    }

    private function resolveHeroMedia(
        Request $request,
        array $validated,
        ImageOptimizationService $imageService,
        ?HeroSection $heroSection = null
    ): array {
        if (! $request->hasFile('background_image')) {
            return $validated;
        }

        if ($heroSection && $heroSection->background_image) {
            $imageService->deleteImage($heroSection->background_image);
        }

        $result = $imageService->uploadAndOptimize(
            $request->file('background_image'),
            'hero-sections',
            1600,
            900,
            'hero_section',
            $heroSection?->id
        );
        $validated['background_image'] = $result['original'];

        return $validated;
    }
}
