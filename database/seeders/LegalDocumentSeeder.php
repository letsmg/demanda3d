<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Terms of Service v1
        LegalDocument::firstOrCreate(
            ['type' => LegalDocument::TYPE_TERMS_OF_SERVICE, 'version' => 1],
            [
                'title' => 'Termos de Uso — Demanda3D',
                'content_html' => file_get_contents(public_path('legal/terms_of_service.html')),
                'published_at' => $now,
            ],
        );

        $this->command->info('✓ Termos de Uso v1 criado.');

        // Privacy Policy v1
        LegalDocument::firstOrCreate(
            ['type' => LegalDocument::TYPE_PRIVACY_POLICY, 'version' => 1],
            [
                'title' => 'Política de Privacidade — Demanda3D',
                'content_html' => file_get_contents(public_path('legal/privacy_policy.html')),
                'published_at' => $now,
            ],
        );

        $this->command->info('✓ Política de Privacidade v1 criada.');
    }
}