@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50">
    <x-hero-section
        pageIdentifier="privacy"
        heightPreset="standard"
        fallbackTitle="{{ __('ui.privacy.title') }}"
        fallbackSubtitle="{{ __('ui.privacy.last_updated', ['date' => date('F Y')]) }}"
        fallbackImage="/images/hero-banners/privacy-banner.avif"
        :animated="true"
    />

    <div class="max-w-[1600px] mx-auto px-4 py-12 lg:py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-8 space-y-7">
                <h2 class="text-2xl font-bold text-gray-900">Data Collection and Usage</h2>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_1_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ __('ui.privacy.section_1_body') }}</p>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_2_title') }}</h3>
                    <p class="mb-2 text-gray-700 leading-relaxed">{{ __('ui.privacy.section_2_intro') }}</p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2">
                        @foreach(trans('ui.privacy.section_2_list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_3_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ __('ui.privacy.section_3_body') }}</p>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_4_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ __('ui.privacy.section_4_body') }}</p>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_5_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ __('ui.privacy.section_5_body') }}</p>
                </div>
            </section>

            <section class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:p-8 space-y-7">
                <h2 class="text-2xl font-bold text-gray-900">Security, Rights and Contact</h2>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_6_title') }}</h3>
                    <p class="mb-2 text-gray-700 leading-relaxed">{{ __('ui.privacy.section_6_intro') }}</p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2">
                        @foreach(trans('ui.privacy.section_6_list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_7_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ __('ui.privacy.section_7_body') }}</p>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_8_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ __('ui.privacy.section_8_body') }}</p>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_9_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{{ __('ui.privacy.section_9_body') }}</p>
                </div>

                <div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">{{ __('ui.privacy.section_10_title') }}</h3>
                    <p class="text-gray-700 leading-relaxed">{!! __('ui.privacy.section_10_body') !!}</p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

