@extends('layouts.shop')

@section('content')
<x-hero-section 
    pageIdentifier="about"
    fallbackTitle="{{ __('ui.about.hero_title') }}"
    fallbackSubtitle="{{ __('ui.about.hero_subtitle') }}"
    fallbackImage="/images/hero-banners/about-banner.avif"
    headingTag="h2"
/>

<div class="container mx-auto px-4 py-16">
        @if($contents->count() > 0)
            @foreach($contents as $index => $content)
                @if($content->items && is_array($content->items) && isset($content->items[0]['title']))
                    <div class="mb-16 animate-slide-up" style="animation-delay: {{ $index * 0.1 }}s">
                        <div class="text-center mb-12">
                            <h2 class="text-4xl font-bold mb-4 text-gray-900">{{ $content->title }}</h2>
                            @if($content->content)
                                <p class="text-xl text-gray-600 max-w-3xl mx-auto">{{ $content->content }}</p>
                            @endif
                        </div>
                        
                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                            @foreach($content->items as $item)
                                <div class="bg-white rounded-xl shadow-lg p-8 text-center transform hover:scale-105 hover:shadow-2xl transition duration-300">
                                    <div class="text-5xl mb-4">{{ $item['icon'] ?? '✨' }}</div>
                                    <h3 class="text-xl font-bold mb-3 text-gray-900">{{ $item['title'] ?? '' }}</h3>
                                    <p class="text-gray-600 leading-relaxed">{{ $item['description'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mb-16 animate-slide-up" style="animation-delay: {{ $index * 0.1 }}s">
                        @if($content->image)
                            <div class="grid md:grid-cols-2 gap-12 items-center mb-8">
                                <div class="{{ $index % 2 == 0 ? 'order-1' : 'order-2' }}">
                                    <div class="rounded-2xl overflow-hidden shadow-2xl">
                                        <img src="{{ '/storage/' . ltrim($content->image ?? '', '/') }}" alt="{{ $content->title }}" class="w-full h-96 object-cover transform hover:scale-105 transition duration-500">
                                    </div>
                                </div>
                                <div class="{{ $index % 2 == 0 ? 'order-2' : 'order-1' }}">
                                    <h2 class="text-4xl font-bold mb-6 text-gray-900">{{ $content->title }}</h2>
                                    @if($content->content)
                                        <div class="prose prose-lg max-w-none text-gray-700 mb-6 leading-relaxed">
                                            {!! nl2br(e($content->content)) !!}
                                        </div>
                                    @endif
                                    
                                    @if($content->items && is_array($content->items))
                                        <ul class="space-y-4">
                                            @foreach($content->items as $item)
                                                <li class="flex items-start transform hover:translate-x-2 transition duration-300">
                                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="text-gray-700 text-lg">{{ is_array($item) ? ($item['text'] ?? '') : $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-white rounded-2xl shadow-xl p-12 max-w-5xl mx-auto">
                                <h2 class="text-4xl font-bold mb-6 text-gray-900 text-center">{{ $content->title }}</h2>
                                @if($content->content)
                                    <div class="prose prose-lg max-w-none text-gray-700 mb-8 leading-relaxed">
                                        {!! nl2br(e($content->content)) !!}
                                    </div>
                                @endif
                                
                                @if($content->items && is_array($content->items))
                                    <div class="grid md:grid-cols-2 gap-6 mt-8">
                                        @foreach($content->items as $item)
                                            <div class="flex items-start transform hover:translate-x-2 transition duration-300">
                                                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="text-gray-700 text-lg">{{ is_array($item) ? ($item['text'] ?? '') : $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="mb-16 animate-slide-up">
                <div class="bg-white rounded-2xl shadow-xl p-12">
                    <h2 class="text-4xl md:text-5xl font-bold mb-8 text-gray-900 text-center">Our Story</h2>
                    <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                        <p class="mb-6 text-lg">
                            Smart African Marketplace was founded with a simple yet powerful mission: to bring authentic African products and services to communities worldwide. We understand the importance of staying connected to your roots, enjoying the flavors of home, and celebrating the rich tapestry of African culture that spans across continents and generations.
                        </p>
                        <p class="mb-6 text-lg">
                            Our journey began when we recognized a gap in the market for genuine African products and services. Many people in the diaspora struggled to find authentic ingredients for their favorite dishes, traditional clothing that reflected their heritage, or professional salon services that understood their unique hair and beauty needs. We knew we had to do something about it.
                        </p>
                        <p class="mb-6 text-lg">
                            Today, our platform connects you with a carefully curated network of trusted vendors offering high-quality African food products from West to East Africa, traditional and contemporary clothing that celebrates African fashion, and professional salon services that specialize in African hair care and beauty. Every vendor on our platform is thoroughly vetted to ensure they meet our high standards of authenticity, quality, and customer service.
                        </p>
                        <p class="mb-6 text-lg">
                            What makes us different is our commitment to community. We're not just a marketplace – we're a bridge connecting cultures, supporting African entrepreneurs, and preserving traditions while embracing modern innovation. Whether you're looking for ingredients for a traditional dish, booking a hair braiding appointment, or shopping for authentic African fashion, we're here to make your experience seamless, authentic, and enjoyable.
                        </p>
                        <p class="text-lg">
                            As we continue to grow, our vision remains clear: to become the world's most trusted platform for African products and services, empowering vendors, delighting customers, and celebrating the vibrant diversity of African culture every single day.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-16 animate-slide-up" style="animation-delay: 0.1s">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold mb-4 text-gray-900">Our Mission</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">Building bridges between cultures through authentic commerce</p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-white rounded-xl shadow-lg p-8 text-center transform hover:scale-105 hover:shadow-2xl transition duration-300">
                        <div class="text-5xl mb-4">🎯</div>
                        <h3 class="text-xl font-bold mb-3 text-gray-900">Quality First</h3>
                        <p class="text-gray-600 leading-relaxed">We ensure every product meets our high standards of authenticity and quality.</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-8 text-center transform hover:scale-105 hover:shadow-2xl transition duration-300">
                        <div class="text-5xl mb-4">🤝</div>
                        <h3 class="text-xl font-bold mb-3 text-gray-900">Community</h3>
                        <p class="text-gray-600 leading-relaxed">Supporting African vendors and bringing communities together through commerce.</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-8 text-center transform hover:scale-105 hover:shadow-2xl transition duration-300">
                        <div class="text-5xl mb-4">🚀</div>
                        <h3 class="text-xl font-bold mb-3 text-gray-900">Innovation</h3>
                        <p class="text-gray-600 leading-relaxed">Leveraging technology to make African products accessible worldwide.</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-8 text-center transform hover:scale-105 hover:shadow-2xl transition duration-300">
                        <div class="text-5xl mb-4">💚</div>
                        <h3 class="text-xl font-bold mb-3 text-gray-900">Sustainability</h3>
                        <p class="text-gray-600 leading-relaxed">Promoting sustainable practices and ethical sourcing across our platform.</p>
                    </div>
                </div>
            </div>

            <div class="mb-16 animate-slide-up" style="animation-delay: 0.2s">
                <div class="bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl shadow-2xl p-12 text-white">
                    <h2 class="text-4xl font-bold mb-8 text-center">Why Choose Us?</h2>
                    <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                        <div class="flex items-start transform hover:translate-x-2 transition duration-300">
                            <svg class="w-8 h-8 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-lg">Wide selection of authentic African products from trusted vendors</span>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition duration-300">
                            <svg class="w-8 h-8 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-lg">Convenient online shopping with secure payment options</span>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition duration-300">
                            <svg class="w-8 h-8 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-lg">Fast and reliable delivery to your doorstep</span>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition duration-300">
                            <svg class="w-8 h-8 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-lg">Professional salon booking system for beauty services</span>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition duration-300">
                            <svg class="w-8 h-8 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-lg">Dedicated customer support team</span>
                        </div>
                        <div class="flex items-start transform hover:translate-x-2 transition duration-300">
                            <svg class="w-8 h-8 mr-4 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-lg">100% satisfaction guarantee</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="text-center mt-16 animate-fade-in" style="animation-delay: 0.3s">
            <a href="{{ route('contact.index') }}" class="inline-block bg-gradient-to-r from-orange-600 to-red-600 text-white px-12 py-4 rounded-xl text-lg font-semibold hover:from-orange-700 hover:to-red-700 transform hover:scale-105 transition duration-300 shadow-2xl">
                {{ __('ui.about.cta') }}
            </a>
        </div>
    </div>

<style>
@keyframes fade-in-down {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.animate-fade-in-down {
    animation: fade-in-down 0.6s ease-out;
}

.animate-slide-up {
    animation: slide-up 0.6s ease-out;
    animation-fill-mode: both;
}

.animate-fade-in {
    animation: fade-in 0.8s ease-out;
    animation-fill-mode: both;
}
</style>
@endsection

