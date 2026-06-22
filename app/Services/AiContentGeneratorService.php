<?php

namespace App\Services;

class AiContentGeneratorService
{
    private array $titleTemplates = [
        'premium' => [
            'Premium {product} - {attribute}',
            'Luxury {product} with {attribute}',
            'Elite {product} | {attribute}',
            'Professional {product} - {attribute}',
        ],
        'standard' => [
            '{product} - {attribute}',
            'Quality {product} with {attribute}',
            '{attribute} {product}',
            'Best {product} for {benefit}',
        ],
        'sale' => [
            'Sale: {product} - {attribute}',
            'Discounted {product} | {attribute}',
            'Special Offer: {product} with {attribute}',
        ],
    ];

    private array $descriptionTemplates = [
        'detailed' => 'Discover our exceptional {product} featuring {attribute}. This {adjective} product is designed for {target_audience} who value {benefit}. Made with {material}, it offers {feature_1} and {feature_2}. Perfect for {use_case}, this {product} combines {quality_1} with {quality_2} to deliver outstanding results.',

        'concise' => 'Premium {product} with {attribute}. {adjective} design for {target_audience}. Features {feature_1} and {feature_2}. Ideal for {use_case}.',

        'technical' => 'High-quality {product} engineered with {attribute}. Specifications include {feature_1}, {feature_2}, and {material} construction. Designed for {target_audience} requiring {benefit}. Suitable for {use_case} applications.',

        'marketing' => 'Transform your {use_case} with our {adjective} {product}! Featuring {attribute}, this exceptional item delivers {benefit} that {target_audience} love. With {feature_1} and {feature_2}, you\'ll experience {quality_1} like never before. Don\'t miss this opportunity to own a {product} that combines {quality_2} with style!',
    ];

    private array $adjectives = [
        'exceptional', 'outstanding', 'premium', 'high-quality', 'professional',
        'elegant', 'modern', 'stylish', 'durable', 'innovative', 'versatile',
        'reliable', 'authentic', 'handcrafted', 'luxurious', 'comfortable',
    ];

    private array $qualities = [
        'quality', 'performance', 'durability', 'style', 'functionality',
        'elegance', 'comfort', 'reliability', 'innovation', 'craftsmanship',
        'value', 'convenience', 'efficiency', 'precision', 'sophistication',
    ];

    private array $benefits = [
        'exceptional quality', 'lasting durability', 'superior performance',
        'ultimate comfort', 'unmatched style', 'outstanding value',
        'professional results', 'peace of mind', 'enhanced productivity',
        'timeless elegance', 'proven reliability', 'innovative solutions',
    ];

    private array $targetAudiences = [
        'professionals', 'enthusiasts', 'homeowners', 'families', 'individuals',
        'businesses', 'collectors', 'gift seekers', 'style-conscious buyers',
        'quality-focused customers', 'smart shoppers', 'discerning customers',
    ];

    private array $useCases = [
        'daily use', 'special occasions', 'professional work', 'home improvement',
        'personal care', 'business purposes', 'gift giving', 'collection',
        'entertainment', 'outdoor activities', 'indoor use', 'travel',
    ];

    public function generateTitle(array $productData, string $style = 'standard'): string
    {
        $templates = $this->titleTemplates[$style] ?? $this->titleTemplates['standard'];
        $template = $templates[array_rand($templates)];

        $replacements = [
            '{product}' => $productData['name'] ?? 'Product',
            '{attribute}' => $this->extractAttribute($productData),
            '{benefit}' => $this->benefits[array_rand($this->benefits)],
        ];

        $title = str_replace(array_keys($replacements), array_values($replacements), $template);

        return $this->cleanText($title);
    }

    public function generateDescription(array $productData, string $style = 'detailed'): string
    {
        $template = $this->descriptionTemplates[$style] ?? $this->descriptionTemplates['detailed'];

        $replacements = [
            '{product}' => $productData['name'] ?? 'product',
            '{attribute}' => $this->extractAttribute($productData),
            '{adjective}' => $this->adjectives[array_rand($this->adjectives)],
            '{target_audience}' => $this->targetAudiences[array_rand($this->targetAudiences)],
            '{benefit}' => $this->benefits[array_rand($this->benefits)],
            '{material}' => $this->extractMaterial($productData),
            '{feature_1}' => $this->generateFeature($productData, 1),
            '{feature_2}' => $this->generateFeature($productData, 2),
            '{use_case}' => $this->useCases[array_rand($this->useCases)],
            '{quality_1}' => $this->qualities[array_rand($this->qualities)],
            '{quality_2}' => $this->qualities[array_rand($this->qualities)],
        ];

        $description = str_replace(array_keys($replacements), array_values($replacements), $template);

        if (! empty($productData['base_description'])) {
            $description .= "\n\n".$productData['base_description'];
        }

        return $this->cleanText($description);
    }

    public function generateShortDescription(array $productData): string
    {
        $adjective = $this->adjectives[array_rand($this->adjectives)];
        $benefit = $this->benefits[array_rand($this->benefits)];
        $productName = $productData['name'] ?? 'product';

        $templates = [
            'Experience {benefit} with our {adjective} {product}.',
            'Our {adjective} {product} delivers {benefit} you can trust.',
            '{adjective} {product} designed for {benefit}.',
            'Discover the perfect {product} - {adjective} and reliable.',
        ];

        $template = $templates[array_rand($templates)];

        $replacements = [
            '{product}' => $productName,
            '{adjective}' => $adjective,
            '{benefit}' => $benefit,
        ];

        return $this->cleanText(str_replace(array_keys($replacements), array_values($replacements), $template));
    }

    public function enhanceWithKeywords(string $text, array $keywords): string
    {
        if (empty($keywords)) {
            return $text;
        }

        $keywordPhrase = implode(', ', array_slice($keywords, 0, 5));

        return $text.' Keywords: '.$keywordPhrase;
    }

    public function generateMetaDescription(array $productData): string
    {
        $productName = $productData['name'] ?? 'Product';
        $price = $productData['price'] ?? null;
        $benefit = $this->benefits[array_rand($this->benefits)];

        $priceText = $price ? ' at $'.number_format($price, 2) : '';

        return substr("Buy {$productName}{$priceText}. {$benefit}. Shop now for the best deals on quality products.", 0, 160);
    }

    private function extractAttribute(array $productData): string
    {
        if (! empty($productData['attributes']) && is_array($productData['attributes'])) {
            $attr = array_values($productData['attributes']);
            if (! empty($attr[0])) {
                return is_array($attr[0]) ? implode(', ', array_slice($attr[0], 0, 2)) : $attr[0];
            }
        }

        if (! empty($productData['category'])) {
            return $productData['category'];
        }

        return $this->benefits[array_rand($this->benefits)];
    }

    private function extractMaterial(array $productData): string
    {
        $defaultMaterials = ['premium materials', 'high-quality materials', 'durable materials', 'quality materials'];

        if (! empty($productData['attributes']['material'])) {
            return $productData['attributes']['material'];
        }

        if (! empty($productData['attributes']) && is_array($productData['attributes'])) {
            foreach ($productData['attributes'] as $key => $value) {
                if (stripos($key, 'material') !== false) {
                    return $value;
                }
            }
        }

        return $defaultMaterials[array_rand($defaultMaterials)];
    }

    private function generateFeature(array $productData, int $index): string
    {
        $genericFeatures = [
            'superior craftsmanship', 'easy maintenance', 'long-lasting performance',
            'ergonomic design', 'premium finish', 'versatile functionality',
            'enhanced durability', 'modern aesthetics', 'user-friendly operation',
            'excellent value', 'reliable construction', 'practical design',
        ];

        if (! empty($productData['features']) && is_array($productData['features'])) {
            $feature = $productData['features'][$index - 1] ?? null;
            if ($feature) {
                return $feature;
            }
        }

        if (! empty($productData['attributes']) && is_array($productData['attributes'])) {
            $attributes = array_values($productData['attributes']);
            if (isset($attributes[$index - 1])) {
                return is_string($attributes[$index - 1]) ? $attributes[$index - 1] : $genericFeatures[array_rand($genericFeatures)];
            }
        }

        return $genericFeatures[array_rand($genericFeatures)];
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        $text = ucfirst($text);

        return $text;
    }

    public function generateSeoKeywords(array $productData): array
    {
        $keywords = [];

        if (! empty($productData['name'])) {
            $keywords[] = strtolower($productData['name']);
            $nameParts = explode(' ', $productData['name']);
            $keywords = array_merge($keywords, array_map('strtolower', $nameParts));
        }

        if (! empty($productData['category'])) {
            $keywords[] = strtolower($productData['category']);
        }

        if (! empty($productData['brand'])) {
            $keywords[] = strtolower($productData['brand']);
        }

        if (! empty($productData['attributes']) && is_array($productData['attributes'])) {
            foreach ($productData['attributes'] as $value) {
                if (is_string($value)) {
                    $keywords[] = strtolower($value);
                }
            }
        }

        $keywords = array_unique($keywords);
        $keywords = array_filter($keywords, fn ($k) => strlen($k) > 2);

        return array_values(array_slice($keywords, 0, 10));
    }
}
