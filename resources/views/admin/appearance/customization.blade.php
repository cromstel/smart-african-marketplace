@extends('admin.layout')

@section('title', 'Customization')
@section('header', 'Customization')

@php
    $tabs = [
        'general' => 'General',
        'colors' => 'Colors',
        'typography' => 'Typography',
        'layout' => 'Layout',
        'hero' => 'Hero Sections',
    ];
    $pageIdentifiers = ['home', 'shop', 'salon', 'about', 'contact', 'blog', 'pricing'];
@endphp

@section('content')
<div class="space-y-6">
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5">
        <h3 class="text-xl text-white font-semibold">Customizer</h3>
        <p class="text-gray-400 mt-1">Advanced controls for global look-and-feel, layout, and hero sections.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <aside class="xl:col-span-3">
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-3 space-y-1 sticky top-6">
                @foreach($tabs as $tabId => $label)
                    <a
                        href="{{ route('admin.appearance.customization', ['tab' => $tabId]) }}"
                        class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-sm {{ $tab === $tabId ? 'bg-brand-gradient text-white' : 'text-gray-300 hover:bg-gray-700' }}"
                    >
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </aside>

        <div class="xl:col-span-9 space-y-6">
            @if($errors->any())
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($tab !== 'hero')
                <form method="POST" action="{{ route('admin.appearance.customization.update', ['tab' => $tab]) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    @if($tab === 'general')
                        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 space-y-5">
                            <h4 class="text-white text-lg font-semibold">General Theme</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Theme Mode</label>
                                    <select name="site_theme" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        <option value="system" {{ $settings['site_theme'] === 'system' ? 'selected' : '' }}>System</option>
                                        <option value="light" {{ $settings['site_theme'] === 'light' ? 'selected' : '' }}>Light</option>
                                        <option value="dark" {{ $settings['site_theme'] === 'dark' ? 'selected' : '' }}>Dark</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Theme Preset</label>
                                    <select name="theme_preset" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        <option value="modern-commerce" {{ $settings['theme_preset'] === 'modern-commerce' ? 'selected' : '' }}>Modern Commerce</option>
                                        <option value="minimal-editorial" {{ $settings['theme_preset'] === 'minimal-editorial' ? 'selected' : '' }}>Minimal Editorial</option>
                                        <option value="dark-pro" {{ $settings['theme_preset'] === 'dark-pro' ? 'selected' : '' }}>Dark Pro</option>
                                        <option value="classic" {{ $settings['theme_preset'] === 'classic' ? 'selected' : '' }}>Classic</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="theme_sticky_header" value="1" {{ $settings['theme_sticky_header'] ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-orange-500">
                                <span class="ml-2 text-sm text-gray-300">Sticky header on public pages</span>
                            </div>
                        </div>
                    @endif

                    @if($tab === 'colors')
                        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 space-y-5">
                            <h4 class="text-white text-lg font-semibold">Color System</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Brand Gradient Start</label>
                                    <input type="color" name="brand_color_from" value="{{ old('brand_color_from', $settings['brand_color_from']) }}" class="w-full h-11 bg-gray-700 border border-gray-600 rounded-lg px-2">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Brand Gradient End</label>
                                    <input type="color" name="brand_color_to" value="{{ old('brand_color_to', $settings['brand_color_to']) }}" class="w-full h-11 bg-gray-700 border border-gray-600 rounded-lg px-2">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Accent Color</label>
                                    <input type="color" name="theme_accent_color" value="{{ old('theme_accent_color', $settings['theme_accent_color']) }}" class="w-full h-11 bg-gray-700 border border-gray-600 rounded-lg px-2">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Link Color</label>
                                    <input type="color" name="theme_link_color" value="{{ old('theme_link_color', $settings['theme_link_color']) }}" class="w-full h-11 bg-gray-700 border border-gray-600 rounded-lg px-2">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Muted Text Color</label>
                                    <input type="color" name="theme_muted_text_color" value="{{ old('theme_muted_text_color', $settings['theme_muted_text_color']) }}" class="w-full h-11 bg-gray-700 border border-gray-600 rounded-lg px-2">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Surface Style</label>
                                    <select name="theme_surface_style" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        <option value="soft" {{ $settings['theme_surface_style'] === 'soft' ? 'selected' : '' }}>Soft</option>
                                        <option value="flat" {{ $settings['theme_surface_style'] === 'flat' ? 'selected' : '' }}>Flat</option>
                                        <option value="glass" {{ $settings['theme_surface_style'] === 'glass' ? 'selected' : '' }}>Glass</option>
                                    </select>
                                </div>
                            </div>
                            <div class="rounded-lg h-20 border border-gray-700" style="background: linear-gradient(to right, {{ old('brand_color_from', $settings['brand_color_from']) }}, {{ old('brand_color_to', $settings['brand_color_to']) }});"></div>
                        </div>
                    @endif

                    @if($tab === 'typography')
                        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 space-y-5">
                            <h4 class="text-white text-lg font-semibold">Typography</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Body Font</label>
                                    <select name="theme_font_body" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        @foreach(['Inter', 'Roboto', 'Open Sans', 'Lora', 'Source Sans Pro'] as $font)
                                            <option value="{{ $font }}" {{ $settings['theme_font_body'] === $font ? 'selected' : '' }}>{{ $font }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Heading Font</label>
                                    <select name="theme_font_heading" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        @foreach(['Poppins', 'Montserrat', 'Playfair Display', 'Merriweather', 'Roboto'] as $font)
                                            <option value="{{ $font }}" {{ $settings['theme_font_heading'] === $font ? 'selected' : '' }}>{{ $font }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Font Scale</label>
                                    <select name="theme_font_scale" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        <option value="sm" {{ $settings['theme_font_scale'] === 'sm' ? 'selected' : '' }}>Compact</option>
                                        <option value="md" {{ $settings['theme_font_scale'] === 'md' ? 'selected' : '' }}>Balanced</option>
                                        <option value="lg" {{ $settings['theme_font_scale'] === 'lg' ? 'selected' : '' }}>Comfortable</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($tab === 'layout')
                        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 space-y-5">
                            <h4 class="text-white text-lg font-semibold">Layout and Structure</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Content Layout</label>
                                    <select name="theme_content_layout" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        <option value="wide" {{ $settings['theme_content_layout'] === 'wide' ? 'selected' : '' }}>Wide</option>
                                        <option value="boxed" {{ $settings['theme_content_layout'] === 'boxed' ? 'selected' : '' }}>Boxed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Container Width (px)</label>
                                    <input type="number" min="1024" max="1600" name="theme_container_width" value="{{ old('theme_container_width', $settings['theme_container_width']) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Header Preset</label>
                                    <select name="theme_header_preset" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        <option value="centered" {{ $settings['theme_header_preset'] === 'centered' ? 'selected' : '' }}>Centered</option>
                                        <option value="split" {{ $settings['theme_header_preset'] === 'split' ? 'selected' : '' }}>Split</option>
                                        <option value="minimal" {{ $settings['theme_header_preset'] === 'minimal' ? 'selected' : '' }}>Minimal</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Footer Preset</label>
                                    <select name="theme_footer_preset" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        <option value="columns" {{ $settings['theme_footer_preset'] === 'columns' ? 'selected' : '' }}>Columns</option>
                                        <option value="minimal" {{ $settings['theme_footer_preset'] === 'minimal' ? 'selected' : '' }}>Minimal</option>
                                        <option value="dense" {{ $settings['theme_footer_preset'] === 'dense' ? 'selected' : '' }}>Dense</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-300 mb-2">Button Radius (px)</label>
                                    <input type="number" min="0" max="28" name="theme_button_radius" value="{{ old('theme_button_radius', $settings['theme_button_radius']) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                </div>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="bg-brand-gradient text-white px-5 py-2 rounded-lg">Save Customization</button>
                </form>
            @else
                <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h4 class="text-white text-lg font-semibold">Hero Sections</h4>
                            <p class="text-gray-400 text-sm mt-1">Manage hero sections directly in customization. One section per page identifier.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.appearance.customization.hero.store') }}" enctype="multipart/form-data" class="border border-gray-700 rounded-lg p-4 space-y-4">
                        @csrf
                        <h5 class="text-white font-medium">Add Hero Section</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Page Identifier</label>
                                <select name="page_identifier" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                    @foreach($pageIdentifiers as $identifier)
                                        <option value="{{ $identifier }}">{{ ucfirst($identifier) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Order</label>
                                <input type="number" name="order" value="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm text-gray-300 mb-2">Title</label>
                                <input type="text" name="title" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Subtitle</label>
                                <input type="text" name="subtitle" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Background Type</label>
                                <select name="background_type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                    <option value="gradient">Gradient</option>
                                    <option value="image">Image</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Gradient From</label>
                                <input type="text" name="gradient_from" value="#ea580c" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Gradient To</label>
                                <input type="text" name="gradient_to" value="#dc2626" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm text-gray-300 mb-2">Description</label>
                                <textarea name="description" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm text-gray-300 mb-2">Background Image</label>
                                <input type="file" name="background_image" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-gray-300 rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Primary Button Text</label>
                                <input type="text" name="primary_button_text" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Primary Button Link</label>
                                <input type="text" name="primary_button_link" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Secondary Button Text</label>
                                <input type="text" name="secondary_button_text" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-300 mb-2">Secondary Button Link</label>
                                <input type="text" name="secondary_button_link" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            </div>
                            <div class="md:col-span-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-600 bg-gray-700 text-orange-500">
                                    <span class="ml-2 text-sm text-gray-300">Active</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="bg-brand-gradient text-white px-4 py-2 rounded-lg">Create Hero Section</button>
                    </form>

                    <div class="space-y-4">
                        @forelse($heroSections as $section)
                            <details class="border border-gray-700 rounded-lg p-4 bg-gray-900/30">
                                <summary class="cursor-pointer text-white font-medium">
                                    {{ ucfirst($section->page_identifier) }}: {{ $section->title }}
                                    <span class="text-xs text-gray-400 ml-2">({{ $section->is_active ? 'Active' : 'Inactive' }})</span>
                                </summary>
                                <div class="mt-4 space-y-3">
                                    <form method="POST" action="{{ route('admin.appearance.customization.hero.update', $section) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Page Identifier</label>
                                            <select name="page_identifier" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                                @foreach($pageIdentifiers as $identifier)
                                                    <option value="{{ $identifier }}" {{ $section->page_identifier === $identifier ? 'selected' : '' }}>{{ ucfirst($identifier) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Order</label>
                                            <input type="number" name="order" value="{{ $section->order }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm text-gray-300 mb-2">Title</label>
                                            <input type="text" name="title" value="{{ $section->title }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Subtitle</label>
                                            <input type="text" name="subtitle" value="{{ $section->subtitle }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Background Type</label>
                                            <select name="background_type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                                <option value="gradient" {{ $section->background_type === 'gradient' ? 'selected' : '' }}>Gradient</option>
                                                <option value="image" {{ $section->background_type === 'image' ? 'selected' : '' }}>Image</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Gradient From</label>
                                            <input type="text" name="gradient_from" value="{{ $section->gradient_from }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Gradient To</label>
                                            <input type="text" name="gradient_to" value="{{ $section->gradient_to }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm text-gray-300 mb-2">Description</label>
                                            <textarea name="description" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ $section->description }}</textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm text-gray-300 mb-2">Replace Background Image</label>
                                            <input type="file" name="background_image" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-gray-300 rounded-lg px-4 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Primary Button Text</label>
                                            <input type="text" name="primary_button_text" value="{{ $section->primary_button_text }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Primary Button Link</label>
                                            <input type="text" name="primary_button_link" value="{{ $section->primary_button_link }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Secondary Button Text</label>
                                            <input type="text" name="secondary_button_text" value="{{ $section->secondary_button_text }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div>
                                            <label class="block text-sm text-gray-300 mb-2">Secondary Button Link</label>
                                            <input type="text" name="secondary_button_link" value="{{ $section->secondary_button_link }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                                        </div>
                                        <div class="md:col-span-2 flex items-center justify-between gap-4">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="is_active" value="1" {{ $section->is_active ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-orange-500">
                                                <span class="ml-2 text-sm text-gray-300">Active</span>
                                            </label>
                                            <button type="submit" class="bg-brand-gradient text-white px-4 py-2 rounded-lg">Save Hero</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('admin.appearance.customization.hero.destroy', $section) }}" onsubmit="return confirm('Delete this hero section?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-700 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Delete Hero</button>
                                    </form>
                                </div>
                            </details>
                        @empty
                            <div class="text-gray-400 text-sm">No hero sections yet.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

