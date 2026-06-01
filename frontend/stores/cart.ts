import { defineStore } from 'pinia'
import type { CartItem } from '~/types/models'

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>([])
  const isOpen = ref(false)
  const loading = ref(false)

  const count = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0))
  const subtotal = computed(() =>
    items.value.reduce((sum, item) => {
      const price = Number(item.product.sale_price || item.product.price)
      return sum + price * item.quantity
    }, 0),
  )

  const { $api } = useNuxtApp()

  function getSessionId(): string {
    if (typeof window === 'undefined') return ''
    let id = localStorage.getItem('cart_session_id')
    if (!id) {
      id = crypto.randomUUID()
      localStorage.setItem('cart_session_id', id)
    }
    return id
  }

  async function fetchCart() {
    loading.value = true
    try {
      const data = await $api<{ items: CartItem[] }>('/cart', {
        headers: { 'X-Session-Id': getSessionId() },
      })
      items.value = data.items
    } finally {
      loading.value = false
    }
  }

  async function addItem(productId: number, quantity = 1) {
    loading.value = true
    try {
      await $api('/cart', {
        method: 'POST',
        headers: { 'X-Session-Id': getSessionId() },
        body: { product_id: productId, quantity },
      })
      await fetchCart()
      isOpen.value = true
    } finally {
      loading.value = false
    }
  }

  async function updateItem(itemId: number, quantity: number) {
    await $api(`/cart/${itemId}`, {
      method: 'PUT',
      headers: { 'X-Session-Id': getSessionId() },
      body: { quantity },
    })
    await fetchCart()
  }

  async function removeItem(itemId: number) {
    await $api(`/cart/${itemId}`, {
      method: 'DELETE',
      headers: { 'X-Session-Id': getSessionId() },
    })
    await fetchCart()
  }

  function openCart() { isOpen.value = true }
  function closeCart() { isOpen.value = false }

  return { items, isOpen, loading, count, subtotal, fetchCart, addItem, updateItem, removeItem, openCart, closeCart }
})
