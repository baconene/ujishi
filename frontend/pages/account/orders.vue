<script setup lang="ts">
import type { Order, PaginatedResponse } from '~/types/models'

definePageMeta({ middleware: 'auth' })
useSeoMeta({ title: 'My Orders' })

const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')
const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)

const { data: orders } = await useFetch<PaginatedResponse<Order>>('/orders', {
  baseURL: config.public.apiBase, headers: authHeaders,
})

const statusColors: Record<string, string> = {
  pending: 'bg-amber-100 text-amber-700',
  processing: 'bg-blue-100 text-blue-700',
  shipped: 'bg-purple-100 text-purple-700',
  delivered: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
  refunded: 'bg-gray-100 text-gray-700',
}
</script>

<template>
  <div class="min-h-screen py-10 px-4">
    <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-3 mb-6">
        <NuxtLink to="/account" class="btn-ghost px-3 py-2">&larr;</NuxtLink>
        <h1 class="font-serif text-2xl font-semibold">My Orders</h1>
      </div>

      <div v-if="!orders?.data?.length" class="text-center py-16 card p-8">
        <p class="font-serif text-xl text-gray-400 mb-4">No orders yet</p>
        <NuxtLink to="/products" class="btn-primary">Start Shopping</NuxtLink>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="order in orders.data"
          :key="order.id"
          class="card p-5"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <p class="font-mono font-semibold text-matcha-700">{{ order.order_number }}</p>
              <p class="text-sm text-gray-400">{{ new Date(order.created_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
            </div>
            <span :class="['badge', statusColors[order.status]]">{{ order.status }}</span>
          </div>
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">{{ order.items?.length ?? 0 }} items</p>
            <p class="font-semibold text-lg">₱{{ Number(order.total).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
