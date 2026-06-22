<?php

namespace App\Observers;

use App\Models\KnowledgeDocument;
use App\Services\Chatbot\KnowledgeBaseService;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBaseObserver
{
    public function __construct(private string $sourceType) {}

    public function saved(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            $this->deleteDocument($model);

            return;
        }

        app(KnowledgeBaseService::class)->upsertModel($model, $this->sourceType);
    }

    public function deleted(Model $model): void
    {
        $this->deleteDocument($model);
    }

    protected function shouldSkip(Model $model): bool
    {
        if ($model->getAttribute('is_published') === false) {
            return true;
        }

        return false;
    }

    protected function deleteDocument(Model $model): void
    {
        KnowledgeDocument::query()
            ->where('source_type', $this->sourceType)
            ->where('source_id', $model->getKey())
            ->delete();
    }
}
