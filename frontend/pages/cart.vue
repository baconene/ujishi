<script setup lang="ts">
const cartStore = useCartStore()
const router = useRouter()

onMounted(() => {
  cartStore.fetchCart()
})

async function checkout() {
  await router.push('/checkout')
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-16">
    <div class="flex flex-col gap-6">
      <header class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-matcha-600">Shopping Cart</p>
          <h1 class="font-serif text-4xl font-semibold text-charcoal">Your Cart</h1>
        </div>
        <div class="text-sm text-gray-500">{{ cartStore.count }} item(s)</div>
      </header>

      <div v-if="cartStore.loading" class="flex items-center justify-center min-h-[40vh]">
        <div class="w-10 h-10 border-4 border-matcha-200 border-t-matcha-500 rounded-full animate-spin" />
      </div>

      <div v-else-if="cartStore.items.length === 0" class="rounded-3xl border border-matcha-100 bg-matcha-50 p-12 text-center">
        <p class="text-xl font-semibold text-charcoal mb-3">Your cart is empty</p>
        <p class="text-gray-500 mb-6">Add premium matcha drinks, pastries, and gift sets to get started.</p>
        <UButton @click="router.push('/products')">Browse Products</UButton>
      </div>

      <div v-else class="grid gap-8 lg:grid-cols-[1.6fr_0.9fr]">
        <section class="space-y-4">
          <div
            v-for="item in cartStore.items"
            :key="item.id"
            class="card flex flex-col gap-4 p-4 md:flex-row md:items-center"
          >
            <NuxtImg
              :src="item.product.images?.[0]?.url || item.product.thumbnail || ''"
              :alt="item.product.name"
              class="w-full rounded-xl object-cover md:w-40 h-40"
              width="160"
              height="160"
            />
            <div class="flex-1">
              <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                  <p class="font-semibold text-charcoal">{{ item.product.name }}</p>
                  <p class="text-sm text-gray-500">{{ item.product.category?.name }}</p>
                </div>
                <p class="font-semibold text-matcha-600">₱{{ Number(item.product.sale_price || item.product.price).toLocaleString() }}</p>
              </div>
              <div class="mt-4 flex flex-wrap items-center gap-3">
                <button
                  class="h-10 w-10 rounded border border-gray-200 text-lg font-semibold hover:border-matcha-500"
                  @click="item.quantity > 1 ? cartStore.updateItem(item.id, item.quantity - 1) : cartStore.removeItem(item.id)"
                >
                  -
                </button>
                <span class="min-w-[2rem] text-center">{{ item.quantity }}</span>
                <button
                  class="h-10 w-10 rounded border border-gray-200 text-lg font-semibold hover:border-matcha-500"
                  @click="cartStore.updateItem(item.id, item.quantity + 1)"
                >
                  +
                </button>
                <button class="ml-auto text-sm text-red-500 hover:text-red-600" @click="cartStore.removeItem(item.id)">
                  Remove
                </button>
              </div>
            </div>
          </div>
        </section>

        <aside class="rounded-3xl border border-matcha-100 bg-matcha-50 p-6 space-y-6">
          <div>
            <p class="text-sm uppercase tracking-[0.25em] text-gray-500">Order Summary</p>
            <p class="text-3xl font-semibold text-charcoal mt-3">₱{{ cartStore.subtotal.toLocaleString() }}</p>
          </div>
          <UButton full size="lg" @click="checkout">Proceed to Checkout</UButton>
          <p class="text-xs text-gray-500">Shipping and taxes will be calculated at checkout.</p>
        </aside>
      </div>
    </div>
  </div>
</template>
