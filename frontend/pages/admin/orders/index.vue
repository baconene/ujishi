<script setup lang="ts">
import type { Order, PaginatedResponse } from '~/types/models'

definePageMeta({ layout: 'admin', title: 'Orders', middleware: 'admin' })
useSeoMeta({ title: 'Orders' })

const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')
const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)
const { $api } = useNuxtApp()

const search = ref('')
const statusFilter = ref('')
const page = ref(1)
const query = computed(() => ({ search: search.value || undefined, status: statusFilter.value || undefined, page: page.value }))

const { data, pending, refresh } = await useFetch<PaginatedResponse<Order>>('/admin/orders', {
  baseURL: config.public.apiBase, headers: authHeaders, query,
})

const statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']

const statusColors: Record<string, string> = {
  pending: 'bg-amber-100 text-amber-700',
  processing: 'bg-blue-100 text-blue-700',
  shipped: 'bg-purple-100 text-purple-700',
  delivered: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
  refunded: 'bg-gray-100 text-gray-700',
}

async function updateStatus(id: number, status: string) {
  await $api(`/admin/orders/${id}/status`, {
    method: 'PUT', headers: authHeaders.value, body: { status },
  })
  await refresh()
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <h1 class="font-serif text-2xl font-semibold">Orders</h1>
      <div class="flex gap-3 flex-wrap">
        <input v-model="search" type="search" placeholder="Search orders..." class="input-field !w-auto text-sm py-2" />
        <select v-model="statusFilter" class="input-field !w-auto text-sm py-2">
          <option value="">All Statuses</option>
          <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
        </select>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left font-medium text-gray-500">Order #</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500 hidden md:table-cell">Customer</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500">Total</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
              <th class="px-4 py-3 text-left font-medium text-gray-500 hidden lg:table-cell">Date</th>
              <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-if="pending" v-for="i in 10" :key="i">
              <td colspan="6" class="px-4 py-3"><div class="h-4 bg-gray-100 rounded animate-pulse" /></td>
            </tr>
            <tr v-for="order in data?.data" :key="order.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono font-medium text-matcha-700">{{ order.order_number }}</td>
              <td class="px-4 py-3 hidden md:table-cell text-gray-600">{{ (order as any).user?.name ?? 'Guest' }}</td>
              <td class="px-4 py-3 font-semibold">₱{{ Number(order.total).toLocaleString() }}</td>
              <td class="px-4 py-3">
                <select
                  :value="order.status"
                  :class="['badge text-xs cursor-pointer', statusColors[order.status]]"
                  @change="updateStatus(order.id, ($event.target as HTMLSelectElement).value)"
                >
                  <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                </select>
              </td>
              <td class="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">
                {{ new Date(order.created_at).toLocaleDateString() }}
              </td>
              <td class="px-4 py-3 text-right">
                <NuxtLink :to="`/admin/orders/${order.id}`" class="btn-ghost text-xs px-2 py-1">View</NuxtLink>
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
