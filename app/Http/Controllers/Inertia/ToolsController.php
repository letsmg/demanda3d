<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use Artisan;
use Illuminate\Http\RedirectResponse;
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

        return Inertia::render('Tools/Index', [
            'sitemap' => [
                'exists' => file_exists($sitemapPath),
                'last_generated' => $sitemapDate,
                'product_count' => $productCount,
            ],
        ]);
    }

    /** Gera o sitemap.xml. */
    public function generateSitemap(): RedirectResponse
    {
        Artisan::call('sitemap:generate');

        return redirect()->route('tools.index')
            ->with('success', 'Sitemap gerado com sucesso!');
    }
}