<?php

namespace App\Http\Controllers;

use App\Models\AboutPageContent;
use App\Models\Category;
use App\Models\SitePage;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function home(Request $request, VendorStoreController $vendorStoreController)
    {
        $storefront = $vendorStoreController->showFromHost($request, app(SeoService::class));
        if ($storefront !== null) {
            return $storefront;
        }

        $seo = app(SeoService::class);
        $seo->setTitle(config('app.name').' - Authentic African Products & Services', false)
            ->setDescription('Shop authentic African food, clothing, and book salon services. Supporting African culture worldwide through our multi-vendor marketplace.')
            ->setKeywords([config('app.name'), 'african products', 'african food', 'african clothing', 'african salon', 'african marketplace'])
            ->setCanonical(route('home'));

        $seo->addStructuredData($seo->generateOrganizationSchema());

        $shopCategories = Category::parents()
            ->active()
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        $heroStats = [
            ['value' => \App\Models\Product::where('is_active', true)->count().'+' , 'label' => 'Products'],
            ['value' => \App\Models\Vendor::where('status', 'approved')->count().'+' , 'label' => 'Vendors'],
            ['value' => Category::active()->count().'+' , 'label' => 'Categories'],
        ];

        return view('pages.home', compact('shopCategories', 'heroStats'));
    }

    public function about()
    {
        $seo = app(SeoService::class);
        $seo->setTitle('About Us')
            ->setDescription('Learn about '.config('app.name').' - connecting cultures through authentic African products and services. Supporting vendors across Africa and the diaspora.')
            ->setKeywords(['about '.config('app.name'), 'african marketplace', 'african vendors', 'african diaspora'])
            ->setCanonical(route('about'));

        $contents = AboutPageContent::active()->ordered()->get();

        return view('pages.about', compact('contents'));
    }

    public function faq()
    {
        $seo = app(SeoService::class);
        $seo->setTitle('Frequently Asked Questions')
            ->setDescription('Find answers to common questions about shopping, selling, and booking services on '.config('app.name').'.')
            ->setKeywords(['faq', 'help', 'support', 'questions', config('app.name').' help'])
            ->setCanonical(route('faq'));

        return view('pages.faq');
    }

    public function terms()
    {
        $seo = app(SeoService::class);
        $seo->setTitle('Terms of Service')
            ->setDescription('Read the terms and conditions for using '.config('app.name').' platform.')
            ->setKeywords(['terms of service', 'terms and conditions', 'legal', config('app.name').' terms'])
            ->setCanonical(route('terms'));

        return view('pages.terms');
    }

    public function privacy()
    {
        $seo = app(SeoService::class);
        $seo->setTitle('Privacy Policy')
            ->setDescription('Learn how '.config('app.name').' protects your privacy and handles your personal information.')
            ->setKeywords(['privacy policy', 'data protection', 'privacy', config('app.name').' privacy'])
            ->setCanonical(route('privacy'));

        return view('pages.privacy');
    }

    public function vendorGuide()
    {
        $seo = app(SeoService::class);
        $seo->setTitle('Become a Vendor - Complete Guide')
            ->setDescription('Learn how to become a successful vendor on '.config('app.name').'. Step-by-step guide to creating your vendor profile, adding products, and growing your business.')
            ->setKeywords(['become a vendor', 'vendor guide', 'sell on '.config('app.name'), 'vendor registration', 'how to sell'])
            ->setCanonical(route('vendor.guide'));

        return view('pages.vendor-guide');
    }

    public function customPage(SitePage $page)
    {
        abort_unless($page->is_published, 404);

        $seo = app(SeoService::class);
        $seo->setTitle($page->meta_title ?: $page->title)
            ->setDescription($page->meta_description ?: ($page->excerpt ?: Str::limit(strip_tags($page->content), 160)))
            ->setCanonical(route('pages.show', $page));

        return view('pages.custom', compact('page'));
    }
}
