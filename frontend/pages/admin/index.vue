<script setup lang="ts">
definePageMeta({ layout: 'admin', title: 'Dashboard', middleware: 'admin' })
useSeoMeta({ title: 'Admin Dashboard' })

const config = useRuntimeConfig()
const { data: stats, pending } = await useFetch('/admin/dashboard/stats', {
  baseURL: config.public.apiBase,
  headers: computed(() => {
    const token = useCookie('auth_token').value
    return token ? { Authorization: `Bearer ${token}` } : {}
  }),
})
</script>

<template>
  <div class="space-y-6">
    <!-- Stats Grid -->
    <div v-if="pending" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="card p-6 h-28 animate-pulse bg-gray-50" />
    </div>

    <div v-else-if="stats" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Revenue -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500 font-medium">Revenue (30d)</p>
          <div class="w-9 h-9 rounded-xl bg-matcha-100 flex items-center justify-center text-matcha-600">
            ₱
          </div>
        </div>
        <p class="font-serif text-2xl font-bold text-charcoal">
          ₱{{ Number(stats.revenue?.total ?? 0).toLocaleString() }}
        </p>
        <p class="text-xs mt-1" :class="stats.revenue?.change >= 0 ? 'text-green-500' : 'text-red-500'">
          {{ stats.revenue?.change >= 0 ? '+' : '' }}{{ stats.revenue?.change }}% vs last month
        </p>
      </div>

      <!-- Orders -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500 font-medium">Orders (30d)</p>
          <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 text-lg">📦</div>
        </div>
        <p class="font-serif text-2xl font-bold text-charcoal">{{ stats.orders?.total ?? 0 }}</p>
        <div class="flex gap-2 mt-1 flex-wrap">
          <span class="text-xs text-orange-500">{{ stats.orders?.by_status?.pending ?? 0 }} pending</span>
          <span class="text-xs text-green-500">{{ stats.orders?.by_status?.delivered ?? 0 }} delivered</span>
        </div>
      </div>

      <!-- Customers -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500 font-medium">Customers</p>
          <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 text-lg">👥</div>
        </div>
        <p class="font-serif text-2xl font-bold text-charcoal">{{ stats.customers?.total ?? 0 }}</p>
        <p class="text-xs text-matcha-600 mt-1">+{{ stats.customers?.new ?? 0 }} new this month</p>
      </div>

      <!-- Top Product -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm text-gray-500 font-medium">Top Product</p>
          <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg">⭐</div>
        </div>
        <p class="font-medium text-charcoal text-sm line-clamp-2">
          {{ stats.top_products?.[0]?.product_name ?? '—' }}
        </p>
        <p class="text-xs text-gray-400 mt-1">{{ stats.top_products?.[0]?.total_sold ?? 0 }} sold</p>
      </div>
    </div>

    <!-- Top Products Table -->
    <div class="card p-6">
      <h2 class="font-serif text-lg font-semibold mb-4">Top Products (30 days)</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-left">
              <th class="pb-3 font-medium text-gray-500">Product</th>
              <th class="pb-3 font-medium text-gray-500 text-right">Sold</th>
              <th class="pb-3 font-medium text-gray-500 text-right">Revenue</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(product, i) in stats?.top_products ?? []"
              :key="i"
              class="border-b border-gray-50"
            >
              <td class="py-3 text-charcoal">{{ product.product_name }}</td>
              <td class="py-3 text-right text-gray-600">{{ product.total_sold }}</td>
              <td class="py-3 text-right font-medium text-matcha-700">₱{{ Number(product.total_revenue).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <NuxtLink to="/admin/products/new" class="card p-5 flex items-center gap-3 hover:shadow-matcha transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-matcha-100 flex items-center justify-center text-matcha-600 font-bold">+</div>
        <span class="font-medium text-sm">Add Product</span>
      </NuxtLink>
      <NuxtLink to="/admin/orders" class="card p-5 flex items-center gap-3 hover:shadow-matcha transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">📦</div>
        <span class="font-medium text-sm">View Orders</span>
      </NuxtLink>
      <NuxtLink to="/admin/homepage" class="card p-5 flex items-center gap-3 hover:shadow-matcha transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">🏠</div>
        <span class="font-medium text-sm">Homepage Builder</span>
      </NuxtLink>
      <NuxtLink to="/admin/media" class="card p-5 flex items-center gap-3 hover:shadow-matcha transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">🖼</div>
        <span class="font-medium text-sm">Media Library</span>
      </NuxtLink>
    </div>
  </div>
</template>
