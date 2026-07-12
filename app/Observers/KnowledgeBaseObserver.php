<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Models\KnowledgeDocument;
use App\Models\Product;
use App\Models\SalonService;
use App\Models\SitePage;
use App\Services\Chatbot\KnowledgeBaseService;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseObserver
{
    public function saved(Model $model): void
    {
        $sourceType = $this->sourceTypeFor($model);

        if ($this->shouldSkip($model)) {
            $this->deleteDocument($model, $sourceType);

            return;
        }

        app(KnowledgeBaseService::class)->upsertModel($model, $sourceType);
    }

    public function deleted(Model $model): void
    {
        $this->deleteDocument($model, $this->sourceTypeFor($model));
    }

    /**
     * Resolve the knowledge-base source type from the observed model class.
     */
    protected function sourceTypeFor(Model $model): string
    {
        return match (get_class($model)) {
            Product::class => 'product',
            SalonService::class => 'salon_service',
            BlogPost::class => 'blog_post',
            SitePage::class => 'site_page',
            default => throw new \InvalidArgumentException('KnowledgeBaseObserver does not support model: '.get_class($model)),
        };
    }

    protected function shouldSkip(Model $model): bool
    {
        if ($model->getAttribute('is_published') === false) {
            return true;
        }

        return false;
    }

    protected function deleteDocument(Model $model, string $sourceType): void
    {
        KnowledgeDocument::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $model->getKey())
            ->delete();
    }
}
