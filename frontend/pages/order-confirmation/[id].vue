<script setup lang="ts">
import type { Order } from '~/types/models'

definePageMeta({ middleware: 'auth' })
const route = useRoute()
const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')

const { data: order } = await useFetch<Order>(`/orders/${route.params.id}`, {
  baseURL: config.public.apiBase,
  headers: authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
})

useSeoMeta({ title: `Order Confirmed - ${order.value?.order_number ?? ''}` })
</script>

<template>
  <div class="min-h-screen py-16 px-4">
    <div class="max-w-2xl mx-auto text-center">
      <div class="w-20 h-20 rounded-full bg-matcha-100 flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-matcha-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h1 class="font-serif text-3xl font-bold text-charcoal mb-3">Order Confirmed!</h1>
      <p class="text-gray-500 text-lg mb-2">Thank you for your order.</p>
      <p class="font-mono text-matcha-700 font-semibold text-xl mb-8">{{ order?.order_number }}</p>

      <div v-if="order" class="card p-6 text-left space-y-4 mb-8">
        <h2 class="font-serif text-lg font-semibold">Order Details</h2>
        <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-100">
          <span class="text-gray-700">{{ item.product_name }} × {{ item.quantity }}</span>
          <span class="font-medium">₱{{ Number(item.subtotal).toLocaleString() }}</span>
        </div>
        <div class="flex justify-between font-semibold text-lg pt-2">
          <span>Total</span>
          <span class="text-matcha-700">₱{{ Number(order.total).toLocaleString() }}</span>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <NuxtLink to="/account/orders" class="btn-primary">View My Orders</NuxtLink>
        <NuxtLink to="/products" class="btn-outline">Continue Shopping</NuxtLink>
      </div>
    </div>
  </div>
</template>
