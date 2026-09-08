<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BlogAutoGenerationService
{
    public function generateFromTitle(string $title): array
    {
        $title = trim($title);

        try {
            $apiKey = (string) config('services.openai.api_key');
            if ($apiKey === '') {
                throw new \RuntimeException('Missing API key');
            }

            $prompt = "Generate SEO title, SEO description (150-160 chars), and blog HTML content for this blog post title: {$title}. Return strict JSON keys: meta_title, meta_description, content.";

            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => Setting::get('chatbot_openai_model', 'gpt-4o-mini'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an SEO blog assistant. Output strict JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.4,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('AI call failed');
            }

            $raw = data_get($response->json(), 'choices.0.message.content');
            if (! is_string($raw) || trim($raw) === '') {
                throw new \RuntimeException('Invalid AI response');
            }

            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            $metaTitle = (string) ($decoded['meta_title'] ?? '');
            $metaDescription = (string) ($decoded['meta_description'] ?? '');
            $content = (string) ($decoded['content'] ?? '');

            if ($metaTitle === '' || $metaDescription === '' || $content === '') {
                throw new \RuntimeException('Incomplete AI payload');
            }

            return [
                'meta_title' => Str::limit($metaTitle, 255, ''),
                'meta_description' => Str::limit($metaDescription, 500, ''),
                'content' => $content,
                'source' => 'ai',
            ];
        } catch (\Throwable $e) {
            return $this->templateFallback($title);
        }
    }

    private function templateFallback(string $title): array
    {
        $metaTitle = Str::limit($title.' | Weekly African News', 255, '');
        $metaDescription = Str::limit(
            "Discover key insights about {$title}. Explore practical takeaways, trends, and what it means for buyers and sellers across ".config('app.name').'.',
            500,
            ''
        );

        $safeTitle = e($title);
        $content = "<h2>{$safeTitle}: What You Need to Know</h2>\n".
            "<p>{$safeTitle} is shaping conversations across commerce, services, and culture. This article highlights what matters most and how to act on it.</p>\n".
            "<h3>Key Highlights</h3>\n".
            "<ul><li>Why {$safeTitle} is relevant right now</li><li>What customers and vendors should watch</li><li>Practical next steps for better outcomes</li></ul>\n".
            "<h3>Practical Insights</h3>\n".
            "<p>Start with the fundamentals, review trusted examples, and apply changes incrementally. Consistent execution usually outperforms one-off efforts.</p>\n".
            "<h3>Conclusion</h3>\n".
            "<p>{$safeTitle} creates opportunities when approached with clear goals and good data. Use these insights to improve strategy and execution.</p>";

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'content' => $content,
            'source' => 'template_fallback',
        ];
    }
}
