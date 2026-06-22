@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50">
    <x-hero-section
        pageIdentifier="terms"
        heightPreset="standard"
        fallbackTitle="{{ __('ui.terms.title') }}"
        fallbackSubtitle="{{ __('ui.terms.last_updated', ['date' => date('F Y')]) }}"
        fallbackImage="/images/hero-banners/hero-banner.avif"
        :animated="true"
    />

    <div class="max-w-[1600px] mx-auto px-4 py-12 lg:py-16" id="terms-top">
        <div class="xl:hidden mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <label for="terms-toc-mobile" class="block text-sm font-semibold text-gray-900 mb-2">Table of Contents</label>
            <select
                id="terms-toc-mobile"
                class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700"
                onchange="if(this.value){ window.location.hash = this.value; }"
            >
                <option value="">Jump to a section</option>
                @for($i = 1; $i <= 10; $i++)
                    <option value="terms-section-{{ $i }}">{{ $i }}. {{ __('ui.terms.section_' . $i . '_title') }}</option>
                @endfor
            </select>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-8">
                <div class="space-y-8">
                    @for($i = 1; $i <= 10; $i++)
                        <section id="terms-section-{{ $i }}" class="scroll-mt-24">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <h2 class="text-2xl font-bold text-gray-900">
                                    <span class="text-orange-600">{{ $i }}.</span>
                                    {{ __('ui.terms.section_' . $i . '_title') }}
                                </h2>
                                <a href="#terms-top" class="shrink-0 text-xs font-semibold text-orange-600 hover:text-orange-700 uppercase tracking-wide">Back to contents</a>
                            </div>
                            @if($i === 10)
                                <p class="text-gray-700 leading-relaxed">{!! __('ui.terms.section_10_body') !!}</p>
                            @else
                                <p class="text-gray-700 leading-relaxed">{{ __('ui.terms.section_' . $i . '_body') }}</p>
                            @endif
                        </section>
                    @endfor
                </div>
        </div>
    </div>
</div>
@endsection

