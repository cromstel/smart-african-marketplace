<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\MediaFolder;
use App\Models\User;
use App\Services\ImageOptimizationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            $admin = User::first();
        }

        if (! $admin) {
            return;
        }

        Auth::login($admin);
        $seedFolderId = MediaFolder::where('slug', 'seeded')->value('id');
        $imageService = app(ImageOptimizationService::class);

        $categories = [
            [
                'name' => 'E-commerce Tips',
                'slug' => 'ecommerce-tips',
                'description' => 'Best practices and tips for online shopping and selling',
            ],
            [
                'name' => 'African Culture',
                'slug' => 'african-culture',
                'description' => 'Celebrating African heritage, traditions, and cultural insights',
            ],
            [
                'name' => 'Business & Trade',
                'slug' => 'business-trade',
                'description' => 'Trade insights and business opportunities in African markets',
            ],
            [
                'name' => 'Beauty & Wellness',
                'slug' => 'beauty-wellness',
                'description' => 'Beauty tips, salon services, and wellness advice',
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $category) {
            $categoryModels[] = BlogCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $tags = [
            ['name' => 'Shopping', 'slug' => 'shopping', 'color' => '#3b82f6'],
            ['name' => 'Fashion', 'slug' => 'fashion', 'color' => '#ec4899'],
            ['name' => 'Culture', 'slug' => 'culture', 'color' => '#f59e0b'],
            ['name' => 'Business', 'slug' => 'business', 'color' => '#10b981'],
            ['name' => 'Beauty', 'slug' => 'beauty', 'color' => '#8b5cf6'],
            ['name' => 'Trends', 'slug' => 'trends', 'color' => '#ef4444'],
            ['name' => 'Tips', 'slug' => 'tips', 'color' => '#06b6d4'],
            ['name' => 'Marketplace', 'slug' => 'marketplace', 'color' => '#f97316'],
        ];

        $tagModels = [];
        foreach ($tags as $tag) {
            $tagModels[] = BlogTag::firstOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }

        $posts = [
            [
                'title' => 'Top 10 African Fashion Trends for 2026',
                'slug' => 'top-10-african-fashion-trends-2026',
                'excerpt' => 'Discover the hottest fashion trends emerging from Africa this year. From bold prints to contemporary designs, explore what\'s making waves in African fashion.',
                'content' => '<h2>Celebrating African Fashion Excellence</h2>
                <p>African fashion has taken the global stage by storm, and 2026 is set to be an even more exciting year. From traditional prints reimagined in modern silhouettes to sustainable fashion practices rooted in African heritage, the continent continues to influence worldwide fashion trends.</p>
                
                <h3>1. Bold Ankara Prints</h3>
                <p>Ankara prints remain a staple, but designers are pushing boundaries with innovative color combinations and unexpected pattern mixing. These vibrant fabrics are being incorporated into everything from casual wear to high fashion.</p>
                
                <h3>2. Sustainable Materials</h3>
                <p>African designers are leading the way in sustainable fashion, using locally sourced organic materials and traditional weaving techniques that have been passed down through generations.</p>
                
                <h3>3. Contemporary African Luxury</h3>
                <p>Luxury African brands are gaining international recognition, combining traditional craftsmanship with contemporary design aesthetics that appeal to global audiences.</p>
                
                <p>Shop these trends and more on our African Market platform, where you can connect directly with talented African designers and vendors.</p>',
                'categories' => [$categoryModels[1]->id],
                'tags' => [$tagModels[1]->id, $tagModels[5]->id],
                'published_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'How to Start Selling on African Market: A Complete Guide',
                'slug' => 'how-to-start-selling-african-market-guide',
                'excerpt' => 'Ready to become a vendor? Learn everything you need to know about setting up your store and reaching customers across Africa and beyond.',
                'content' => '<h2>Your Journey to Becoming a Successful Vendor</h2>
                <p>African Market is more than just an e-commerce platform—it\'s a community of entrepreneurs, artisans, and businesses connecting African products with the world. Whether you\'re a small artisan or an established business, our platform provides the tools you need to succeed.</p>
                
                <h3>Step 1: Create Your Vendor Account</h3>
                <p>Getting started is simple. Sign up for a vendor account, complete your business profile, and verify your identity. Our streamlined onboarding process ensures you can start selling quickly.</p>
                
                <h3>Step 2: List Your Products</h3>
                <p>Upload high-quality images, write compelling product descriptions, and set competitive prices. Our platform supports multiple product variants, categories, and pricing options.</p>
                
                <h3>Step 3: Manage Orders and Deliveries</h3>
                <p>Use our intuitive dashboard to track orders, communicate with customers, and coordinate deliveries. We support multiple payment methods and provide comprehensive order management tools.</p>
                
                <h3>Marketing Your Store</h3>
                <p>Take advantage of our promotional features, including featured listings, discount coupons, and seasonal campaigns to boost your visibility and sales.</p>
                
                <p>Join thousands of successful vendors already selling on African Market. <a href="/register">Start your journey today!</a></p>',
                'categories' => [$categoryModels[0]->id, $categoryModels[2]->id],
                'tags' => [$tagModels[3]->id, $tagModels[6]->id, $tagModels[7]->id],
                'published_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'The Rise of African Beauty Products in Global Markets',
                'slug' => 'rise-of-african-beauty-products-global-markets',
                'excerpt' => 'African beauty products are conquering international markets. Discover the natural ingredients and traditional beauty secrets that are revolutionizing skincare worldwide.',
                'content' => '<h2>African Beauty Goes Global</h2>
                <p>From shea butter to black soap, African beauty products are gaining unprecedented recognition in international markets. These time-tested ingredients, used for centuries across the continent, are now sought after by beauty enthusiasts worldwide.</p>
                
                <h3>Natural Ingredients, Proven Results</h3>
                <p>African beauty products harness the power of natural ingredients like shea butter from West Africa, argan oil from Morocco, and marula oil from Southern Africa. These ingredients are rich in vitamins, antioxidants, and essential fatty acids.</p>
                
                <h3>Traditional Beauty Wisdom</h3>
                <p>Many African beauty rituals have been passed down through generations. From Ethiopian coffee scrubs to Nigerian turmeric masks, these traditional practices are finding new audiences in modern skincare routines.</p>
                
                <h3>Supporting African Beauty Entrepreneurs</h3>
                <p>By purchasing African beauty products through our marketplace, you\'re supporting local entrepreneurs and artisans who craft these products using sustainable practices and traditional knowledge.</p>
                
                <h3>Popular African Beauty Products</h3>
                <ul>
                    <li>Shea Butter: Deep moisturizing and anti-inflammatory properties</li>
                    <li>African Black Soap: Natural cleanser for all skin types</li>
                    <li>Argan Oil: Hair and skin nourishment</li>
                    <li>Baobab Oil: Rich in vitamins A, D, E, and F</li>
                    <li>Marula Oil: Lightweight moisturizer with anti-aging benefits</li>
                </ul>
                
                <p>Explore our extensive collection of authentic African beauty products and transform your beauty routine naturally.</p>',
                'categories' => [$categoryModels[3]->id, $categoryModels[1]->id],
                'tags' => [$tagModels[4]->id, $tagModels[5]->id],
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => '7 Essential Tips for Safe Online Shopping in Africa',
                'slug' => '7-essential-tips-safe-online-shopping-africa',
                'excerpt' => 'Online shopping is booming across Africa. Protect yourself with these essential security tips and shopping best practices for a safe e-commerce experience.',
                'content' => '<h2>Shop Smart, Shop Safe</h2>
                <p>As e-commerce continues to grow across Africa, it\'s important to stay informed about safe shopping practices. Follow these essential tips to protect yourself while enjoying the convenience of online shopping.</p>
                
                <h3>1. Verify Vendor Authenticity</h3>
                <p>Always check vendor profiles, ratings, and reviews before making a purchase. On African Market, we verify all our vendors to ensure authenticity and quality.</p>
                
                <h3>2. Use Secure Payment Methods</h3>
                <p>Choose platforms that offer secure payment options and buyer protection. African Market supports multiple trusted payment gateways including mobile money, card payments, and bank transfers.</p>
                
                <h3>3. Read Product Descriptions Carefully</h3>
                <p>Review all product details, including dimensions, materials, and specifications. Don\'t hesitate to contact vendors with questions before purchasing.</p>
                
                <h3>4. Check Return Policies</h3>
                <p>Understand the return and refund policies before completing your purchase. Reputable platforms provide clear terms and customer protection.</p>
                
                <h3>5. Keep Transaction Records</h3>
                <p>Save all confirmation emails, receipts, and communication with vendors. These records are valuable if you need to resolve any issues.</p>
                
                <h3>6. Use Strong Passwords</h3>
                <p>Protect your account with a strong, unique password and enable two-factor authentication when available.</p>
                
                <h3>7. Watch for Deal Red Flags</h3>
                <p>If a deal seems too good to be true, it probably is. Be cautious of unrealistic discounts and suspicious vendor behavior.</p>
                
                <p>At African Market, customer safety is our priority. We implement robust security measures and vendor verification processes to ensure a safe shopping environment for all our users.</p>',
                'categories' => [$categoryModels[0]->id],
                'tags' => [$tagModels[0]->id, $tagModels[6]->id],
                'published_at' => Carbon::now()->subDays(7),
            ],
            [
                'title' => 'Exploring African Textiles: From Kente to Mudcloth',
                'slug' => 'exploring-african-textiles-kente-mudcloth',
                'excerpt' => 'Journey through the rich heritage of African textiles. Learn about traditional weaving techniques, symbolic patterns, and the stories woven into every piece.',
                'content' => '<h2>The Art and Heritage of African Textiles</h2>
                <p>African textiles are more than just fabric—they\'re storytellers, cultural ambassadors, and works of art. Each region across the continent has developed unique weaving techniques and patterns that reflect their history, beliefs, and identity.</p>
                
                <h3>Kente Cloth: Ghana\'s Royal Heritage</h3>
                <p>Originating from the Ashanti Kingdom, Kente cloth is one of Africa\'s most recognizable textiles. Hand-woven on horizontal looms, each color and pattern combination has specific meanings. Originally reserved for royalty, Kente is now celebrated worldwide as a symbol of African heritage.</p>
                
                <h3>Mudcloth: Mali\'s Ancient Art</h3>
                <p>Bògòlanfini, commonly known as mudcloth, is a traditional Malian cotton fabric dyed with fermented mud. The process takes weeks and results in distinctive geometric patterns. Each design tells a story or conveys traditional proverbs.</p>
                
                <h3>Ankara: West Africa\'s Colorful Canvas</h3>
                <p>Though influenced by Indonesian batik, Ankara prints have become quintessentially African. These vibrant wax-printed fabrics are used for everything from everyday wear to special occasions, with new patterns constantly emerging to reflect contemporary African life.</p>
                
                <h3>Shweshwe: South Africa\'s Heritage Fabric</h3>
                <p>This distinctive printed cotton fabric, featuring intricate geometric patterns, has been part of South African culture since the 19th century. It\'s particularly significant in Xhosa, Sotho, and Tswana cultures.</p>
                
                <h3>Preserving Traditional Crafts</h3>
                <p>By purchasing authentic African textiles through platforms like African Market, you\'re supporting artisans who keep these ancient traditions alive. Each piece connects you to centuries of cultural heritage and skilled craftsmanship.</p>
                
                <p>Browse our collection of authentic African textiles and bring home a piece of Africa\'s rich cultural tapestry.</p>',
                'categories' => [$categoryModels[1]->id],
                'tags' => [$tagModels[2]->id, $tagModels[1]->id],
                'published_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Maximizing Your Salon Business on African Market',
                'slug' => 'maximizing-salon-business-african-market',
                'excerpt' => 'Salon owners, take your business to the next level! Discover strategies to attract more clients, manage bookings efficiently, and grow your beauty business online.',
                'content' => '<h2>Digital Transformation for Salon Businesses</h2>
                <p>The beauty industry in Africa is thriving, and digital platforms are opening new opportunities for salon owners to reach clients and grow their businesses. African Market\'s salon booking system provides the tools you need to succeed in the digital age.</p>
                
                <h3>Create an Attractive Service Portfolio</h3>
                <p>Showcase your services with high-quality photos and detailed descriptions. Highlight your specialties, whether it\'s traditional African hairstyles, modern cuts, natural hair care, or spa treatments. Clear pricing and duration information help clients make informed booking decisions.</p>
                
                <h3>Build Your Online Reputation</h3>
                <p>Encourage satisfied clients to leave reviews on your profile. Positive testimonials build trust with potential clients and improve your visibility on the platform. Respond professionally to all reviews, showing you value client feedback.</p>
                
                <h3>Optimize Your Booking Schedule</h3>
                <p>Use our booking management system to minimize gaps in your schedule and reduce no-shows. Set up automated reminders for clients and configure your availability to match your business hours.</p>
                
                <h3>Offer Package Deals and Promotions</h3>
                <p>Create special packages combining multiple services at discounted rates. Seasonal promotions and loyalty discounts can help attract new clients and encourage repeat bookings.</p>
                
                <h3>Expand Your Reach</h3>
                <p>African Market connects you with clients beyond your immediate neighborhood. With clear location information and service descriptions, you can attract clients from across your city and beyond.</p>
                
                <h3>Professional Growth</h3>
                <p>Stay updated on beauty trends, invest in training, and continuously expand your service offerings. Clients appreciate salons that stay current with the latest techniques and products.</p>
                
                <h3>Key Success Metrics to Track</h3>
                <ul>
                    <li>Booking rate and cancellation rate</li>
                    <li>Customer retention and repeat bookings</li>
                    <li>Average service value per client</li>
                    <li>Review ratings and client feedback</li>
                    <li>Peak booking times and seasonal trends</li>
                </ul>
                
                <p>Join our community of successful beauty professionals. <a href="/register">Register your salon today</a> and start accepting online bookings!</p>',
                'categories' => [$categoryModels[3]->id, $categoryModels[2]->id],
                'tags' => [$tagModels[4]->id, $tagModels[3]->id, $tagModels[6]->id],
                'published_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'African Spices That Elevate Everyday Cooking',
                'slug' => 'african-spices-everyday-cooking',
                'excerpt' => 'Discover essential African spices and seasoning blends that add depth, aroma, and color to your daily meals.',
                'content' => '<h2>Flavor, Aroma, and Tradition</h2>
                <p>African kitchens are built on rich, layered flavors. Spices like suya blend, berbere, and grains of paradise bring warmth and complexity to simple dishes.</p>
                <h3>Must-Have Spices</h3>
                <ul>
                    <li>Suya Spice: A peanut-based blend with smoky heat</li>
                    <li>Berbere: Ethiopian chili and herb blend</li>
                    <li>Grains of Paradise: Peppery, citrusy notes</li>
                    <li>Cameroon Pepper: Bold and aromatic</li>
                </ul>
                <p>Start small, build a spice rack, and experiment with marinades, stews, and grilled proteins.</p>',
                'categories' => [$categoryModels[0]->id],
                'tags' => [$tagModels[0]->id, $tagModels[7]->id],
                'published_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Building a Strong Brand Story for Your Store',
                'slug' => 'building-a-strong-brand-story',
                'excerpt' => 'A clear brand story helps customers remember you and trust your products. Learn the essentials.',
                'content' => '<h2>Why Brand Story Matters</h2>
                <p>In a crowded marketplace, customers choose brands they connect with. A strong story explains who you are, what you stand for, and why your products matter.</p>
                <h3>Key Elements</h3>
                <ul>
                    <li>Origin: How your business started</li>
                    <li>Mission: The problem you solve</li>
                    <li>Proof: Testimonials and reviews</li>
                    <li>Consistency: Visual identity and tone</li>
                </ul>
                <p>Use your product pages and shop banner to reinforce your story.</p>',
                'categories' => [$categoryModels[2]->id],
                'tags' => [$tagModels[3]->id, $tagModels[6]->id],
                'published_at' => Carbon::now()->subDays(6),
            ],
            [
                'title' => 'Simple Packaging Tips That Reduce Returns',
                'slug' => 'packaging-tips-reduce-returns',
                'excerpt' => 'Better packaging protects products, reduces damage, and improves customer satisfaction.',
                'content' => '<h2>Packaging Is Part of the Product</h2>
                <p>Returns are costly. The right packaging keeps items secure and ensures a positive unboxing experience.</p>
                <h3>Practical Tips</h3>
                <ul>
                    <li>Use cushioning and proper box sizes</li>
                    <li>Label fragile items clearly</li>
                    <li>Seal edges and corners</li>
                    <li>Include care instructions</li>
                </ul>
                <p>Small improvements here can lead to fewer issues and more repeat customers.</p>',
                'categories' => [$categoryModels[2]->id],
                'tags' => [$tagModels[6]->id, $tagModels[7]->id],
                'published_at' => Carbon::now()->subDays(8),
            ],
            [
                'title' => 'Natural Hair Care Routines That Work',
                'slug' => 'natural-hair-care-routines',
                'excerpt' => 'Keep natural hair healthy with proven routines, gentle products, and consistent care.',
                'content' => '<h2>Healthy Hair Starts With Consistency</h2>
                <p>Moisture, protection, and gentle handling are the foundation of a strong routine. Use sulfate-free shampoos, rich conditioners, and protective styles that reduce breakage.</p>
                <h3>Weekly Routine</h3>
                <ul>
                    <li>Cleanse and deep condition</li>
                    <li>Moisturize and seal with oils</li>
                    <li>Protect at night with satin or silk</li>
                </ul>
                <p>Consistency is more effective than switching products too often.</p>',
                'categories' => [$categoryModels[3]->id],
                'tags' => [$tagModels[4]->id, $tagModels[5]->id],
                'published_at' => Carbon::now()->subDays(9),
            ],
            [
                'title' => 'How to Price Products for Profit',
                'slug' => 'price-products-for-profit',
                'excerpt' => 'Pricing should cover costs, account for fees, and still feel fair to customers.',
                'content' => '<h2>Balance Profit and Value</h2>
                <p>Many vendors underprice at launch. Build your price around cost, margin, and positioning in the market.</p>
                <h3>Pricing Checklist</h3>
                <ul>
                    <li>Know your cost per item</li>
                    <li>Include platform and payment fees</li>
                    <li>Compare with similar products</li>
                    <li>Test and adjust based on demand</li>
                </ul>
                <p>Pricing is a strategy, not a guess.</p>',
                'categories' => [$categoryModels[2]->id],
                'tags' => [$tagModels[3]->id, $tagModels[6]->id],
                'published_at' => Carbon::now()->subDays(11),
            ],
        ];

        foreach ($posts as $postData) {
            $categoryIds = $postData['categories'];
            $tagIds = $postData['tags'];
            unset($postData['categories']);
            unset($postData['tags']);

            $post = BlogPost::firstOrCreate(
                ['slug' => $postData['slug']],
                array_merge($postData, [
                    'author_id' => $admin->id,
                    'status' => 'published',
                    'views' => rand(50, 500),
                ])
            );

            $post->categories()->sync($categoryIds);
            $post->tags()->sync($tagIds);

            if (! $post->featured_image) {
                $file = SeedImageHelper::makeImage($post->title, 1200, 630);
                $result = $imageService->uploadAndOptimize(
                    $file,
                    'blog/'.$post->id,
                    1200,
                    630,
                    'blog_post',
                    $post->id,
                    true,
                    $seedFolderId
                );
                $post->update(['featured_image' => $result['original']]);
                @unlink($file->getRealPath());
            }
        }

        Auth::logout();
        $this->command->info('Created sample blog posts with categories, tags, and featured images.');
    }
}
