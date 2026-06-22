@extends('layouts.shop')

@section('content')
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes parallaxFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }
    
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
        opacity: 0;
    }
    
    .parallax-bg {
        transition: transform 0.3s ease-out;
    }
    
    .animate-delay-200 { animation-delay: 0.2s; }
    .animate-delay-400 { animation-delay: 0.4s; }
    .animate-delay-600 { animation-delay: 0.6s; }
    
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .shimmer-effect {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        background-size: 1000px 100%;
        animation: shimmer 3s infinite;
    }
</style>

<div class="min-h-screen bg-gray-50">
    <!-- Hero Section with Background Image -->
    <div class="relative min-h-[50vh] flex items-center overflow-hidden" 
         x-data="{ mouseX: 0, mouseY: 0 }"
         @mousemove="mouseX = ($event.clientX / window.innerWidth - 0.5) * 30; mouseY = ($event.clientY / window.innerHeight - 0.5) * 30">
        
        <!-- Background Image with Parallax -->
        <div class="absolute inset-0 parallax-bg"
             :style="`transform: translate(${mouseX}px, ${mouseY}px) scale(1.1)`">
            <img src="/images/hero-banners/contact-banner.avif" 
                 alt="Contact Us Background" 
                 class="w-full h-full object-cover">
        </div>
        
        <!-- 65% black overlay to match global hero transparency -->
        <div class="absolute inset-0" style="background-color: rgba(0, 0, 0, 0.65);"></div>
        
        <!-- Animated Overlay Shapes -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-10 right-10 w-32 h-32 bg-white rounded-full opacity-10 blur-2xl" style="animation: parallaxFloat 4s ease-in-out infinite;"></div>
            <div class="absolute bottom-10 left-10 w-40 h-40 bg-white rounded-full opacity-10 blur-3xl" style="animation: parallaxFloat 5s ease-in-out infinite; animation-delay: 1s;"></div>
        </div>
        
        <!-- Content -->
        <div class="max-w-[1600px] mx-auto px-4 relative z-10 w-full">
            <div class="text-left text-white max-w-3xl">
                <h2 class="text-4xl md:text-6xl font-bold mb-4 animate-fade-in-up drop-shadow-lg">
                    {{ __('ui.contact.hero_title') }}
                </h2>
                <p class="text-xl md:text-2xl text-white/95 animate-fade-in-up animate-delay-200 drop-shadow-md">
                    {{ __('ui.contact.hero_subtitle') }}
                </p>
                
                <!-- Decorative Icons -->
                <div class="flex justify-start gap-8 mt-8 animate-fade-in-up animate-delay-400">
                    <div class="text-4xl hover:scale-110 transition-transform cursor-default">📧</div>
                    <div class="text-4xl hover:scale-110 transition-transform cursor-default">📞</div>
                    <div class="text-4xl hover:scale-110 transition-transform cursor-default">💬</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-[1600px] mx-auto px-4 py-16 w-full">
        <div>
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid md:grid-cols-2 gap-8">
                <div class="animate-fade-in-up">
                    <div class="bg-white rounded-lg shadow-md p-8 hover-lift">
                        <h2 class="text-2xl font-bold mb-6 text-gray-900 flex items-center gap-2">
                            <span class="text-3xl">✉️</span>
                            {{ __('ui.contact.send_message') }}
                        </h2>
                        <form action="{{ route('contact.store') }}" method="POST">
                            @csrf
                            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="first_name" class="sr-only">{{ __('ui.contact.first_name') }}</label>
                                    <div class="relative">
                                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="{{ __('ui.contact.first_name') }}"
                                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 hover:border-orange-400">
                                        <x-form-hint text="{{ __('ui.contact.first_name_hint') }}" />
                                    </div>
                                    @error('first_name')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="last_name" class="sr-only">{{ __('ui.contact.last_name') }}</label>
                                    <div class="relative">
                                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="{{ __('ui.contact.last_name') }}"
                                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 hover:border-orange-400">
                                        <x-form-hint text="{{ __('ui.contact.last_name_hint') }}" />
                                    </div>
                                    @error('last_name')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="sr-only">{{ __('ui.contact.email') }}</label>
                                <div class="relative">
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com"
                                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 hover:border-orange-400">
                                    <x-form-hint text="{{ __('ui.contact.email_hint') }}" />
                                </div>
                                @error('email')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="subject" class="sr-only">{{ __('ui.contact.subject') }}</label>
                                <div class="relative">
                                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required placeholder="{{ __('ui.contact.subject_placeholder') }}"
                                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 hover:border-orange-400">
                                    <x-form-hint text="{{ __('ui.contact.subject_hint') }}" />
                                </div>
                                @error('subject')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="message" class="sr-only">{{ __('ui.contact.message') }}</label>
                                <div class="relative">
                                    <textarea id="message" name="message" rows="5" required placeholder="{{ __('ui.contact.message_placeholder') }}"
                                        class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 hover:border-orange-400">{{ old('message') }}</textarea>
                                    <x-form-hint class="top-3 !inset-y-auto items-start" text="{{ __('ui.contact.message_hint') }}" />
                                </div>
                                @error('message')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-600 text-white py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition-all duration-200 hover:scale-105 hover:shadow-lg relative overflow-hidden group">
                                <span class="relative z-10">{{ __('ui.contact.send_button') }}</span>
                                <div class="absolute inset-0 shimmer-effect"></div>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-6 animate-fade-in-up animate-delay-200">
                    <div class="bg-white rounded-lg shadow-md p-8 hover-lift">
                        <h2 class="text-2xl font-bold mb-6 text-gray-900 flex items-center gap-2">
                            <span class="text-3xl">📋</span>
                            {{ __('ui.contact.contact_info') }}
                        </h2>
                        <div class="space-y-4">
                            <div class="flex items-start p-3 rounded-lg hover:bg-orange-50 transition-colors duration-200 group">
                                <span class="text-orange-600 text-2xl mr-4 group-hover:scale-110 transition-transform">📧</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ __('ui.contact.email') }}</h3>
                                    <a href="mailto:support@africanmarket.com" class="text-gray-600 hover:text-orange-600 transition-colors">support@africanmarket.com</a>
                                </div>
                            </div>
                            <div class="flex items-start p-3 rounded-lg hover:bg-orange-50 transition-colors duration-200 group">
                                <span class="text-orange-600 text-2xl mr-4 group-hover:scale-110 transition-transform">📞</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ __('ui.contact.phone') }}</h3>
                                    <a href="tel:+15551234567" class="text-gray-600 hover:text-orange-600 transition-colors">+1 (555) 123-4567</a>
                                </div>
                            </div>
                            <div class="flex items-start p-3 rounded-lg hover:bg-orange-50 transition-colors duration-200 group">
                                <span class="text-orange-600 text-2xl mr-4 group-hover:scale-110 transition-transform">📍</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ __('ui.contact.address') }}</h3>
                                    <p class="text-gray-600">123 Market Street<br>City, State 12345</p>
                                </div>
                            </div>
                            <div class="flex items-start p-3 rounded-lg hover:bg-orange-50 transition-colors duration-200 group">
                                <span class="text-orange-600 text-2xl mr-4 group-hover:scale-110 transition-transform">⏰</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ __('ui.contact.business_hours') }}</h3>
                                    <p class="text-gray-600">Mon - Fri: 9:00 AM - 6:00 PM<br>Sat - Sun: 10:00 AM - 4:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-8 hover-lift">
                        <h2 class="text-2xl font-bold mb-4 text-gray-900 flex items-center gap-2">
                            <span class="text-3xl">🌐</span>
                            {{ __('ui.contact.follow_us') }}
                        </h2>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-600 hover:text-orange-600 transition-all duration-200 hover:scale-110 font-medium">Facebook</a>
                            <a href="#" class="text-gray-600 hover:text-orange-600 transition-all duration-200 hover:scale-110 font-medium">Instagram</a>
                            <a href="#" class="text-gray-600 hover:text-orange-600 transition-all duration-200 hover:scale-110 font-medium">Twitter</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



