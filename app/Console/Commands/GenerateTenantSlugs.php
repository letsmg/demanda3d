<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class GenerateTenantSlugs extends Command
{
    protected $signature = 'tenants:generate-slugs';
    protected $description = 'Popula fantasy_slug para todos os tenants que não possuem.';

    public function handle(): int
    {
        $tenants = Tenant::whereNull('fantasy_slug')->orWhere('fantasy_slug', '')->get();

        if ($tenants->isEmpty()) {
            $this->info('Todos os tenants já possuem fantasy_slug.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $name = $tenant->fantasy_name ?: $tenant->company_name ?: 'loja-' . $tenant->id;
            $tenant->fantasy_slug = Tenant::generateUniqueFantasySlug($name, $tenant->id);
            $tenant->save();
            $this->line("✓ Tenant #{$tenant->id}: {$tenant->fantasy_slug}");
        }

        $this->info("\n" . $tenants->count() . ' tenant(s) atualizado(s).');

        return self::SUCCESS;
    }
}