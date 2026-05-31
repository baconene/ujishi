<script setup lang="ts">
definePageMeta({ middleware: 'auth' })
useSeoMeta({ title: 'Checkout' })

const cartStore = useCartStore()
const { $api } = useNuxtApp()
const router = useRouter()

const loading = ref(false)
const error = ref('')
const couponCode = ref('')
const couponLoading = ref(false)
const couponDiscount = ref(0)
const appliedCoupon = ref<{ code: string } | null>(null)

const form = ref({
  name: '',
  phone: '',
  address_line1: '',
  address_line2: '',
  city: '',
  province: '',
  postal_code: '',
  payment_method: 'cod',
  notes: '',
})

const shipping = 150
const total = computed(() =>
  cartStore.subtotal - couponDiscount.value + shipping,
)

async function applyCoupon() {
  if (!couponCode.value) return
  couponLoading.value = true
  try {
    const data = await $api<{ coupon: any; discount: number }>('/cart/coupon', {
      method: 'POST',
      body: { code: couponCode.value },
      headers: { 'X-Session-Id': localStorage.getItem('cart_session_id') || '' },
    })
    appliedCoupon.value = data.coupon
    couponDiscount.value = data.discount
  } catch (e: any) {
    error.value = e?.data?.message ?? 'Invalid coupon code.'
  } finally {
    couponLoading.value = false
  }
}

async function placeOrder() {
  loading.value = true
  error.value = ''
  try {
    const sessionId = localStorage.getItem('cart_session_id') || ''
    const data = await $api<{ order: { id: number } }>('/orders', {
      method: 'POST',
      headers: { 'X-Session-Id': sessionId },
      body: {
        shipping_address: {
          name: form.value.name,
          phone: form.value.phone,
          address_line1: form.value.address_line1,
          address_line2: form.value.address_line2,
          city: form.value.city,
          province: form.value.province,
          postal_code: form.value.postal_code,
          country: 'PH',
        },
        payment_method: form.value.payment_method,
        coupon_code: appliedCoupon.value?.code,
        notes: form.value.notes,
      },
    })

    await cartStore.fetchCart()
    await router.push(`/order-confirmation/${data.order.id}`)
  } catch (e: any) {
    error.value = e?.data?.message ?? 'Failed to place order. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(() => cartStore.fetchCart())
</script>

<template>
  <div class="min-h-screen py-10 px-4">
    <div class="max-w-6xl mx-auto">
      <h1 class="font-serif text-3xl font-semibold mb-8">Checkout</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form -->
        <div class="lg:col-span-2 space-y-6">
          <div class="card p-6">
            <h2 class="font-serif text-lg font-semibold mb-4">Shipping Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
                <input v-model="form.name" type="text" class="input-field" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Phone Number</label>
                <input v-model="form.phone" type="tel" class="input-field" required />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Address Line 1</label>
                <input v-model="form.address_line1" type="text" class="input-field" required />
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Address Line 2 (optional)</label>
                <input v-model="form.address_line2" type="text" class="input-field" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">City</label>
                <input v-model="form.city" type="text" class="input-field" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Province</label>
                <input v-model="form.province" type="text" class="input-field" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Postal Code</label>
                <input v-model="form.postal_code" type="text" class="input-field" required />
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="card p-6">
            <h2 class="font-serif text-lg font-semibold mb-4">Payment Method</h2>
            <div class="space-y-3">
              <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                :class="form.payment_method === 'cod' ? 'border-matcha-500 bg-matcha-50' : 'border-gray-200'"
              >
                <input v-model="form.payment_method" type="radio" value="cod" />
                <div>
                  <div class="font-medium">Cash on Delivery</div>
                  <div class="text-sm text-gray-500">Pay when your order arrives</div>
                </div>
              </label>
              <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                :class="form.payment_method === 'paymongo' ? 'border-matcha-500 bg-matcha-50' : 'border-gray-200'"
              >
                <input v-model="form.payment_method" type="radio" value="paymongo" />
                <div>
                  <div class="font-medium">GCash / Maya / Credit Card</div>
                  <div class="text-sm text-gray-500">Secure payment via PayMongo</div>
                </div>
              </label>
            </div>
          </div>

          <div class="card p-6">
            <label class="block text-sm font-medium text-gray-600 mb-1">Order Notes (optional)</label>
            <textarea v-model="form.notes" class="input-field" rows="3" placeholder="Special instructions..." />
          </div>
        </div>

        <!-- Summary -->
        <div class="space-y-4">
          <div class="card p-6 sticky top-24">
            <h2 class="font-serif text-lg font-semibold mb-4">Order Summary</h2>
            <div class="space-y-3 mb-4">
              <div v-for="item in cartStore.items" :key="item.id" class="flex justify-between text-sm">
                <span class="text-gray-600 line-clamp-1">{{ item.product.name }} × {{ item.quantity }}</span>
                <span class="font-medium">₱{{ (Number(item.product.sale_price || item.product.price) * item.quantity).toLocaleString() }}</span>
              </div>
            </div>

            <!-- Coupon -->
            <div class="flex gap-2 mb-4">
              <input v-model="couponCode" type="text" placeholder="Coupon code" class="input-field text-sm flex-1 py-2" />
              <UButton size="sm" variant="outline" :loading="couponLoading" @click="applyCoupon">Apply</UButton>
            </div>

            <div class="space-y-2 pt-4 border-t border-gray-100">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <span>₱{{ cartStore.subtotal.toLocaleString() }}</span>
              </div>
              <div v-if="couponDiscount > 0" class="flex justify-between text-sm text-green-600">
                <span>Discount</span>
                <span>-₱{{ couponDiscount.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Shipping</span>
                <span>₱{{ shipping.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between font-semibold text-lg pt-2 border-t border-gray-100">
                <span>Total</span>
                <span class="text-matcha-700">₱{{ total.toLocaleString() }}</span>
              </div>
            </div>

            <div v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg mt-4">{{ error }}</div>

            <UButton full size="lg" :loading="loading" class="mt-6" @click="placeOrder">
              Place Order
            </UButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
