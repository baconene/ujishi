<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()
const config = useRuntimeConfig()

const { data: settings } = await useFetch<Record<string, string>>('/settings', {
  baseURL: config.public.apiBase,
})

const logoUrl = computed(() => settings.value?.logo_url || '')

const mobileMenuOpen = ref(false)
const searchOpen = ref(false)
const searchQuery = ref('')

const navLinks = [
  { label: 'Menu', href: '/products?category=matcha-drinks' },
  { label: 'Shop', href: '/products' },
  { label: 'Gift Sets', href: '/products?category=gift-sets' },
  { label: 'Our Story', href: '/p/about' },
]

async function handleSearch() {
  if (searchQuery.value.trim()) {
    await router.push(`/products?search=${encodeURIComponent(searchQuery.value)}`)
    searchOpen.value = false
  }
}

onMounted(() => {
  cartStore.fetchCart()
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-cream">
    <!-- Announcement Bar -->
    <!-- <div class="bg-matcha-800 text-cream text-center text-xs py-2 px-4">
      Free nationwide delivery on orders ₱1,500+ · Use code <strong>MATCHA10</strong> for 10% off your first order
    </div> -->

    <!-- Header -->
    <header class="sticky top-0 z-50 bg-cream/95 backdrop-blur-sm border-b border-cream-dark">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 md:h-20">
          <!-- Logo -->
          <NuxtLink to="/" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-full bg-matcha-600 flex items-center justify-center overflow-hidden">
              <template v-if="logoUrl">
                <NuxtImg
                  :src="logoUrl"
                  alt="Uji-Shi logo"
                  class="w-full h-full object-contain bg-white"
                  width="40"
                  height="40"
                />
              </template>
              <template v-else>
                <span class="text-white font-serif font-bold text-lg">宇</span>
              </template>
            </div>
            <span class="font-serif text-xl font-semibold text-charcoal group-hover:text-matcha-600 transition-colors">
              Uji-Shi
            </span>
          </NuxtLink>

          <!-- Desktop Nav -->
          <nav class="hidden md:flex items-center gap-8">
            <NuxtLink
              v-for="link in navLinks"
              :key="link.href"
              :to="link.href"
              class="text-charcoal-light hover:text-matcha-600 font-medium transition-colors text-sm"
            >
              {{ link.label }}
            </NuxtLink>
          </nav>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <!-- Search -->
            <button
              class="p-2 rounded-lg hover:bg-matcha-50 transition-colors"
              @click="searchOpen = !searchOpen"
            >
              <svg class="w-5 h-5 text-charcoal-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>

            <!-- Wishlist -->
            <NuxtLink v-if="authStore.isAuthenticated" to="/account/wishlist" class="p-2 rounded-lg hover:bg-matcha-50 transition-colors hidden md:flex">
              <svg class="w-5 h-5 text-charcoal-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </NuxtLink>

            <!-- Account -->
            <NuxtLink :to="authStore.isAuthenticated ? '/account' : '/auth/login'" class="p-2 rounded-lg hover:bg-matcha-50 transition-colors hidden md:flex">
              <svg class="w-5 h-5 text-charcoal-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </NuxtLink>

            <!-- Cart -->
            <button
              class="relative p-2 rounded-lg hover:bg-matcha-50 transition-colors"
              @click="cartStore.openCart()"
            >
              <svg class="w-5 h-5 text-charcoal-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              <span
                v-if="cartStore.count > 0"
                class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 rounded-full bg-matcha-500 text-white text-[10px] flex items-center justify-center font-medium"
              >
                {{ cartStore.count }}
              </span>
            </button>

            <!-- Mobile menu -->
            <button
              class="p-2 rounded-lg hover:bg-matcha-50 md:hidden"
              @click="mobileMenuOpen = !mobileMenuOpen"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Search Bar -->
        <Transition name="slide-down">
          <div v-if="searchOpen" class="pb-4">
            <form @submit.prevent="handleSearch">
              <div class="relative">
                <input
                  v-model="searchQuery"
                  type="search"
                  placeholder="Search for matcha, accessories, gift sets..."
                  class="input-field pr-12"
                  autofocus
                />
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-matcha-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>

      <!-- Mobile Menu -->
      <Transition name="slide-down">
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-cream-dark bg-cream">
          <nav class="px-4 py-4 flex flex-col gap-1">
            <NuxtLink
              v-for="link in navLinks"
              :key="link.href"
              :to="link.href"
              class="py-3 px-2 text-charcoal font-medium border-b border-cream-dark"
              @click="mobileMenuOpen = false"
            >
              {{ link.label }}
            </NuxtLink>
            <NuxtLink to="/account" class="py-3 px-2 text-charcoal font-medium" @click="mobileMenuOpen = false">
              {{ authStore.isAuthenticated ? 'My Account' : 'Login / Register' }}
            </NuxtLink>
          </nav>
        </div>
      </Transition>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-matcha-900 text-cream mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
          <div class="md:col-span-2">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-10 h-10 rounded-full bg-matcha-500 flex items-center justify-center text-white font-serif font-bold text-lg">宇</div>
              <span class="font-serif text-xl font-semibold text-cream">Uji-Shi</span>
            </div>
            <p class="text-matcha-200 text-sm leading-relaxed max-w-xs">
              Premium Japanese matcha experience. Ceremonial grade matcha sourced directly from the hills of Uji, Kyoto.
            </p>
          </div>
          <div>
            <h4 class="font-serif text-lg text-cream mb-4">Shop</h4>
            <ul class="space-y-2">
              <li><NuxtLink to="/products?category=matcha-drinks" class="text-matcha-300 hover:text-cream text-sm transition-colors">Matcha Drinks</NuxtLink></li>
              <li><NuxtLink to="/products?category=pastries-food" class="text-matcha-300 hover:text-cream text-sm transition-colors">Pastries & Food</NuxtLink></li>
              <li><NuxtLink to="/products?category=tea-leaves" class="text-matcha-300 hover:text-cream text-sm transition-colors">Tea Leaves</NuxtLink></li>
              <li><NuxtLink to="/products?category=gift-sets" class="text-matcha-300 hover:text-cream text-sm transition-colors">Gift Sets</NuxtLink></li>
            </ul>
          </div>
          <div>
            <h4 class="font-serif text-lg text-cream mb-4">Help</h4>
            <ul class="space-y-2">
              <li><NuxtLink to="/p/about" class="text-matcha-300 hover:text-cream text-sm transition-colors">Our Story</NuxtLink></li>
              <li><NuxtLink to="/p/shipping" class="text-matcha-300 hover:text-cream text-sm transition-colors">Shipping Policy</NuxtLink></li>
              <li><NuxtLink to="/p/returns" class="text-matcha-300 hover:text-cream text-sm transition-colors">Returns</NuxtLink></li>
              <li><NuxtLink to="/p/contact" class="text-matcha-300 hover:text-cream text-sm transition-colors">Contact Us</NuxtLink></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-matcha-800 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-matcha-400 text-sm">© {{ new Date().getFullYear() }} Uji-Shi Matcha Cafe. All rights reserved.</p>
          <div class="flex gap-4">
            <a href="#" class="text-matcha-400 hover:text-cream transition-colors text-sm">Facebook</a>
            <a href="#" class="text-matcha-400 hover:text-cream transition-colors text-sm">Instagram</a>
            <a href="#" class="text-matcha-400 hover:text-cream transition-colors text-sm">TikTok</a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Cart Sidebar -->
    <ShopCartSidebar />
  </div>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
