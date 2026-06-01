<script setup lang="ts">
import type { Category, PaginatedResponse, Product } from '~/types/models'

const route = useRoute()
const router = useRouter()

const search = ref(String(route.query.search || ''))
const selectedCategory = ref(String(route.query.category || ''))
const sort = ref(String(route.query.sort || 'newest'))
const page = ref(Number(route.query.page || 1))
const priceRange = ref([0, 5000])

const config = useRuntimeConfig()

const query = computed(() => ({
  search: search.value || undefined,
  category: selectedCategory.value || undefined,
  sort: sort.value,
  page: page.value,
  per_page: 12,
  min_price: priceRange.value[0] || undefined,
  max_price: priceRange.value[1] < 5000 ? priceRange.value[1] : undefined,
}))

const { data: productsData, pending } = await useFetch<PaginatedResponse<Product>>('/products', {
  baseURL: config.public.apiBase,
  query,
})

const { data: categories } = await useFetch<Category[]>('/categories', {
  baseURL: config.public.apiBase,
})

useSeoMeta({
  title: 'Shop All Products',
  description: 'Browse our full collection of premium matcha drinks, pastries, tea leaves, accessories, and gift sets.',
})

function applyFilters() {
  page.value = 1
  router.replace({ query: { ...route.query, ...query.value } })
}

watch([search, selectedCategory, sort], () => applyFilters())
</script>

<template>
  <div class="min-h-screen py-10 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Page Title -->
      <div class="mb-8">
        <h1 class="font-serif text-3xl font-semibold text-charcoal">Shop All Products</h1>
        <p class="text-gray-500 mt-1">{{ productsData?.total ?? 0 }} products available</p>
      </div>

      <div class="flex gap-8">
        <!-- Filters (desktop) -->
        <aside class="hidden lg:block w-64 flex-shrink-0">
          <div class="card p-5 sticky top-24">
            <h3 class="font-semibold text-charcoal mb-4">Filters</h3>

            <!-- Search -->
            <div class="mb-6">
              <label class="text-sm font-medium text-gray-600 mb-2 block">Search</label>
              <input v-model="search" type="search" placeholder="Search products..." class="input-field text-sm" />
            </div>

            <!-- Categories -->
            <div class="mb-6">
              <label class="text-sm font-medium text-gray-600 mb-2 block">Category</label>
              <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="selectedCategory" type="radio" value="" class="text-matcha-500" />
                  <span class="text-sm">All Categories</span>
                </label>
                <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 cursor-pointer">
                  <input v-model="selectedCategory" type="radio" :value="cat.slug" class="text-matcha-500" />
                  <span class="text-sm">{{ cat.name }}</span>
                </label>
              </div>
            </div>
          </div>
        </aside>

        <!-- Products -->
        <div class="flex-1">
          <!-- Sort Bar -->
          <div class="flex items-center justify-between mb-6 gap-4">
            <div class="flex gap-2 overflow-x-auto pb-2 lg:hidden">
              <button
                v-for="cat in [{ name: 'All', slug: '' }, ...(categories || [])]"
                :key="cat.slug"
                :class="[
                  'px-3 py-1.5 rounded-full text-sm whitespace-nowrap transition-colors',
                  selectedCategory === cat.slug
                    ? 'bg-matcha-500 text-white'
                    : 'bg-matcha-100 text-matcha-700 hover:bg-matcha-200',
                ]"
                @click="selectedCategory = cat.slug"
              >
                {{ cat.name }}
              </button>
            </div>

            <select v-model="sort" class="ml-auto input-field !w-auto text-sm py-2">
              <option value="newest">Newest</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="name_asc">Name A–Z</option>
            </select>
          </div>

          <div v-if="pending" class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
            <div v-for="i in 12" :key="i" class="card aspect-square animate-pulse bg-matcha-50" />
          </div>

          <template v-else-if="productsData?.data?.length">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
              <ProductCard v-for="product in productsData.data" :key="product.id" :product="product" />
            </div>
            <UPagination
              :current-page="productsData.current_page"
              :last-page="productsData.last_page"
              :total="productsData.total"
              @change="page = $event; router.replace({ query: { ...route.query, page: $event } })"
            />
          </template>

          <div v-else class="text-center py-20">
            <p class="font-serif text-xl text-gray-500">No products found</p>
            <p class="text-gray-400 text-sm mt-2">Try adjusting your filters</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
