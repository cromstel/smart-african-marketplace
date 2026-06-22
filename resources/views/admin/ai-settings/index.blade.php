@extends('admin.layout')

@section('title', 'AI Settings')
@section('header', 'AI Content Generation Settings')

@section('content')
<div class="max-w-4xl">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-6">
        <div class="flex items-center mb-4">
            <svg class="w-8 h-8 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <div>
                <h3 class="text-lg font-semibold text-white">AI Content Generation</h3>
                <p class="text-sm text-gray-400">Configure automated content generation features for vendors</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.ai-settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">General Settings</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                    <div>
                        <label class="text-white font-medium">Enable AI Features</label>
                        <p class="text-sm text-gray-400 mt-1">Allow vendors to use AI content generation for products</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="ai_enabled" value="1" {{ ($settings['ai_enabled']->value ?? 1) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                    <div>
                        <label class="text-white font-medium">Auto-Suggest Content</label>
                        <p class="text-sm text-gray-400 mt-1">Automatically suggest AI-generated content when vendors create products</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="auto_generate_enabled" value="1" {{ ($settings['auto_generate_enabled']->value ?? 0) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Default Generation Styles</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Default Title Style</label>
                    <select name="default_title_style" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="premium" {{ ($settings['default_title_style']->value ?? 'standard') === 'premium' ? 'selected' : '' }}>Premium (Luxury/Elite)</option>
                        <option value="standard" {{ ($settings['default_title_style']->value ?? 'standard') === 'standard' ? 'selected' : '' }}>Standard (Quality/Best)</option>
                        <option value="sale" {{ ($settings['default_title_style']->value ?? 'standard') === 'sale' ? 'selected' : '' }}>Sale (Discounted/Special Offer)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Controls the tone and style of AI-generated product titles</p>
                    @error('default_title_style') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Default Description Style</label>
                    <select name="default_description_style" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="detailed" {{ ($settings['default_description_style']->value ?? 'detailed') === 'detailed' ? 'selected' : '' }}>Detailed (Comprehensive)</option>
                        <option value="concise" {{ ($settings['default_description_style']->value ?? 'detailed') === 'concise' ? 'selected' : '' }}>Concise (Short & Sweet)</option>
                        <option value="technical" {{ ($settings['default_description_style']->value ?? 'detailed') === 'technical' ? 'selected' : '' }}>Technical (Specs Focused)</option>
                        <option value="marketing" {{ ($settings['default_description_style']->value ?? 'detailed') === 'marketing' ? 'selected' : '' }}>Marketing (Engaging & Persuasive)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Controls the length and focus of AI-generated descriptions</p>
                    @error('default_description_style') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">AI Generation Examples</h2>
            
            <div class="space-y-4">
                <div class="p-4 bg-gray-700 rounded-lg">
                    <h3 class="font-semibold text-white mb-2">Premium Title Example</h3>
                    <p class="text-gray-300 text-sm italic">"Premium Handcrafted African Leather Bag - Authentic Design"</p>
                </div>

                <div class="p-4 bg-gray-700 rounded-lg">
                    <h3 class="font-semibold text-white mb-2">Standard Title Example</h3>
                    <p class="text-gray-300 text-sm italic">"Quality African Leather Bag with Authentic Craftsmanship"</p>
                </div>

                <div class="p-4 bg-gray-700 rounded-lg">
                    <h3 class="font-semibold text-white mb-2">Detailed Description Example</h3>
                    <p class="text-gray-300 text-sm italic">"Discover our exceptional handcrafted leather bag featuring authentic African design. This premium product is designed for style-conscious buyers who value quality. Made with genuine leather, it offers durability and elegant craftsmanship. Perfect for daily use, this bag combines functionality with timeless style to deliver outstanding results."</p>
                </div>

                <div class="p-4 bg-gray-700 rounded-lg">
                    <h3 class="font-semibold text-white mb-2">Concise Description Example</h3>
                    <p class="text-gray-300 text-sm italic">"Premium handcrafted leather bag with authentic African design. Modern style for quality-focused customers. Features durability and elegant craftsmanship. Ideal for daily use."</p>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium">
                Save Settings
            </button>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

