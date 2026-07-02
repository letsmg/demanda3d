<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Gera o sitemap.xml com todas as páginas públicas do projeto';

    public function handle(): int
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $today = Carbon::now()->format('Y-m-d');

        $urls = [];

        // ── Páginas públicas ──────────────────────
        $urls[] = $this->url($baseUrl, $today, 'daily', '1.0');
        $urls[] = $this->url($baseUrl . '/store', $today, 'daily', '0.9');

        // ── Produtos ativos da store ──────────────
        $products = Product::where('is_active', true)
            ->whereNotNull('slug')
            ->get(['slug', 'updated_at']);

        foreach ($products as $product) {
            $lastmod = optional($product->updated_at)->format('Y-m-d') ?? $today;
            $urls[] = $this->url($baseUrl . '/store/' . $product->slug, $lastmod, 'weekly', '0.8');
        }

        // ── Documentos legais ─────────────────────
        $urls[] = $this->url($baseUrl . '/legal/terms', $today, 'monthly', '0.5');
        $urls[] = $this->url($baseUrl . '/legal/privacy', $today, 'monthly', '0.5');

        // ── Monta XML ─────────────────────────────
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= implode("\n", $urls);
        $xml .= "\n</urlset>\n";

        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->info('✓ Sitemap gerado com ' . count($products) . ' produtos.');

        return self::SUCCESS;
    }

    private function url(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return "    <url>\n" .
               "        <loc>{$loc}</loc>\n" .
               "        <lastmod>{$lastmod}</lastmod>\n" .
               "        <changefreq>{$changefreq}</changefreq>\n" .
               "        <priority>{$priority}</priority>\n" .
               "    </url>";
    }
}