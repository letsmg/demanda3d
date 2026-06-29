<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Models\SeoSetting;
use Illuminate\Database\Seeder;

class SeoSettingSeeder extends Seeder
{
    /**
     * Seed das configurações padrão de SEO do site.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'meta_title_default', 'value' => 'Demanda3D — Marketplace de Impressão 3D', 'group' => 'general'],
            ['key' => 'meta_description_default', 'value' => 'Marketplace de impressão 3D sob demanda. Compre e venda peças impressas em 3D.', 'group' => 'general'],
            ['key' => 'canonical_url_default', 'value' => config('app.url'), 'group' => 'general'],
            ['key' => 'og_image_default', 'value' => asset('images/og-default.jpg'), 'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            SeoSetting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                ]
            );
        }

        $this->command?->info('✓ Configurações de SEO padrão criadas.');
    }
}