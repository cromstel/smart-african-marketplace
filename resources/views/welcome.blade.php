<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="African Market - Authentic African Products & Services">
    <title>African Market - Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <nav class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-bold text-primary-600">🌍 African Market</h1>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="#" class="text-gray-700 hover:text-primary-600">Shop</a>
                    <a href="#" class="text-gray-700 hover:text-primary-600">Salon</a>
                    <a href="#" class="text-gray-700 hover:text-primary-600">About</a>
                    <a href="#" class="text-gray-700 hover:text-primary-600">Contact</a>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="btn-secondary">Login</button>
                    <button class="btn-primary">Get Started</button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-5xl font-bold mb-6">Welcome to African Market</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Your one-stop platform for authentic African food products, 
                traditional clothing, and professional salon services.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="/shop/products" class="bg-white text-primary-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Browse Products
                </a>
                <a href="/salon" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary-600 transition">
                    Book Salon Service
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h3 class="text-3xl font-bold text-center mb-12">Why Choose African Market?</h3>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card text-center">
                    <div class="text-5xl mb-4">🍲</div>
                    <h4 class="text-xl font-bold mb-2">Authentic Food</h4>
                    <p class="text-gray-600">
                        Fresh African ingredients, spices, and ready-to-eat meals from across the continent.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="card text-center">
                    <div class="text-5xl mb-4">👗</div>
                    <h4 class="text-xl font-bold mb-2">Traditional Clothing</h4>
                    <p class="text-gray-600">
                        Beautiful Ankara, Kente, Dashiki, and modern African fashion for all occasions.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="card text-center">
                    <div class="text-5xl mb-4">💇</div>
                    <h4 class="text-xl font-bold mb-2">Salon Services</h4>
                    <p class="text-gray-600">
                        Book appointments with expert stylists for braiding, styling, and treatments.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="bg-white py-16">
        <div class="container mx-auto px-4">
            <h3 class="text-3xl font-bold text-center mb-12">Shop by Category</h3>
            <div class="grid md:grid-cols-4 gap-6">
                <div class="bg-gray-100 rounded-lg p-6 text-center hover:shadow-lg transition cursor-pointer">
                    <div class="text-4xl mb-3">🌾</div>
                    <h5 class="font-semibold">Grains & Cereals</h5>
                </div>
                <div class="bg-gray-100 rounded-lg p-6 text-center hover:shadow-lg transition cursor-pointer">
                    <div class="text-4xl mb-3">🌶️</div>
                    <h5 class="font-semibold">Spices & Seasonings</h5>
                </div>
                <div class="bg-gray-100 rounded-lg p-6 text-center hover:shadow-lg transition cursor-pointer">
                    <div class="text-4xl mb-3">🥘</div>
                    <h5 class="font-semibold">Ready Meals</h5>
                </div>
                <div class="bg-gray-100 rounded-lg p-6 text-center hover:shadow-lg transition cursor-pointer">
                    <div class="text-4xl mb-3">👔</div>
                    <h5 class="font-semibold">Fashion & Apparel</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-primary-50 py-16">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-3xl font-bold mb-4">Ready to Experience African Culture?</h3>
            <p class="text-xl text-gray-700 mb-8 max-w-2xl mx-auto">
                Join thousands of customers enjoying authentic African products and services delivered to your door.
            </p>
            <button class="btn-primary text-lg px-10 py-4">
                Create Free Account
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-xl font-bold mb-4">African Market</h4>
                    <p class="text-gray-400">
                        Connecting cultures through authentic African products and services.
                    </p>
                </div>
                <div>
                    <h5 class="font-semibold mb-4">Shop</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Food Products</a></li>
                        <li><a href="#" class="hover:text-white">Clothing</a></li>
                        <li><a href="#" class="hover:text-white">Accessories</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-semibold mb-4">Services</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Salon Booking</a></li>
                        <li><a href="#" class="hover:text-white">Delivery</a></li>
                        <li><a href="#" class="hover:text-white">Vendor Portal</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-semibold mb-4">Support</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 African Market. Made with ❤️ for the African diaspora community.</p>
            </div>
        </div>
    </footer>

    <script>
        console.log('🌍 African Market Platform Loaded');
        console.log('Built with Laravel + Livewire + Alpine.js + Tailwind CSS');
    </script>
</body>
</html>
