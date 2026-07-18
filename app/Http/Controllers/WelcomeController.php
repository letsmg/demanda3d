<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        // 1. Tenta buscar em 'imgs/site/home/optimized' (imagens otimizadas)
        $heroImages = $this->getImagesFromDirectory('imgs/site/home/optimized');

        // 2. Se estiver vazio, tenta buscar em 'imgs/site/home/original' (fallback)
        if (empty($heroImages)) {
            $heroImages = $this->getImagesFromDirectory('imgs/site/home/original');
        }
    
        return Inertia::render('Welcome', [
            'heroImages' => $heroImages,
        ]);
    }

    /**
     * Helper para ler, filtrar, ordenar e formatar as imagens de um diretório
     */
    private function getImagesFromDirectory(string $directory): array
{
    $basePath = storage_path('app/public/' . $directory);

    // 1. Check de Pasta
    if (!is_dir($basePath)) {
        return ["ERRO: Pasta não encontrada em $basePath"];
    }

    // 2. Tenta listar com glob simples (sem o GLOB_BRACE que pode falhar em alguns servidores)
    // Buscamos tudo primeiro para garantir que estamos vendo algo
    $allFiles = glob($basePath . '/*');
    
    if (empty($allFiles)) {
        return ["ERRO: A pasta existe ($basePath), mas está vazia."];
    }

    // 3. Filtro manual em vez de glob complexo (mais seguro)
    $validExtensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
    $images = [];

    foreach ($allFiles as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (in_array($ext, $validExtensions)) {
            $filename = basename($file);
            $url = asset('storage/' . $directory . '/' . $filename) . '?v=' . filemtime($file);
            
            $images[] = [
                'filename' => $filename,
                'url' => $url,
            ];
        }
    }

    if (empty($images)) {
        return ["ERRO: Arquivos encontrados, mas nenhum com extensão válida (" . implode(', ', $validExtensions) . ")"];
    }

    // Ordena por nome natural
    usort($images, fn ($a, $b) => strnatcmp($a['filename'], $b['filename']));

    return array_map(fn ($img) => $img['url'], $images);
}
}