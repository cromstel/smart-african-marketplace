@extends('layouts.shop')

@section('title', __('ui.faq.meta_title'))
@section('seoDescription', __('ui.faq.meta_description'))
@section('seoKeywords', __('ui.faq.meta_keywords'))

@section('content')
<div class="min-h-screen bg-gray-50">
    <x-hero-section
        pageIdentifier="faq"
        heightPreset="standard"
        fallbackTitle="{{ __('ui.faq.title') }}"
        fallbackSubtitle="{{ __('ui.faq.subtitle') }}"
        fallbackImage="/images/hero-banners/hero-banner.avif"
        
    />

    <div class="max-w-[1600px] mx-auto px-4 py-12 lg:py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-600 mb-2">Orders</p>
                <p class="text-sm text-gray-700 leading-relaxed">
                    Track purchases from your dashboard, check delivery updates, and manage returns from one place.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-600 mb-2">Salon Bookings</p>
                <p class="text-sm text-gray-700 leading-relaxed">
                    Book, reschedule, or cancel salon appointments directly from your account booking page.
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-600 mb-2">Vendor Support</p>
                <p class="text-sm text-gray-700 leading-relaxed">
                    New vendors can review onboarding requirements and launch their storefront with guided setup.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <div class="xl:col-span-8 space-y-4">
                @for($i = 1; $i <= 7; $i++)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <details class="group">
                            <summary class="flex justify-between items-center cursor-pointer px-6 py-5 hover:bg-gray-50">
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('ui.faq.q' . $i) }}</h3>
                                <span class="text-orange-600 group-open:rotate-180 transition-transform">&#9660;</span>
                            </summary>
                            <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                                @if($i === 7)
                                    <p class="mb-3">{{ __('ui.faq.a7') }}</p>
                                    <a href="{{ route('vendor.guide') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 font-semibold">
                                        {{ __('ui.faq.vendor_guide_link') }}
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                @else
                                    <p>{{ __('ui.faq.a' . $i) }}</p>
                                @endif
                            </div>
                        </details>
                    </div>
                @endfor
            </div>

            <aside class="xl:col-span-4">
                <div class="xl:sticky xl:top-24 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-2xl font-bold mb-3 text-gray-900">{{ __('ui.faq.more_title') }}</h2>
                    <p class="text-gray-700 mb-6">{{ __('ui.faq.more_body') }}</p>
                    <div class="mb-6 rounded-lg border border-orange-100 bg-orange-50 p-4">
                        <p class="text-sm text-orange-900 font-semibold mb-1">Support hours</p>
                        <p class="text-sm text-orange-800">Monday to Saturday, 8:00 AM to 8:00 PM (local time).</p>
                    </div>
                    <a href="{{ route('contact.index') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-orange-600 to-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 transition">
                        {{ __('ui.faq.contact_button') }}
                    </a>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

