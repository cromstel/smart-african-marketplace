<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo
        :title="'404 - Page Not Found | ' . config('app.name', 'Smart African Market')"
        :index="false"
        :follow="false"
    />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(5deg); }
            66% { transform: translateY(30px) rotate(-5deg); }
        }

        @keyframes float-delayed {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(40px) rotate(-7deg); }
            66% { transform: translateY(-40px) rotate(7deg); }
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes bounce-in {
            0% { transform: scale(0) rotate(-180deg); opacity: 0; }
            60% { transform: scale(1.2) rotate(10deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); }
        }

        .animated-bg {
            background: linear-gradient(-45deg, #fff5f3, #ffe4e1, #fff0e6, #fef3f2);
            background-size: 400% 400%;
            animation: gradient-shift 15s ease infinite;
        }

        .float-shape {
            animation: float 8s ease-in-out infinite;
        }

        .float-shape-delayed {
            animation: float-delayed 10s ease-in-out infinite;
        }

        .pulse-shape {
            animation: pulse-glow 3s ease-in-out infinite;
        }

        .bounce-in-element {
            animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .parallax-layer {
            transition: transform 0.3s ease-out;
        }
    </style>
</head>
<body class="animated-bg antialiased overflow-hidden">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-64 h-64 bg-orange-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 float-shape"></div>
        <div class="absolute top-40 right-20 w-72 h-72 bg-red-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 float-shape-delayed" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-20 left-40 w-80 h-80 bg-yellow-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 float-shape" style="animation-delay: 4s;"></div>
        <div class="absolute top-1/2 left-1/4 w-48 h-48 bg-pink-200 rounded-full mix-blend-multiply filter blur-2xl opacity-20 pulse-shape"></div>
        <div class="absolute bottom-20 right-1/4 w-56 h-56 bg-orange-300 rounded-full mix-blend-multiply filter blur-2xl opacity-20 pulse-shape" style="animation-delay: 1.5s;"></div>
    </div>

    <div 
        class="min-h-screen flex items-center justify-center px-4 relative z-10"
        x-data="{ 
            mouseX: 0, 
            mouseY: 0,
            init() {
                window.addEventListener('mousemove', (e) => {
                    this.mouseX = (e.clientX / window.innerWidth - 0.5) * 20;
                    this.mouseY = (e.clientY / window.innerHeight - 0.5) * 20;
                });
            }
        }"
    >
        <div class="text-center">
            <div 
                class="mb-8 parallax-layer bounce-in-element"
                :style="`transform: translate(${mouseX}px, ${mouseY}px)`"
            >
                <h2 class="text-9xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-600 via-red-500 to-red-600 drop-shadow-lg select-none">
                    404
                </h2>
            </div>
            
            <div 
                class="parallax-layer bounce-in-element"
                style="animation-delay: 0.2s;"
                :style="`transform: translate(${mouseX * 0.5}px, ${mouseY * 0.5}px)`"
            >
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Oops! Page Not Found
                </h2>
                <p class="text-xl text-gray-600 mb-8 max-w-md mx-auto">
                    Looks like this page took a detour. Let's get you back on track! 🛒
                </p>
            </div>
            
            <div 
                class="flex flex-col sm:flex-row gap-4 justify-center bounce-in-element"
                style="animation-delay: 0.4s;"
            >
                <a 
                    href="{{ route('shop.index') }}" 
                    class="inline-block bg-gradient-to-r from-orange-600 to-red-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-orange-700 hover:to-red-700 hover:scale-105 transition-all shadow-lg hover:shadow-xl"
                >
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Go Shopping
                    </span>
                </a>
                <a 
                    href="{{ url('/') }}" 
                    class="inline-block border-2 border-orange-600 text-orange-600 px-8 py-3 rounded-lg font-semibold hover:bg-orange-50 hover:scale-105 transition-all shadow-md hover:shadow-lg"
                >
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Go Home
                    </span>
                </a>
            </div>

            <div 
                class="mt-12 bounce-in-element"
                style="animation-delay: 0.6s;"
            >
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Explore Popular Categories</h3>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a 
                        href="{{ route('shop.index') }}" 
                        class="group px-6 py-3 bg-white/80 backdrop-blur-sm rounded-xl shadow-md hover:shadow-xl transition-all hover:scale-105 text-gray-700 hover:text-orange-600 border border-transparent hover:border-orange-200"
                    >
                        <span class="text-2xl group-hover:scale-110 inline-block transition-transform">🍎</span>
                        <span class="ml-2 font-medium">Food & Groceries</span>
                    </a>
                    <a 
                        href="{{ route('shop.index') }}" 
                        class="group px-6 py-3 bg-white/80 backdrop-blur-sm rounded-xl shadow-md hover:shadow-xl transition-all hover:scale-105 text-gray-700 hover:text-orange-600 border border-transparent hover:border-orange-200"
                    >
                        <span class="text-2xl group-hover:scale-110 inline-block transition-transform">👗</span>
                        <span class="ml-2 font-medium">Fashion & Clothing</span>
                    </a>
                    <a 
                        href="{{ route('salon.index') }}" 
                        class="group px-6 py-3 bg-white/80 backdrop-blur-sm rounded-xl shadow-md hover:shadow-xl transition-all hover:scale-105 text-gray-700 hover:text-orange-600 border border-transparent hover:border-orange-200"
                    >
                        <span class="text-2xl group-hover:scale-110 inline-block transition-transform">💇</span>
                        <span class="ml-2 font-medium">Salon Services</span>
                    </a>
                </div>
            </div>

            <div 
                class="mt-8 text-sm text-gray-500 bounce-in-element"
                style="animation-delay: 0.8s;"
            >
                <p>Need help? <a href="{{ route('contact.index') }}" class="text-orange-600 hover:text-orange-700 font-medium">Contact Support</a></p>
            </div>
        </div>
    </div>
</body>
</html>

