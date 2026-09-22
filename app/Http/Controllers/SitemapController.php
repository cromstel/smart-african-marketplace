<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalonService;
use App\Models\Vendor;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Served as a route rather than a static public/robots.txt file so the
     * Sitemap: directive always reflects the actual configured app.url.
     * A static file previously shipped here with unprocessed Blade syntax
     * ("{{ url(...) }}") served verbatim to every crawler — see the fixed
     * bug this replaces.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin/',
            'Disallow: /vendor/',
            'Disallow: /cart/',
            'Disallow: /checkout/',
            'Disallow: /orders/',
            'Disallow: /bookings/',
            'Disallow: /profile/',
            'Disallow: /dashboard/',
            'Disallow: /wishlist/',
            'Disallow: /payment/',
            'Disallow: /api/',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /password/',
            'Disallow: /email/',
            '',
            'Allow: /shop',
            'Allow: /shop/*',
            'Allow: /salon',
            'Allow: /salon/*',
            'Allow: /blog',
            'Allow: /blog/*',
            'Allow: /about',
            'Allow: /contact',
            'Allow: /faq',
            '',
            'Crawl-delay: 1',
            '',
            'User-agent: Googlebot',
            'Allow: /',
            'Crawl-delay: 0',
            '',
            'User-agent: Bingbot',
            'Allow: /',
            'Crawl-delay: 1',
            '',
            'User-agent: Yandex',
            'Allow: /',
            'Crawl-delay: 2',
            '',
            'Sitemap: '.route('sitemap.index'),
        ];

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    public function index(): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.route('sitemap.static').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.route('sitemap.products').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.route('sitemap.salon').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.route('sitemap.blog').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.route('sitemap.auctions').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.route('sitemap.vendors').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        $sitemap .= '</sitemapindex>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function static(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('shop.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('salon.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('blog.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('about'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('contact.index'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('faq'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('terms'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('privacy'), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        return $this->generateXml($urls);
    }

    public function products(): Response
    {
        $products = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->select('slug', 'updated_at')
            ->get();

        $categories = Category::whereHas('products', function ($query) {
            $query->where('is_active', true);
        })->get();

        $urls = [];

        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('shop.index', ['category' => $category->slug]),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $category->updated_at->toAtomString(),
            ];
        }

        foreach ($products as $product) {
            $urls[] = [
                'loc' => route('shop.show', $product->slug),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $product->updated_at->toAtomString(),
            ];
        }

        return $this->generateXml($urls);
    }

    public function salon(): Response
    {
        $services = SalonService::where('is_active', true)
            ->select('slug', 'updated_at')
            ->get();

        $urls = [];

        foreach ($services as $service) {
            $urls[] = [
                'loc' => route('salon.show', $service->slug),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $service->updated_at->toAtomString(),
            ];
        }

        return $this->generateXml($urls);
    }

    public function blog(): Response
    {
        $posts = BlogPost::where('status', 'published')
            ->where('published_at', '<=', now())
            ->select('slug', 'updated_at')
            ->get();

        $urls = [];

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post->slug),
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => $post->updated_at->toAtomString(),
            ];
        }

        return $this->generateXml($urls);
    }

    /**
     * Generate sitemap for auctions
     */
    public function auctions(): Response
    {
        $auctions = Auction::where('status', 'active')
            ->where('ends_at', '>', now())
            ->select('slug', 'updated_at')
            ->get();

        $urls = [
            ['loc' => route('auctions.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('auctions.featured'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('auctions.ending-soon'), 'priority' => '0.8', 'changefreq' => 'hourly'],
        ];

        foreach ($auctions as $auction) {
            $urls[] = [
                'loc' => route('auctions.show', $auction->slug),
                'priority' => '0.7',
                'changefreq' => 'hourly',
                'lastmod' => $auction->updated_at->toAtomString(),
            ];
        }

        return $this->generateXml($urls);
    }

    /**
     * Generate sitemap for vendors
     */
    public function vendors(): Response
    {
        $vendors = Vendor::where('is_active', true)
            ->where('status', 'approved')
            ->select('slug', 'updated_at')
            ->get();

        $urls = [];

        foreach ($vendors as $vendor) {
            $urls[] = [
                'loc' => route('vendors.show', $vendor->slug),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $vendor->updated_at->toAtomString(),
            ];
        }

        return $this->generateXml($urls);
    }

    protected function generateXml(array $urls): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $sitemap .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        foreach ($urls as $url) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>'.htmlspecialchars($url['loc']).'</loc>';

            if (isset($url['lastmod'])) {
                $sitemap .= '<lastmod>'.$url['lastmod'].'</lastmod>';
            }

            if (isset($url['changefreq'])) {
                $sitemap .= '<changefreq>'.$url['changefreq'].'</changefreq>';
            }

            if (isset($url['priority'])) {
                $sitemap .= '<priority>'.$url['priority'].'</priority>';
            }

            if (isset($url['images'])) {
                foreach ($url['images'] as $image) {
                    $sitemap .= '<image:image>';
                    $sitemap .= '<image:loc>'.htmlspecialchars($image).'</image:loc>';
                    $sitemap .= '</image:image>';
                }
            }

            $sitemap .= '</url>';
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }
}
