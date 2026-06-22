<?php

namespace App\Services\Chatbot;

use App\Models\BlogPost;
use App\Models\KnowledgeDocument;
use App\Models\Product;
use App\Models\SalonService;
use App\Models\SitePage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class KnowledgeBaseService
{
    public function ensureIndexed(): void
    {
        if (\App\Models\KnowledgeChunk::query()->exists()) {
            return;
        }

        $lockKey = 'chatbot_knowledge_reindexing';
        if (! Cache::add($lockKey, true, now()->addMinutes(10))) {
            return;
        }

        try {
            $this->reindexAll();
        } finally {
            Cache::forget($lockKey);
        }
    }

    public function reindexAll(): void
    {
        Product::query()->chunkById(100, fn ($rows) => $rows->each(fn ($m) => $this->upsertModel($m, 'product')));
        SalonService::query()->chunkById(100, fn ($rows) => $rows->each(fn ($m) => $this->upsertModel($m, 'salon_service')));
        BlogPost::query()->chunkById(100, fn ($rows) => $rows->each(fn ($m) => $this->upsertModel($m, 'blog_post')));
        SitePage::query()->published()->chunkById(100, fn ($rows) => $rows->each(fn ($m) => $this->upsertModel($m, 'site_page')));

        // Static page sources
        $staticPages = collect([
            ['source_id' => 1, 'title' => 'About', 'content' => 'African Market platform information, mission, and support details.'],
            ['source_id' => 2, 'title' => 'FAQ', 'content' => 'Frequently asked questions for orders, payments, shipping, and returns.'],
            ['source_id' => 3, 'title' => 'Terms', 'content' => 'Terms and conditions for using African Market services and purchases.'],
            ['source_id' => 4, 'title' => 'Privacy', 'content' => 'Privacy policy for personal data, cookies, and communications.'],
        ]);

        $staticPages->each(function (array $page) {
            $doc = KnowledgeDocument::updateOrCreate(
                ['source_type' => 'static_page', 'source_id' => $page['source_id']],
                ['title' => $page['title'], 'content' => $page['content'], 'meta' => []]
            );
            $this->syncChunks($doc);
        });
    }

    public function upsertModel(Model $model, string $sourceType): void
    {
        $title = $model->name ?? $model->title ?? class_basename($model).' '.$model->getKey();
        $content = trim(implode("\n\n", array_filter([
            $model->name ?? null,
            $model->title ?? null,
            $model->description ?? null,
            $model->short_description ?? null,
            $model->excerpt ?? null,
            $model->content ?? null,
        ])));

        if ($content === '') {
            return;
        }

        $document = KnowledgeDocument::updateOrCreate(
            ['source_type' => $sourceType, 'source_id' => $model->getKey()],
            ['title' => $title, 'content' => $content, 'meta' => []]
        );

        $this->syncChunks($document);
    }

    public function search(string $query, int $limit = 4): Collection
    {
        $terms = collect(explode(' ', Str::lower($query)))
            ->filter(fn ($term) => strlen($term) > 2)
            ->values();

        if ($terms->isEmpty()) {
            return collect();
        }
        $queryBuilder = \App\Models\KnowledgeChunk::query();

        $queryBuilder->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhereRaw('LOWER(content) LIKE ?', ['%'.$term.'%']);
            }
        });

        $scoreSql = $terms->map(function ($term) {
            return 'CASE WHEN LOWER(content) LIKE ? THEN 1 ELSE 0 END';
        })->implode(' + ');

        $bindings = $terms->map(fn ($term) => '%'.$term.'%')->all();

        return $queryBuilder
            ->select('knowledge_chunks.*')
            ->selectRaw($scoreSql.' as relevance_score', $bindings)
            ->orderByDesc('relevance_score')
            ->limit($limit)
            ->get();
    }

    private function syncChunks(KnowledgeDocument $document): void
    {
        $text = strip_tags($document->content);
        $chunks = str_split($text, 700);

        $document->chunks()->delete();
        foreach ($chunks as $index => $chunkText) {
            $chunkText = trim($chunkText);
            if ($chunkText === '') {
                continue;
            }
            $document->chunks()->create([
                'chunk_index' => $index,
                'content' => $chunkText,
                'embedding' => null,
            ]);
        }
    }
}
