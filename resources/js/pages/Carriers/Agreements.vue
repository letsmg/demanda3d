<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
  agreements: {
    data: {
      id: number;
      status: string;
      created_at: string;
      tenant: { id: number; fantasy_name: string } | null;
    }[];
  };
}>();

const form = useForm({});
</script>

<template>
  <Head title="Acordos Comerciais" />
  <div class="p-6 max-w-3xl mx-auto space-y-6">
    <h1 class="text-xl font-bold">Contratos / Acordos com Vendedores</h1>

    <Card>
      <CardContent class="pt-4">
        <div v-if="agreements.data.length === 0" class="text-gray-500 text-sm">Nenhum acordo encontrado.</div>
        <table v-else class="w-full text-sm">
          <thead><tr class="border-b text-left text-gray-600"><th class="py-2">Vendedor</th><th>Status</th><th>Data</th><th class="text-right">Ação</th></tr></thead>
          <tbody>
            <tr v-for="a in agreements.data" :key="a.id" class="border-b">
              <td class="py-2">{{ a.tenant?.fantasy_name ?? '—' }}</td>
              <td><span class="px-2 py-0.5 rounded text-xs font-medium" :class="a.status === 'active' ? 'bg-green-100 text-green-700' : a.status === 'pending_carrier' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'">{{ a.status }}</span></td>
              <td class="text-xs text-gray-500">{{ new Date(a.created_at).toLocaleDateString() }}</td>
              <td class="text-right space-x-2">
                <Link v-if="a.status === 'pending_carrier'" :href="`/carrier/agreements/${a.id}/accept`" method="post" as="button" class="text-xs text-green-600 hover:underline">Aceitar</Link>
                <Link v-if="a.status === 'pending_carrier'" :href="`/carrier/agreements/${a.id}/reject`" method="post" as="button" class="text-xs text-red-600 hover:underline">Rejeitar</Link>
                <span v-else class="text-xs text-gray-400">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </CardContent>
    </Card>

    <Link href="/carrier/dashboard" class="text-sm text-blue-600 hover:underline">← Voltar ao Painel</Link>
  </div>
</template>