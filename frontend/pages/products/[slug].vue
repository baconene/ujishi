<script setup lang="ts">
import type { Product } from '~/types/models'

const route = useRoute()
const cartStore = useCartStore()
const config = useRuntimeConfig()
const apiBase = computed(() => (config.public.apiBase || '').replace(/\/api\/?$/, ''))

function normalizeUrl(url?: string | null) {
  if (!url) return ''
  if (/^https?:\/\//i.test(url)) return url
  if (url.startsWith('/')) return apiBase.value + url
  return apiBase.value + (url.startsWith('storage') ? '/' : '/storage/') + url.replace(/^\/+/, '')
}

const { data, error } = await useFetch<{ product: Product; related: Product[] }>(
  `/products/${route.params.slug}`,
  { baseURL: config.public.apiBase },
)

if (error.value) {
  throw createError({ statusCode: 404, message: 'Product not found' })
}

const product = computed(() => data.value?.product)
const related = computed(() => data.value?.related ?? [])
const selectedImageIndex = ref(0)
const quantity = ref(1)
const adding = ref(false)
const activeTab = ref<'description' | 'reviews'>('description')

const allImages = computed(() => {
  const imgs = (product.value?.images ?? []).filter(img => img.url)
  return imgs.length ? imgs : [{ url: product.value?.thumbnail, alt: product.value?.name }]
})

const effectivePrice = computed(() => Number(product.value?.sale_price || product.value?.price))
const isOnSale = computed(() => !!product.value?.sale_price && Number(product.value.sale_price) < Number(product.value.price))

useSeoMeta({
  title: product.value?.seo_title ?? product.value?.name,
  description: product.value?.seo_description ?? product.value?.description,
  ogImage: product.value?.thumbnail,
})

async function addToCart() {
  if (!product.value) return
  adding.value = true
  try {
    await cartStore.addItem(product.value.id, quantity.value)
  } finally {
    adding.value = false
  }
}
</script>

<template>
  <div v-if="product" class="min-h-screen py-10 px-4">
    <div class="max-w-7xl mx-auto">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
        <NuxtLink to="/" class="hover:text-matcha-600">Home</NuxtLink>
        <span>/</span>
        <NuxtLink to="/products" class="hover:text-matcha-600">Products</NuxtLink>
        <span>/</span>
        <NuxtLink v-if="product.category" :to="`/products?category=${product.category.slug}`" class="hover:text-matcha-600">
          {{ product.category.name }}
        </NuxtLink>
        <span v-if="product.category">/</span>
        <span class="text-charcoal">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
        <!-- Gallery -->
        <div class="space-y-4">
          <div class="aspect-square rounded-2xl overflow-hidden bg-matcha-50">
            <NuxtImg
              :src="normalizeUrl(allImages[selectedImageIndex]?.url) || 'https://placehold.co/800x800/e5f2db/5a9e3c?text=No+Image'"
              :alt="allImages[selectedImageIndex]?.alt || product.name"
              class="w-full h-full object-cover"
              width="800"
              height="800"
            />
          </div>
          <div v-if="allImages.length > 1" class="grid grid-cols-5 gap-2">
            <button
              v-for="(img, i) in allImages"
              :key="i"
              :class="['rounded-lg overflow-hidden aspect-square border-2 transition-all', selectedImageIndex === i ? 'border-matcha-500' : 'border-transparent']"
              @click="selectedImageIndex = i"
            >
              <NuxtImg :src="normalizeUrl(img.url)" :alt="img.alt || ''" class="w-full h-full object-cover" width="120" height="120" />
            </button>
          </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
          <div>
            <NuxtLink v-if="product.category" :to="`/products?category=${product.category.slug}`" class="badge-matcha">
              {{ product.category.name }}
            </NuxtLink>
            <h1 class="font-serif text-3xl font-bold text-charcoal mt-3">{{ product.name }}</h1>
            <div class="flex items-center gap-3 mt-3">
              <URating :value="product.average_rating ?? 0" />
              <span class="text-sm text-gray-400">({{ product.approved_reviews?.length ?? 0 }} reviews)</span>
            </div>
          </div>

          <!-- Price -->
          <div class="flex items-center gap-4">
            <span class="font-serif text-4xl font-bold text-charcoal">₱{{ effectivePrice.toLocaleString() }}</span>
            <span v-if="isOnSale" class="text-xl text-gray-400 line-through">₱{{ Number(product.price).toLocaleString() }}</span>
            <span v-if="isOnSale" class="badge-sale text-sm">
              Save ₱{{ (Number(product.price) - effectivePrice).toLocaleString() }}
            </span>
          </div>

          <!-- Stock -->
          <div class="flex items-center gap-2">
            <div :class="['w-2 h-2 rounded-full', product.stock > 0 ? 'bg-green-400' : 'bg-red-400']" />
            <span class="text-sm text-gray-600">
              {{ product.stock > 0 ? `${product.stock} in stock` : 'Out of stock' }}
            </span>
          </div>

          <!-- Description -->
          <p class="text-gray-600 leading-relaxed">{{ product.description }}</p>

          <!-- Quantity + Add to Cart -->
          <div class="flex gap-4">
            <div class="flex items-center border border-gray-200 rounded-lg">
              <button
                class="px-4 py-3 hover:bg-gray-50 transition-colors rounded-l-lg"
                @click="quantity = Math.max(1, quantity - 1)"
              >-</button>
              <span class="px-4 py-3 min-w-[3rem] text-center font-medium">{{ quantity }}</span>
              <button
                class="px-4 py-3 hover:bg-gray-50 transition-colors rounded-r-lg"
                @click="quantity = Math.min(product.stock, quantity + 1)"
              >+</button>
            </div>
            <UButton
              size="lg"
              full
              :loading="adding"
              :disabled="product.stock === 0"
              @click="addToCart"
            >
              {{ product.stock > 0 ? 'Add to Cart' : 'Out of Stock' }}
            </UButton>
          </div>

          <div class="grid grid-cols-3 gap-4 py-6 border-t border-b border-gray-100">
            <div class="text-center">
              <div class="text-2xl mb-1">🍵</div>
              <div class="text-xs text-gray-500">Ceremonial Grade</div>
            </div>
            <div class="text-center">
              <div class="text-2xl mb-1">🌿</div>
              <div class="text-xs text-gray-500">100% Natural</div>
            </div>
            <div class="text-center">
              <div class="text-2xl mb-1">🚚</div>
              <div class="text-xs text-gray-500">Fast Delivery</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Blocks -->
      <div v-if="product.content_blocks?.length" class="mb-16 space-y-8">
        <div v-for="block in product.content_blocks" :key="block.id">
          <!-- Rich Text -->
          <div
            v-if="block.type === 'rich_text'"
            class="prose prose-matcha max-w-none"
            v-html="(block.content as any).html"
          />

          <!-- Ingredients -->
          <div v-else-if="block.type === 'ingredients'" class="card p-6">
            <h3 class="font-serif text-xl font-semibold mb-4">Ingredients</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div
                v-for="item in (block.content as any).items"
                :key="item.name"
                class="flex justify-between items-center py-2 border-b border-gray-100"
              >
                <span class="text-charcoal">{{ item.name }}</span>
                <span class="text-matcha-600 font-medium text-sm">{{ item.amount }}</span>
              </div>
            </div>
          </div>

          <!-- FAQ -->
          <div v-else-if="block.type === 'faq'" class="space-y-3">
            <h3 class="font-serif text-2xl font-semibold mb-6">Frequently Asked Questions</h3>
            <details
              v-for="faq in (block.content as any).items"
              :key="faq.q"
              class="card p-5 group"
            >
              <summary class="cursor-pointer font-medium text-charcoal list-none flex items-center justify-between">
                {{ faq.q }}
                <span class="text-matcha-500 group-open:rotate-180 transition-transform">&#8964;</span>
              </summary>
              <p class="mt-3 text-gray-600 text-sm leading-relaxed">{{ faq.a }}</p>
            </details>
          </div>
        </div>
      </div>

      <!-- Reviews -->
      <div v-if="product.approved_reviews?.length" class="mb-16">
        <h2 class="font-serif text-2xl font-semibold mb-6">Customer Reviews</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="review in product.approved_reviews"
            :key="review.id"
            class="card p-5"
          >
            <div class="flex items-center gap-2 mb-2">
              <URating :value="review.rating" size="sm" />
              <span class="text-xs text-gray-400">{{ new Date(review.created_at).toLocaleDateString() }}</span>
            </div>
            <p v-if="review.title" class="font-semibold text-sm mb-1">{{ review.title }}</p>
            <p class="text-gray-600 text-sm">{{ review.body }}</p>
            <p class="text-xs text-gray-400 mt-2">— {{ review.user?.name }}</p>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div v-if="related.length">
        <h2 class="font-serif text-2xl font-semibold mb-6">You Might Also Like</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <ProductCard v-for="p in related" :key="p.id" :product="p" />
        </div>
      </div>
    </div>
  </div>
</template>
