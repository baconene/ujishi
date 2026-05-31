<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useSeoMeta({ title: 'My Account' })

const authStore = useAuthStore()

const tabs = [
  { label: 'My Orders', href: '/account/orders' },
  { label: 'Wishlist', href: '/account/wishlist' },
  { label: 'Addresses', href: '/account/addresses' },
]
</script>

<template>
  <div class="min-h-screen py-10 px-4">
    <div class="max-w-4xl mx-auto">
      <div class="flex items-center gap-4 mb-8">
        <div class="w-16 h-16 rounded-full bg-matcha-100 flex items-center justify-center text-matcha-700 font-serif text-2xl font-bold">
          {{ authStore.user?.name?.[0] }}
        </div>
        <div>
          <h1 class="font-serif text-2xl font-semibold">{{ authStore.user?.name }}</h1>
          <p class="text-gray-400">{{ authStore.user?.email }}</p>
        </div>
        <div class="ml-auto flex gap-3">
          <NuxtLink v-if="authStore.isAdmin" to="/admin" class="btn-outline text-sm px-4 py-2">Admin Panel</NuxtLink>
          <UButton variant="ghost" @click="authStore.logout().then(() => navigateTo('/'))">Sign Out</UButton>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink
          v-for="tab in tabs"
          :key="tab.href"
          :to="tab.href"
          class="card p-6 hover:shadow-matcha transition-shadow flex items-center gap-4"
        >
          <div class="w-10 h-10 rounded-xl bg-matcha-100 flex items-center justify-center text-matcha-600 font-bold">→</div>
          <span class="font-medium">{{ tab.label }}</span>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>
