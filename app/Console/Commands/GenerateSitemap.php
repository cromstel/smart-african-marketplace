<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\SalonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate XML sitemap for SEO';

    public function handle()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $sitemap .= $this->addUrl(url('/'), '1.0', 'daily');
        $sitemap .= $this->addUrl(route('shop.index'), '0.9', 'daily');
        $sitemap .= $this->addUrl(route('salon.index'), '0.9', 'daily');

        $products = Product::where('is_active', true)->get();
        foreach ($products as $product) {
            $sitemap .= $this->addUrl(
                route('shop.show', $product->slug),
                '0.8',
                'weekly',
                $product->updated_at->toAtomString()
            );
        }

        $salonServices = SalonService::where('is_active', true)->get();
        foreach ($salonServices as $service) {
            $sitemap .= $this->addUrl(
                route('salon.show', $service->slug),
                '0.7',
                'weekly',
                $service->updated_at->toAtomString()
            );
        }

        $sitemap .= '</urlset>';

        File::put(public_path('sitemap.xml'), $sitemap);

        $this->info('Sitemap generated successfully at public/sitemap.xml');

        return 0;
    }

    private function addUrl(string $loc, string $priority, string $changefreq, ?string $lastmod = null): string
    {
        $url = '<url>';
        $url .= '<loc>'.htmlspecialchars($loc).'</loc>';
        if ($lastmod) {
            $url .= '<lastmod>'.$lastmod.'</lastmod>';
        }
        $url .= '<changefreq>'.$changefreq.'</changefreq>';
        $url .= '<priority>'.$priority.'</priority>';
        $url .= '</url>';

        return $url;
    }
}
