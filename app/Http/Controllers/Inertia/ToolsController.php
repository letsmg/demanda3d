<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ToolsController extends Controller
{
    /** Exibe a página de ferramentas do dashboard. */
    public function index(): Response
    {
        $sitemapPath = public_path('sitemap.xml');
        $sitemapDate = file_exists($sitemapPath)
            ? \Illuminate\Support\Carbon::createFromTimestamp(filemtime($sitemapPath))->format('d/m/Y H:i:s')
            : 'Nunca gerado';

        $productCount = \App\Models\Product::where('is_active', true)->whereNotNull('slug')->count();

        // Lista as imagens atuais do carrossel (home/)
        $heroImages = $this->getCurrentHeroImages();

        return Inertia::render('Tools/Index', [
            'sitemap' => [
                'exists' => file_exists($sitemapPath),
                'last_generated' => $sitemapDate,
                'product_count' => $productCount,
            ],
            'heroImages' => $heroImages,
        ]);
    }

    /** Gera o sitemap.xml. */
    public function generateSitemap(): RedirectResponse
    {
        \Artisan::call('sitemap:generate');

        return redirect()->route('tools.index')
            ->with('success', 'Sitemap gerado com sucesso!');
    }

    /**
     * Upload de imagens para o carrossel da home (admin only).
     *
     * Gera nomes sequenciais (3d-1.webp, 3d-2.webp, ...) e aplica
     * o pipeline de otimização (resize + compressão).
     */
    public function uploadHeroImages(Request $request, ImageOptimizationService $imageService): RedirectResponse
    {
        Gate::authorize('admin.only');

        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'images.required' => 'Selecione pelo menos uma imagem.',
            'images.*.image' => 'Cada arquivo deve ser uma imagem.',
            'images.*.mimes' => 'Formatos aceitos: JPG, PNG, WEBP.',
            'images.*.max' => 'Cada imagem deve ter no máximo 2MB.',
        ]);

        $uploaded = 0;
        $nextIndex = $this->getNextHeroImageIndex();

        foreach ($request->file('images') as $file) {
            $filename = "3d-{$nextIndex}";

            $imageService->processUpload($file, $filename);

            $nextIndex++;
            $uploaded++;
        }

        return redirect()->route('tools.index')
            ->with('success', "{$uploaded} imagem(ns) enviada(s) e otimizada(s) com sucesso!");
    }

    /**
     * Remove uma imagem do carrossel (admin only).
     */
    public function deleteHeroImage(Request $request): RedirectResponse
    {
        Gate::authorize('admin.only');

        $request->validate([
            'filename' => ['required', 'string'],
        ]);

        $filename = basename($request->input('filename'));
        $path = 'imgs/home/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('tools.index')
            ->with('success', 'Imagem removida com sucesso.');
    }

    /**
     * Retorna a lista de imagens atuais do carrossel com URLs absolutas.
     */
    private function getCurrentHeroImages(): array
    {
        $files = Storage::disk('public')->files('imgs/home');
        $images = [];

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['webp', 'jpg', 'jpeg', 'png', 'gif'], true)) {
                $images[] = [
                    'filename' => basename($file),
                    'url' => Storage::disk('public')->url($file),
                ];
            }
        }

        // Ordena pelo nome do arquivo
        usort($images, fn ($a, $b) => strnatcmp($a['filename'], $b['filename']));

        return $images;
    }

    /**
     * Determina o próximo índice sequencial (3d-{N}) baseado nos arquivos existentes.
     */
    private function getNextHeroImageIndex(): int
    {
        $maxIndex = 0;

        foreach (Storage::disk('public')->files('imgs/home') as $file) {
            $filename = basename($file);
            if (preg_match('/^3d-(\d+)\./', $filename, $matches)) {
                $index = (int) $matches[1];
                if ($index > $maxIndex) {
                    $maxIndex = $index;
                }
            }
        }

        return $maxIndex + 1;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.