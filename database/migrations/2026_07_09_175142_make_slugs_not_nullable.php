<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfillSlugs();

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('fantasy_slug')->nullable(false)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('fantasy_slug')->nullable()->unique()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->change();
        });
    }

    private function backfillSlugs(): void
    {
        $tenants = DB::table('tenants')->whereNull('fantasy_slug')->orWhere('fantasy_slug', '')->get();
        foreach ($tenants as $tenant) {
            $encrypted = $tenant->fantasy_name_encrypted;
            if ($encrypted) {
                try {
                    $name = \Illuminate\Support\Facades\Crypt::decryptString($encrypted);
                } catch (\Exception) {
                    $name = 'loja-' . $tenant->id;
                }
            } else {
                $name = 'loja-' . $tenant->id;
            }
            $slug = \Illuminate\Support\Str::slug($name);
            // Garante unicidade
            $base = $slug;
            $counter = 1;
            while (DB::table('tenants')->where('fantasy_slug', $slug)->where('id', '!=', $tenant->id)->exists()) {
                $slug = $base . '-' . $counter;
                $counter++;
            }
            DB::table('tenants')->where('id', $tenant->id)->update(['fantasy_slug' => $slug]);
        }

        $products = DB::table('products')->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($products as $product) {
            $slug = \Illuminate\Support\Str::slug($product->name);
            $base = $slug;
            $counter = 1;
            while (DB::table('products')->where('slug', $slug)->where('tenant_id', $product->tenant_id)->where('id', '!=', $product->id)->exists()) {
                $slug = $base . '-' . $counter;
                $counter++;
            }
            DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
        }
    }
};