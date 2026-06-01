<script setup lang="ts">
import type { PaginatedResponse, Product } from '~/types/models'

definePageMeta({ layout: 'admin', title: 'Products', middleware: 'admin' })
useSeoMeta({ title: 'Products' })

const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')
const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)
const { $api } = useNuxtApp()

const search = ref('')
const page = ref(1)
const { normalizeUrl } = useImageUrl()
const query = computed(() => ({ search: search.value || undefined, page: page.value }))

const { data, pending, refresh } = await useFetch<PaginatedResponse<Product>>('/admin/products', {
  baseURL: config.public.apiBase,
  headers: authHeaders,
  query,
  watch: [query],
})

async function deleteProduct(id: number) {
  if (!confirm('Delete this product? This cannot be undone.')) return
  await $api(`/admin/products/${id}`, { method: 'DELETE', headers: authHeaders.value })
  await refresh()
}

function statusBadge(product: Product) {
  if (!product.is_active) return { label: 'Draft', class: 'bg-gray-100 text-gray-600' }
  if (product.stock === 0) return { label: 'Out of Stock', class: 'bg-red-100 text-red-600' }
  return { label: 'Active', class: 'bg-green-100 text-green-700' }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <h1 class="font-serif text-2xl font-semibold">Products</h1>
      <div class="flex gap-3">
        <input v-model="search" type="search" placeholder="Search products..." class="input-field !w-auto text-sm py-2" />
        <NuxtLink to="/admin/products/new" class="btn-primary text-sm px-4 py-2">+ New Product</NuxtLink>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-500">Product</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500 hidden md:table-cell">SKU</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500">Price</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500 hidden sm:table-cell">Stock</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="pending" v-for="i in 8" :key="i">
              <td colspan="6" class="px-4 py-3">
                <div class="h-4 bg-gray-100 rounded animate-pulse" />
              </td>
            </tr>
            <tr v-for="product in data?.data" :key="product.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <NuxtImg
                    :src="normalizeUrl(product.images?.[0]?.url || product.thumbnail) || 'https://placehold.co/40x40/e5f2db/5a9e3c?text=P'"
                    :alt="product.name"
                    class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
                    width="40"
                    height="40"
                  />
                  <div>
                    <p class="font-medium text-charcoal line-clamp-1">{{ product.name }}</p>
                    <p class="text-xs text-gray-400">{{ product.category?.name }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-500 hidden md:table-cell font-mono text-xs">{{ product.sku }}</td>
              <td class="px-4 py-3 font-medium">
                ₱{{ Number(product.sale_price || product.price).toLocaleString() }}
                <span v-if="product.sale_price" class="text-xs text-gray-400 line-through ml-1">₱{{ Number(product.price).toLocaleString() }}</span>
              </td>
              <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ product.stock }}</td>
              <td class="px-4 py-3">
                <span :class="['badge text-xs', statusBadge(product).class]">{{ statusBadge(product).label }}</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <NuxtLink :to="`/admin/products/${product.id}`" class="btn-ghost text-xs px-2 py-1">Edit</NuxtLink>
                  <button class="text-red-500 hover:text-red-700 text-xs px-2 py-1" @click="deleteProduct(product.id)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <UPagination
      v-if="data"
      :current-page="data.current_page"
      :last-page="data.last_page"
      :total="data.total"
      @change="page = $event"
    />
  </div>
</template>
