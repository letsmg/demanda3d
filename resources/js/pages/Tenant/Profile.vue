<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import WelcomeLayout from '@/layouts/WelcomeLayout.vue';

defineOptions({ layout: WelcomeLayout });

interface Product {
  id: number;
  name: string;
  slug: string;
  sale_price: number;
  description?: string;
  images?: { url: string }[];
  categories?: { slug: string; name: string }[];
}

interface TenantData {
  fantasy_name: string;
  company_name: string;
  fantasy_slug: string;
  logo_url: string | null;
  banner_url: string | null;
  state: string;
  city: string;
  rating_average: number;
  rating_count: number;
}

const props = defineProps<{
  tenant: TenantData;
  products: Product[];
  categories: { slug: string; name: string }[];
  filters: Record<string, string>;
}>();

const form = useForm({
  search: props.filters.search || '',
  category: props.filters.category || '',
  sort: props.filters.sort || 'name',
});

function submitFilters() {
  const query = new URLSearchParams();
  if (form.search) query.set('search', form.search);
  if (form.category) query.set('category', form.category);
  if (form.sort) query.set('sort', form.sort);

  window.location.search = query.toString();
}
</script>

<template>
  <Head :title="`${tenant.fantasy_name} — Demanda 3D`" />

  <!-- Banner da loja -->
  <div v-if="tenant.banner_url" class="w-full h-48 md:h-64 bg-amber-100 overflow-hidden">
    <img :src="tenant.banner_url" :alt="tenant.fantasy_name" class="w-full h-full object-cover" />
  </div>

  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header da loja -->
    <div class="flex flex-col md:flex-row items-start gap-6 mb-8">
      <div v-if="tenant.logo_url" class="w-24 h-24 rounded-full bg-white shadow-md overflow-hidden flex-shrink-0">
        <img :src="tenant.logo_url" :alt="tenant.fantasy_name" class="w-full h-full object-cover" />
      </div>
      <div>
        <h1 class="text-3xl font-bold text-amber-900">{{ tenant.fantasy_name }}</h1>
        <p class="text-amber-600 text-sm">
          {{ tenant.city }}, {{ tenant.state }}
        </p>
        <p v-if="tenant.rating_count > 0" class="text-amber-700 text-sm mt-1">
          ⭐ {{ tenant.rating_average }} ({{ tenant.rating_count }} avaliações)
        </p>
      </div>
    </div>

    <!-- Aviso: loja específica -->
    <div class="bg-amber-100 border border-amber-300 rounded-lg p-4 mb-8">
      <p class="text-amber-800 text-sm">
        Você está visualizando apenas produtos de <strong>{{ tenant.fantasy_name }}</strong>.
        <a :href="`/store?` + new URLSearchParams({ search: form.search, category: form.category, sort: form.sort }).toString()" class="font-semibold text-amber-900 hover:underline">
          Clique aqui para ver todos os produtos disponíveis
        </a>
      </p>
    </div>

    <!-- Filtros -->
    <form @submit.prevent="submitFilters" class="flex flex-wrap gap-3 mb-6">
      <input
        v-model="form.search"
        type="text"
        placeholder="Buscar produtos..."
        class="border border-amber-300 rounded-lg px-4 py-2 text-amber-900 placeholder:text-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500"
        @change="submitFilters"
      />
      <select
        v-model="form.category"
        class="border border-amber-300 rounded-lg px-4 py-2 text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
        @change="submitFilters"
      >
        <option value="">Todas as categorias</option>
        <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
          {{ cat.name }}
        </option>
      </select>
      <select
        v-model="form.sort"
        class="border border-amber-300 rounded-lg px-4 py-2 text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
        @change="submitFilters"
      >
        <option value="name">Nome</option>
        <option value="sale_price">Preço</option>
        <option value="created_at">Mais recentes</option>
      </select>
    </form>

    <!-- Grid de produtos -->
    <div v-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <a
        v-for="product in products"
        :key="product.id"
        :href="`/store/${product.slug}`"
        class="bg-white rounded-lg shadow-sm border border-amber-100 overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="aspect-square bg-amber-50 overflow-hidden">
          <img
            v-if="product.images && product.images.length > 0"
            :src="product.images[0].url"
            :alt="product.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-amber-300">
            Sem imagem
          </div>
        </div>
        <div class="p-4">
          <h3 class="font-semibold text-amber-900 truncate">{{ product.name }}</h3>
          <p class="text-amber-700 font-bold mt-1">
            R$ {{ Number(product.sale_price).toFixed(2) }}
          </p>
        </div>
      </a>
    </div>
    <div v-else class="text-center py-16 text-amber-600">
      Nenhum produto encontrado nesta loja.
    </div>
  </div>
</template>