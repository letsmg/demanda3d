<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\MeilisearchService;

/**
 * Observer que mantém o índice Meilisearch sincronizado com o PostgreSQL.
 *
 * Dispara automaticamente nas operações de create, update, delete e restore
 * do Eloquent. Utiliza o MeilisearchService que gerencia graceful degradation
 * quando o Meilisearch está indisponível (fallback silencioso).
 */
class ProductMeilisearchObserver
{
    public function __construct(
        private readonly MeilisearchService $meilisearchService,
    ) {}

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->meilisearchService->syncProduct($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->meilisearchService->syncProduct($product);
    }

    /**
     * Handle the Product "deleted" event.
     *
     * SoftDelete: o produto é removido do índice.
     * Se o produto for restaurado posteriormente, o evento 'restored' cuida disso.
     */
    public function deleted(Product $product): void
    {
        $this->meilisearchService->removeProduct($product);
    }

    /**
     * Handle the Product "restored" event (SoftDelete restore).
     */
    public function restored(Product $product): void
    {
        $this->meilisearchService->syncProduct($product);
    }

    /**
     * Handle the Product "forceDeleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->meilisearchService->removeProduct($product);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.