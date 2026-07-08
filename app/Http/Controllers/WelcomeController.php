<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    /**
     * Renderiza a página inicial com as imagens do carrossel.
     *
     * As imagens são lidas de storage/app/public/imgs/home/ e ordenadas
     * por nome (para manter a sequência 3d-1, 3d-2, ...).
     */
    public function __invoke(): Response
    {
        $heroImages = [];

        foreach (Storage::disk('public')->files('imgs/home') as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['webp', 'jpg', 'jpeg', 'png', 'gif'], true)) {
                $heroImages[] = [
                    'filename' => basename($file),
                    'url' => Storage::disk('public')->url($file),
                ];
            }
        }

        // Ordena por nome natural (3d-1, 3d-2, ..., 3d-10)
        usort($heroImages, fn ($a, $b) => strnatcmp($a['filename'], $b['filename']));

        return Inertia::render('Welcome', [
            'heroImages' => array_map(fn ($img) => $img['url'], $heroImages),
        ]);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.