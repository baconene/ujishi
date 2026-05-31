<script setup lang="ts">
definePageMeta({ middleware: 'admin' })

const authStore = useAuthStore()
const route = useRoute()
const sidebarOpen = ref(true)

const navItems = [
  { label: 'Dashboard', href: '/admin', icon: 'grid' },
  { label: 'Products', href: '/admin/products', icon: 'package' },
  { label: 'Categories', href: '/admin/categories', icon: 'tag' },
  { label: 'Orders', href: '/admin/orders', icon: 'shopping-bag' },
  { label: 'Customers', href: '/admin/customers', icon: 'users' },
  { label: 'Coupons', href: '/admin/coupons', icon: 'percent' },
  { label: 'Homepage Builder', href: '/admin/homepage', icon: 'layout' },
  { label: 'Carousel', href: '/admin/carousel', icon: 'image' },
  { label: 'Banners', href: '/admin/banners', icon: 'flag' },
  { label: 'Media Library', href: '/admin/media', icon: 'folder' },
  { label: 'Reviews', href: '/admin/reviews', icon: 'star' },
  { label: 'CMS Pages', href: '/admin/pages', icon: 'file-text' },
  { label: 'Settings', href: '/admin/settings', icon: 'settings' },
]

function isActive(href: string): boolean {
  if (href === '/admin') return route.path === '/admin'
  return route.path.startsWith(href)
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-100 flex flex-col transition-all duration-300',
        sidebarOpen ? 'w-64' : 'w-16',
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center px-4 border-b border-gray-100 gap-3">
        <div class="w-8 h-8 rounded-full bg-matcha-600 flex items-center justify-center text-white font-serif font-bold flex-shrink-0">宇</div>
        <Transition name="fade">
          <span v-if="sidebarOpen" class="font-serif font-semibold text-charcoal truncate">Uji-Shi Admin</span>
        </Transition>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
        <NuxtLink
          v-for="item in navItems"
          :key="item.href"
          :to="item.href"
          :class="['admin-nav-item', { active: isActive(item.href) }]"
          :title="!sidebarOpen ? item.label : undefined"
        >
          <AdminNavIcon :icon="item.icon" class="w-5 h-5 flex-shrink-0" />
          <Transition name="fade">
            <span v-if="sidebarOpen" class="text-sm truncate">{{ item.label }}</span>
          </Transition>
        </NuxtLink>
      </nav>

      <!-- User -->
      <div class="p-4 border-t border-gray-100">
        <button
          class="flex items-center gap-3 w-full text-left hover:bg-gray-50 rounded-xl p-2 transition-colors"
          @click="authStore.logout().then(() => navigateTo('/auth/login'))"
        >
          <div class="w-8 h-8 rounded-full bg-matcha-100 flex items-center justify-center text-matcha-700 font-medium text-sm flex-shrink-0">
            {{ authStore.user?.name?.[0] ?? 'A' }}
          </div>
          <Transition name="fade">
            <div v-if="sidebarOpen" class="flex-1 min-w-0">
              <div class="text-sm font-medium text-charcoal truncate">{{ authStore.user?.name }}</div>
              <div class="text-xs text-gray-400">Sign out</div>
            </div>
          </Transition>
        </button>
      </div>
    </aside>

    <!-- Main -->
    <div :class="['flex-1 flex flex-col min-h-screen transition-all duration-300', sidebarOpen ? 'ml-64' : 'ml-16']">
      <!-- Top Bar -->
      <header class="h-16 bg-white border-b border-gray-100 flex items-center px-6 gap-4 sticky top-0 z-40">
        <button
          class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
          @click="sidebarOpen = !sidebarOpen"
        >
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <div class="flex-1">
          <h1 class="text-lg font-serif font-semibold text-charcoal">
            {{ $route.meta.title ?? 'Admin Panel' }}
          </h1>
        </div>

        <NuxtLink to="/" target="_blank" class="text-sm text-matcha-600 hover:text-matcha-700 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
          View Store
        </NuxtLink>
      </header>

      <!-- Content -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
