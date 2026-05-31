<script setup lang="ts">
const cartStore = useCartStore()
const router = useRouter()

async function checkout() {
  cartStore.closeCart()
  await router.push('/checkout')
}
</script>

<template>
  <Teleport to="body">
    <!-- Overlay -->
    <Transition name="fade">
      <div v-if="cartStore.isOpen" class="fixed inset-0 bg-black/40 z-40" @click="cartStore.closeCart()" />
    </Transition>

    <!-- Drawer -->
    <Transition name="slide-right">
      <div v-if="cartStore.isOpen" class="fixed right-0 top-0 bottom-0 w-full max-w-sm bg-white z-50 flex flex-col shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
          <h2 class="font-serif text-xl font-semibold">My Cart ({{ cartStore.count }})</h2>
          <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors" @click="cartStore.closeCart()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Items -->
        <div class="flex-1 overflow-y-auto p-5">
          <div v-if="cartStore.items.length === 0" class="flex flex-col items-center justify-center h-full gap-4 text-center">
            <div class="w-20 h-20 rounded-full bg-matcha-50 flex items-center justify-center">
              <svg class="w-10 h-10 text-matcha-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <div>
              <p class="font-serif text-lg font-semibold">Your cart is empty</p>
              <p class="text-gray-500 text-sm mt-1">Discover our premium matcha collection</p>
            </div>
            <UButton @click="cartStore.closeCart(); navigateTo('/products')">Start Shopping</UButton>
          </div>

          <div v-else class="space-y-4">
            <div v-for="item in cartStore.items" :key="item.id" class="flex gap-3">
              <NuxtImg
                :src="item.product.images?.[0]?.url || item.product.thumbnail || ''"
                :alt="item.product.name"
                class="w-16 h-16 rounded-lg object-cover flex-shrink-0 bg-matcha-50"
                width="64"
                height="64"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-charcoal line-clamp-1">{{ item.product.name }}</p>
                <p class="text-matcha-600 font-semibold text-sm">
                  ₱{{ Number(item.product.sale_price || item.product.price).toLocaleString() }}
                </p>
                <div class="flex items-center gap-2 mt-2">
                  <button
                    class="w-6 h-6 rounded border border-gray-200 flex items-center justify-center text-sm hover:border-matcha-400"
                    @click="item.quantity > 1 ? cartStore.updateItem(item.id, item.quantity - 1) : cartStore.removeItem(item.id)"
                  >
                    -
                  </button>
                  <span class="text-sm w-4 text-center">{{ item.quantity }}</span>
                  <button
                    class="w-6 h-6 rounded border border-gray-200 flex items-center justify-center text-sm hover:border-matcha-400"
                    @click="cartStore.updateItem(item.id, item.quantity + 1)"
                  >
                    +
                  </button>
                  <button class="ml-auto text-gray-400 hover:text-red-500 transition-colors" @click="cartStore.removeItem(item.id)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="cartStore.items.length > 0" class="p-5 border-t border-gray-100 space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Subtotal</span>
            <span class="font-semibold text-lg">₱{{ cartStore.subtotal.toLocaleString() }}</span>
          </div>
          <p class="text-xs text-gray-400 text-center">
            Shipping and taxes calculated at checkout
          </p>
          <UButton full size="lg" @click="checkout">
            Proceed to Checkout
          </UButton>
          <UButton full variant="ghost" @click="cartStore.closeCart(); navigateTo('/cart')">
            View Full Cart
          </UButton>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-right-enter-active, .slide-right-leave-active { transition: transform 0.3s ease; }
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
</style>
