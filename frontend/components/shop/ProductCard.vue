<script setup lang="ts">
import type { Product } from '~/types/models'

const props = defineProps<{ product: Product }>()
const cartStore = useCartStore()
const adding = ref(false)
const { normalizeUrl } = useImageUrl()

const effectivePrice = computed(() => Number(props.product.sale_price || props.product.price))
const originalPrice = computed(() => Number(props.product.price))
const isOnSale = computed(() => !!props.product.sale_price && Number(props.product.sale_price) < Number(props.product.price))
const discountPct = computed(() =>
  isOnSale.value ? Math.round((1 - effectivePrice.value / originalPrice.value) * 100) : 0,
)

const thumbnail = computed(() => normalizeUrl(props.product.images?.[0]?.url || props.product.thumbnail))

async function addToCart() {
  adding.value = true
  try {
    await cartStore.addItem(props.product.id)
  } finally {
    adding.value = false
  }
}
</script>

<template>
  <div class="card group flex flex-col overflow-hidden">
    <!-- Image -->
    <NuxtLink :to="`/products/${product.slug}`" class="relative aspect-square overflow-hidden">
      <NuxtImg
        :src="thumbnail || 'https://placehold.co/400x400/e5f2db/5a9e3c?text=No+Image'"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        loading="lazy"
        width="400"
        height="400"
      />
      <div class="absolute top-3 left-3 flex flex-col gap-1">
        <span v-if="isOnSale" class="badge-sale">-{{ discountPct }}%</span>
        <span v-if="product.is_best_seller" class="badge-matcha">Best Seller</span>
        <span v-if="product.is_featured" class="badge bg-amber-100 text-amber-700">Featured</span>
      </div>
      <div v-if="product.stock === 0" class="absolute inset-0 bg-white/70 flex items-center justify-center">
        <span class="badge bg-gray-100 text-gray-600 text-sm">Out of Stock</span>
      </div>
    </NuxtLink>

    <!-- Info -->
    <div class="p-3 sm:p-4 flex flex-col flex-1 gap-1.5 sm:gap-2">
      <NuxtLink :to="`/products/${product.slug}`">
        <p class="text-xs text-matcha-600 font-medium">{{ product.category?.name }}</p>
        <h3 class="font-serif text-sm sm:text-base font-semibold text-charcoal group-hover:text-matcha-600 transition-colors line-clamp-2 mt-0.5">
          {{ product.name }}
        </h3>
      </NuxtLink>

      <div class="hidden sm:flex items-center gap-1">
        <URating :value="product.average_rating ?? 0" size="sm" readonly />
        <span class="text-xs text-gray-400">({{ product.average_rating?.toFixed(1) ?? '0.0' }})</span>
      </div>

      <div class="flex flex-col gap-2 mt-auto pt-1 sm:pt-2">
        <div>
          <span class="font-semibold text-charcoal text-base sm:text-lg">₱{{ effectivePrice.toLocaleString() }}</span>
          <span v-if="isOnSale" class="text-xs sm:text-sm text-gray-400 line-through ml-1 sm:ml-2">₱{{ originalPrice.toLocaleString() }}</span>
        </div>
        <UButton
          size="sm"
          full
          :loading="adding"
          :disabled="product.stock === 0"
          @click.prevent="addToCart"
        >
          <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add to Cart
        </UButton>
      </div>
    </div>
  </div>
</template>
