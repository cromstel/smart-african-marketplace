@extends('vendor.layout')

@section('title', 'Store Theme Customization')
@section('header', 'Customize Your Store Theme')

@section('content')
<div class="max-w-5xl">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Store Theme Customization</h3>
                <p class="text-sm text-gray-400 mt-1">Personalize your store's appearance with colors, fonts, and branding.</p>
            </div>
            <a href="{{ auth()->user()->vendor->storefrontUrl() }}" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm transition">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Preview Store
            </a>
        </div>
    </div>

    <form action="{{ route('vendor.theme.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Brand Colors</h2>
            <div class="grid grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Primary Color</label>
                    <input type="color" name="primary_color" value="{{ old('primary_color', $theme->primary_color) }}" class="w-full h-12 rounded cursor-pointer">
                    <input type="text" name="primary_color_hex" value="{{ old('primary_color', $theme->primary_color) }}" readonly class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded px-2 py-1 text-xs text-center">
                    @error('primary_color') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Secondary Color</label>
                    <input type="color" name="secondary_color" value="{{ old('secondary_color', $theme->secondary_color) }}" class="w-full h-12 rounded cursor-pointer">
                    <input type="text" name="secondary_color_hex" value="{{ old('secondary_color', $theme->secondary_color) }}" readonly class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded px-2 py-1 text-xs text-center">
                    @error('secondary_color') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Accent Color</label>
                    <input type="color" name="accent_color" value="{{ old('accent_color', $theme->accent_color) }}" class="w-full h-12 rounded cursor-pointer">
                    <input type="text" name="accent_color_hex" value="{{ old('accent_color', $theme->accent_color) }}" readonly class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded px-2 py-1 text-xs text-center">
                    @error('accent_color') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Text Color</label>
                    <input type="color" name="text_color" value="{{ old('text_color', $theme->text_color) }}" class="w-full h-12 rounded cursor-pointer">
                    <input type="text" name="text_color_hex" value="{{ old('text_color', $theme->text_color) }}" readonly class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded px-2 py-1 text-xs text-center">
                    @error('text_color') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Background</label>
                    <input type="color" name="background_color" value="{{ old('background_color', $theme->background_color) }}" class="w-full h-12 rounded cursor-pointer">
                    <input type="text" name="background_color_hex" value="{{ old('background_color', $theme->background_color) }}" readonly class="mt-1 w-full bg-gray-700 border border-gray-600 text-white rounded px-2 py-1 text-xs text-center">
                    @error('background_color') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Typography</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Body Font</label>
                    <select name="font_family" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="Inter" {{ $theme->font_family == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Arial" {{ $theme->font_family == 'Arial' ? 'selected' : '' }}>Arial</option>
                        <option value="Helvetica" {{ $theme->font_family == 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                        <option value="Georgia" {{ $theme->font_family == 'Georgia' ? 'selected' : '' }}>Georgia</option>
                        <option value="Times New Roman" {{ $theme->font_family == 'Times New Roman' ? 'selected' : '' }}>Times New Roman</option>
                        <option value="Roboto" {{ $theme->font_family == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ $theme->font_family == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                    </select>
                    @error('font_family') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Heading Font</label>
                    <select name="font_heading" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="Inter" {{ $theme->font_heading == 'Inter' ? 'selected' : '' }}>Inter</option>
                        <option value="Arial" {{ $theme->font_heading == 'Arial' ? 'selected' : '' }}>Arial</option>
                        <option value="Helvetica" {{ $theme->font_heading == 'Helvetica' ? 'selected' : '' }}>Helvetica</option>
                        <option value="Georgia" {{ $theme->font_heading == 'Georgia' ? 'selected' : '' }}>Georgia</option>
                        <option value="Times New Roman" {{ $theme->font_heading == 'Times New Roman' ? 'selected' : '' }}>Times New Roman</option>
                        <option value="Roboto" {{ $theme->font_heading == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                        <option value="Open Sans" {{ $theme->font_heading == 'Open Sans' ? 'selected' : '' }}>Open Sans</option>
                    </select>
                    @error('font_heading') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Branding Assets</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Store Profile Image (Recommended: 200x200px)</label>
                    @php
                        $vendorProfileImage = auth()->user()->vendor->profile_image ?? null;
                    @endphp
                    @if($vendorProfileImage)
                        <div class="mb-2">
                            <img src="{{ '/storage/' . ltrim($vendorProfileImage ?? '', '/') }}" alt="Profile image" class="h-20 w-20 rounded-full border border-gray-600 object-cover">
                            <form action="{{ route('vendor.theme.delete-image', 'profile_image') }}" method="POST" class="inline" onsubmit="return confirm('Delete profile image?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mt-2 text-red-400 hover:text-red-300 text-xs">Remove Profile Image</button>
                            </form>
                        </div>
                    @endif
                    <input type="file" name="profile_image" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('profile_image') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Store Banner (Recommended: 1920x400px)</label>
                    @if($theme->banner_image)
                        <div class="mb-2">
                            <img src="{{ '/storage/' . ltrim($theme->banner_image ?? '', '/') }}" alt="Banner" class="h-32 rounded border border-gray-600">
                            <form action="{{ route('vendor.theme.delete-image', 'banner_image') }}" method="POST" class="inline" onsubmit="return confirm('Delete banner image?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mt-2 text-red-400 hover:text-red-300 text-xs">Remove Banner</button>
                            </form>
                        </div>
                    @endif
                    <input type="file" name="banner_image" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('banner_image') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Store Logo (Recommended: 200x200px)</label>
                        @if($theme->logo)
                            <div class="mb-2">
                                <img src="{{ '/storage/' . ltrim($theme->logo ?? '', '/') }}" alt="Logo" class="h-20 rounded border border-gray-600">
                                <form action="{{ route('vendor.theme.delete-image', 'logo') }}" method="POST" class="inline" onsubmit="return confirm('Delete logo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mt-2 text-red-400 hover:text-red-300 text-xs">Remove Logo</button>
                                </form>
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @error('logo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Favicon (Recommended: 32x32px)</label>
                        @if($theme->favicon)
                            <div class="mb-2">
                                <img src="{{ '/storage/' . ltrim($theme->favicon ?? '', '/') }}" alt="Favicon" class="h-8 rounded border border-gray-600">
                                <form action="{{ route('vendor.theme.delete-image', 'favicon') }}" method="POST" class="inline" onsubmit="return confirm('Delete favicon?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mt-2 text-red-400 hover:text-red-300 text-xs">Remove Favicon</button>
                                </form>
                            </div>
                        @endif
                        <input type="file" name="favicon" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @error('favicon') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Layout & Display</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Product Layout Style</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center p-3 bg-gray-700 rounded border border-gray-600 cursor-pointer hover:bg-gray-600">
                            <input type="radio" name="layout_style" value="grid" {{ $theme->layout_style == 'grid' ? 'checked' : '' }} class="mr-2">
                            <span class="text-white">Grid</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-700 rounded border border-gray-600 cursor-pointer hover:bg-gray-600">
                            <input type="radio" name="layout_style" value="list" {{ $theme->layout_style == 'list' ? 'checked' : '' }} class="mr-2">
                            <span class="text-white">List</span>
                        </label>
                        <label class="flex items-center p-3 bg-gray-700 rounded border border-gray-600 cursor-pointer hover:bg-gray-600">
                            <input type="radio" name="layout_style" value="masonry" {{ $theme->layout_style == 'masonry' ? 'checked' : '' }} class="mr-2">
                            <span class="text-white">Masonry</span>
                        </label>
                    </div>
                    @error('layout_style') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="show_categories" value="1" {{ $theme->show_categories ? 'checked' : '' }} class="w-4 h-4 text-orange-600 bg-gray-700 border-gray-600 rounded focus:ring-orange-500">
                        <span class="ml-2 text-sm text-gray-300">Show Categories</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_filters" value="1" {{ $theme->show_filters ? 'checked' : '' }} class="w-4 h-4 text-orange-600 bg-gray-700 border-gray-600 rounded focus:ring-orange-500">
                        <span class="ml-2 text-sm text-gray-300">Show Filters</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="show_search" value="1" {{ $theme->show_search ? 'checked' : '' }} class="w-4 h-4 text-orange-600 bg-gray-700 border-gray-600 rounded focus:ring-orange-500">
                        <span class="ml-2 text-sm text-gray-300">Show Search</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Store Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">About Your Store</label>
                    <textarea name="about_text" rows="5" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Tell customers about your store, your mission, and what makes you unique...">{{ old('about_text', $theme->about_text) }}</textarea>
                    @error('about_text') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Contact Information</label>
                    <textarea name="contact_info" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Phone, email, business hours, etc.">{{ old('contact_info', $theme->contact_info) }}</textarea>
                    @error('contact_info') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Social Media Links</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Facebook URL</label>
                    <input type="url" name="social_links[facebook]" value="{{ old('social_links.facebook', $theme->getSocialLinksArray()['facebook'] ?? '') }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="https://facebook.com/yourpage">
                    @error('social_links.facebook') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Twitter URL</label>
                    <input type="url" name="social_links[twitter]" value="{{ old('social_links.twitter', $theme->getSocialLinksArray()['twitter'] ?? '') }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="https://twitter.com/yourhandle">
                    @error('social_links.twitter') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Instagram URL</label>
                    <input type="url" name="social_links[instagram]" value="{{ old('social_links.instagram', $theme->getSocialLinksArray()['instagram'] ?? '') }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="https://instagram.com/yourprofile">
                    @error('social_links.instagram') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">LinkedIn URL</label>
                    <input type="url" name="social_links[linkedin]" value="{{ old('social_links.linkedin', $theme->getSocialLinksArray()['linkedin'] ?? '') }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="https://linkedin.com/company/yourcompany">
                    @error('social_links.linkedin') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">WhatsApp Number</label>
                    <input type="text" name="social_links[whatsapp]" value="{{ old('social_links.whatsapp', $theme->getSocialLinksArray()['whatsapp'] ?? '') }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="+234xxxxxxxxxx">
                    @error('social_links.whatsapp') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Advanced Customization</h2>
            <p class="text-sm text-gray-400 mb-4">Add custom CSS and JavaScript to further customize your store. Use with caution.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Custom CSS</label>
                    <textarea name="custom_css" rows="6" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm"
                              placeholder=".custom-class { color: #ff0000; }">{{ old('custom_css', $theme->custom_css) }}</textarea>
                    @error('custom_css') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Custom JavaScript</label>
                    <textarea name="custom_js" rows="6" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm"
                              placeholder="console.log('Hello World');">{{ old('custom_js', $theme->custom_js) }}</textarea>
                    @error('custom_js') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ $theme->is_active ? 'checked' : '' }} class="w-4 h-4 text-orange-600 bg-gray-700 border-gray-600 rounded focus:ring-orange-500">
                <span class="ml-2 text-sm text-gray-300">Activate custom theme (if unchecked, default theme will be used)</span>
            </label>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-medium">
                Save Theme
            </button>
            <a href="{{ route('vendor.dashboard') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        const hexInput = colorInput.nextElementSibling;
        
        colorInput.addEventListener('input', function() {
            hexInput.value = this.value;
        });
        
        hexInput.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorInput.value = this.value;
            }
        });
    });
</script>
@endpush

@endsection

