@php($hideBreadcrumbs = true)
@php($suppressHeaderWidgets = true)
@php($hideFooter = true)
@extends('layouts.shop')

@section('content')
<x-auth-overlay-shell panelClass="max-w-md lg:max-w-lg">
                <div>
                    <x-auth.brand-header name="Smart African Market" />
                    <h2 class="mt-6 text-4xl font-bold text-gray-900">Verify Your Email</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        We sent a verification link to your email. Please verify to continue.
                    </p>
                </div>

                <div class="space-y-4 animate-fade-in-up">
                    <div class="text-sm text-gray-600">
                        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you did not receive the email, we will gladly send you another.') }}
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 transition-all duration-200 shadow-sm">
                            {{ __('Resend Email') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900 underline underline-offset-4">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>

            </x-auth-overlay-shell>
@endsection





