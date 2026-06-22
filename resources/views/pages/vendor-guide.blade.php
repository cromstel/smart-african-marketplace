@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">{{ __('ui.vendor.hero_title') }}</h2>
            <p class="text-xl text-orange-50">{{ __('ui.vendor.hero_subtitle') }}</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-12">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('ui.vendor.why_title') }}</h2>
                    <p class="text-lg text-gray-700 leading-relaxed">
                        {{ __('ui.vendor.why_body') }}
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 p-6 rounded-xl">
                        <div class="text-4xl mb-3">&#127757;</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('ui.vendor.benefits.global_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.benefits.global_body') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 p-6 rounded-xl">
                        <div class="text-4xl mb-3">&#128188;</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('ui.vendor.benefits.management_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.benefits.management_body') }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 p-6 rounded-xl">
                        <div class="text-4xl mb-3">&#128176;</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('ui.vendor.benefits.commission_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.benefits.commission_body') }}</p>
                    </div>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-8">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">&#8505;</div>
                        <div>
                            <h3 class="text-lg font-bold text-blue-900 mb-2">{{ __('ui.vendor.subscription_title') }}</h3>
                            <p class="text-blue-800">
                                {!! __('ui.vendor.subscription_body') !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">{{ __('ui.vendor.registration_title') }}</h2>
                
                <div class="space-y-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold mr-6">
                            1
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('ui.vendor.step1_title') }}</h3>
                            <p class="text-gray-700 mb-4">{!! __('ui.vendor.step1_intro', ['url' => route('register', ['type' => 'vendor'])]) !!}</p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                @foreach(trans('ui.vendor.step1_items') as $item)
                                    <li>{!! $item !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold mr-6">
                            2
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('ui.vendor.step2_title') }}</h3>
                            <p class="text-gray-700 mb-4">
                                {!! __('ui.vendor.step2_body') !!}
                            </p>
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                                <p class="text-yellow-800">
                                    {!! __('ui.vendor.step2_note') !!}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold mr-6">
                            3
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('ui.vendor.step3_title') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('ui.vendor.step3_intro') }}</p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                @foreach(trans('ui.vendor.step3_items') as $item)
                                    <li>{!! $item !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold mr-6">
                            4
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('ui.vendor.step4_title') }}</h3>
                            <p class="text-gray-700 mb-4">{{ __('ui.vendor.step4_intro') }}</p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                @foreach(trans('ui.vendor.step4_items') as $item)
                                    <li>{!! $item !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold mr-6">
                            5
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('ui.vendor.step5_title') }}</h3>
                            <p class="text-gray-700 mb-4">
                                {{ __('ui.vendor.step5_body') }}
                            </p>
                            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                                <p class="text-green-800">
                                    {!! __('ui.vendor.step5_note') !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">{{ __('ui.vendor.dashboard_title') }}</h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128230; {{ __('ui.vendor.dashboard.order_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.order_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128202; {{ __('ui.vendor.dashboard.analytics_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.analytics_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128179; {{ __('ui.vendor.dashboard.withdrawals_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.withdrawals_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128666; {{ __('ui.vendor.dashboard.shipping_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.shipping_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#127903; {{ __('ui.vendor.dashboard.coupons_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.coupons_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128172; {{ __('ui.vendor.dashboard.enquiries_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.enquiries_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128260; {{ __('ui.vendor.dashboard.rma_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.rma_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#11088; {{ __('ui.vendor.dashboard.reviews_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.reviews_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128101; {{ __('ui.vendor.dashboard.followers_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.followers_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#127912; {{ __('ui.vendor.dashboard.customization_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.customization_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#129302; {{ __('ui.vendor.dashboard.ai_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.ai_body') }}</p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-6 py-2">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">&#128205; {{ __('ui.vendor.dashboard.location_title') }}</h3>
                        <p class="text-gray-700">{{ __('ui.vendor.dashboard.location_body') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">{{ __('ui.vendor.requirements_title') }}</h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">&#9989; {{ __('ui.vendor.requirements.accept_title') }}</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            @foreach(trans('ui.vendor.requirements.accept_list') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">&#10060; {{ __('ui.vendor.requirements.reject_title') }}</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            @foreach(trans('ui.vendor.requirements.reject_list') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">&#128203; {{ __('ui.vendor.requirements.quality_title') }}</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                            @foreach(trans('ui.vendor.requirements.quality_list') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">{{ __('ui.vendor.commission_title') }}</h2>
                
                <div class="space-y-6">
                    <div class="bg-blue-50 p-6 rounded-xl">
                        <h3 class="text-xl font-bold text-blue-900 mb-3">{{ __('ui.vendor.commission.how_title') }}</h3>
                        <p class="text-blue-800 mb-4">
                            {{ __('ui.vendor.commission.how_body') }}
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-blue-800 ml-4">
                            @foreach(trans('ui.vendor.commission.how_list') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="bg-green-50 p-6 rounded-xl">
                        <h3 class="text-xl font-bold text-green-900 mb-3">{{ __('ui.vendor.commission.withdraw_title') }}</h3>
                        <p class="text-green-800 mb-4">
                            {{ __('ui.vendor.commission.withdraw_body') }}
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-green-800 ml-4">
                            @foreach(trans('ui.vendor.commission.withdraw_list') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-2xl shadow-2xl p-8 md:p-12 text-center">
                <h2 class="text-3xl font-bold mb-4">{{ __('ui.vendor.cta_title') }}</h2>
                <p class="text-xl text-orange-50 mb-8">{{ __('ui.vendor.cta_body') }}</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('register', ['type' => 'vendor']) }}" class="inline-block bg-white text-orange-600 px-10 py-4 rounded-lg font-bold text-lg hover:bg-orange-50 transition shadow-lg">
                        {{ __('ui.vendor.cta_primary') }}
                    </a>
                    <a href="{{ route('contact.index') }}" class="inline-block bg-transparent border-2 border-white text-white px-10 py-4 rounded-lg font-bold text-lg hover:bg-white hover:text-orange-600 transition">
                        {{ __('ui.vendor.cta_secondary') }}
                    </a>
                </div>
            </div>

            <div class="mt-12 bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg">
                <div class="flex items-start">
                    <div class="text-3xl mr-4">&#128161;</div>
                    <div>
                        <h3 class="text-lg font-bold text-yellow-900 mb-2">{{ __('ui.vendor.help_title') }}</h3>
                        <p class="text-yellow-800 mb-4">
                            {!! __('ui.vendor.help_body', ['faq' => route('faq'), 'contact' => route('contact.index')]) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

