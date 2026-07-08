<?php

namespace Database\Seeders;

use App\Models\SecurityLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class SecurityLogSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('=== Criando logs de segurança simulados ===');

        $admin = User::where('email', 'admin@demanda3d.com')->first();
        $management = User::where('email', 'tech3d@demanda3d.com.br')->first();

        if (! $admin) {
            $this->command?->warn('⚠ Admin não encontrado.');

            return;
        }

        // Log de teste — violação adulta
        SecurityLog::create([
            'tenant_id' => $management?->tenant_id,
            'user_id' => $management?->id ?? $admin->id,
            'attempted_at' => now()->subDays(2),
            'violation_type' => 'ADULT',
            'raw_response' => [
                'adult' => 'LIKELY',
                'violence' => 'VERY_UNLIKELY',
                'racy' => 'POSSIBLE',
                'medical' => 'VERY_UNLIKELY',
                'spoof' => 'VERY_UNLIKELY',
            ],
        ]);

        // Log de teste — violação violência
        SecurityLog::create([
            'tenant_id' => $management?->tenant_id,
            'user_id' => $management?->id ?? $admin->id,
            'attempted_at' => now()->subDay(),
            'violation_type' => 'VIOLENCE',
            'raw_response' => [
                'adult' => 'UNLIKELY',
                'violence' => 'LIKELY',
                'racy' => 'UNLIKELY',
                'medical' => 'VERY_UNLIKELY',
                'spoof' => 'VERY_UNLIKELY',
            ],
        ]);

        $this->command?->line('    ✓ 2 logs de segurança criados');
    }
}