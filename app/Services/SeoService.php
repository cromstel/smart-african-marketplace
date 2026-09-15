<?php

namespace App\Services;

class SeoService
{
    protected array $meta = [];

    protected array $og = [];

    protected array $twitter = [];

    protected ?string $canonical = null;

    protected array $structuredData = [];

    public function setTitle(string $title, bool $appendSiteName = true): self
    {
        $siteName = config('app.name', 'Smart African Marketplace');
        $this->meta['title'] = $appendSiteName ? "{$title} - {$siteName}" : $title;
        $this->og['title'] = $title;
        $this->twitter['title'] = $title;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->meta['description'] = $description;
        $this->og['description'] = $description;
        $this->twitter['description'] = $description;

        return $this;
    }

    public function setKeywords(string|array $keywords): self
    {
        if (is_array($keywords)) {
            $keywords = implode(', ', $keywords);
        }
        $this->meta['keywords'] = $keywords;

        return $this;
    }

    public function setImage(string $imageUrl): self
    {
        $this->og['image'] = $imageUrl;
        $this->twitter['image'] = $imageUrl;

        return $this;
    }

    public function setCanonical(string $url): self
    {
        $this->canonical = $url;
        $this->og['url'] = $url;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->og['type'] = $type;

        return $this;
    }

    public function setTwitterCard(string $card = 'summary_large_image'): self
    {
        $this->twitter['card'] = $card;

        return $this;
    }

    public function addStructuredData(array $data): self
    {
        $this->structuredData[] = $data;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->meta['title'] ?? config('app.name', 'Smart African Marketplace');
    }

    public function getDescription(): ?string
    {
        return $this->meta['description'] ?? config('seo.description');
    }

    public function getKeywords(): ?string
    {
        return $this->meta['keywords'] ?? config('seo.keywords');
    }

    public function getCanonical(): ?string
    {
        return $this->canonical ?? url()->current();
    }

    public function getOpenGraph(): array
    {
        return array_merge([
            'site_name' => config('app.name', 'Smart African Marketplace'),
            'type' => 'website',
            'locale' => 'en_US',
        ], $this->og);
    }

    public function getTwitter(): array
    {
        return array_merge([
            'card' => 'summary_large_image',
            'site' => '@africanmarket',
        ], $this->twitter);
    }

    public function getStructuredData(): array
    {
        return $this->structuredData;
    }

    public function generateProductSchema(object $product): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->description,
            'sku' => $product->sku ?? $product->id,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('shop.show', $product->slug),
                'priceCurrency' => 'USD',
                'price' => $product->price,
                'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $product->vendor->business_name ?? config('app.name'),
                ],
            ],
        ];

        if ($product->images && $product->images->isNotEmpty()) {
            $schema['image'] = $product->images->first()->image_path;
        }

        if ($product->reviews && $product->reviews->isNotEmpty()) {
            $avgRating = $product->reviews->avg('rating');
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($avgRating, 1),
                'reviewCount' => $product->reviews->count(),
            ];
        }

        if ($product->category) {
            $schema['category'] = $product->category->name;
        }

        return $schema;
    }

    public function generateBreadcrumbSchema(array $breadcrumbs): array
    {
        $items = [];
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    public function generateOrganizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name', 'Smart African Marketplace'),
            'url' => url('/'),
            'logo' => asset(config('branding.logo')),
            'sameAs' => array_values(array_filter(config('seo.social', []))),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => config('mail.from.address'),
            ],
        ];
    }

    public function generateArticleSchema(object $article): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt ?? substr(strip_tags($article->content), 0, 200),
            'datePublished' => $article->published_at?->toIso8601String() ?? $article->created_at->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $article->author->name ?? config('app.name').' Team',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'Smart African Marketplace'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset(config('branding.logo')),
                ],
            ],
        ];

        if ($article->featured_image) {
            $schema['image'] = $article->featured_image;
        }

        return $schema;
    }

    /**
     * Generate schema for auction listings
     */
    public function generateAuctionSchema(object $auction): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Auction',
            'name' => $auction->title,
            'description' => $auction->description,
            'url' => route('auctions.show', $auction->slug),
            'startDate' => $auction->starts_at?->toIso8601String(),
            'endDate' => $auction->ends_at?->toIso8601String(),
            'seller' => [
                '@type' => 'Organization',
                'name' => $auction->vendor->business_name ?? $auction->vendor->name ?? config('app.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'USD',
                'price' => $auction->current_price,
                'priceValidUntil' => $auction->ends_at?->toDateString(),
                'availability' => $auction->status === 'active' && $auction->ends_at && $auction->ends_at->isFuture()
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ];

        $auctionImages = $auction->product?->images;
        if ($auctionImages && $auctionImages->isNotEmpty()) {
            $schema['image'] = $auctionImages->first()->image_path;
        }

        return $schema;
    }

    /**
     * Generate schema for auction listing page (collection of auctions)
     */
    public function generateAuctionListSchema(array $auctions): array
    {
        $items = [];
        foreach ($auctions as $index => $auction) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => route('auctions.show', $auction->slug),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items,
        ];
    }
}
