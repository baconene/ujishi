<script setup lang="ts">
import type { Category, ContentBlock, Product } from '~/types/models'

definePageMeta({ layout: 'admin', title: 'Edit Product', middleware: 'admin' })

const route = useRoute()
const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')
const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)

const isNew = computed(() => route.params.id === 'new')
const toast = ref('')
const saving = ref(false)

const { data: categories } = await useFetch<Category[]>('/admin/categories', {
  baseURL: config.public.apiBase, headers: authHeaders,
})

const form = ref<Partial<Product>>({
  name: '', sku: '', price: 0, sale_price: undefined, description: '',
  thumbnail: '', stock: 0, is_featured: false, is_best_seller: false,
  is_active: true, category_id: undefined,
  seo_title: '', seo_description: '', seo_keywords: '', weight: undefined,
})
const contentBlocks = ref<ContentBlock[]>([])
const productImages = ref<{ url: string; alt: string }[]>([])

if (!isNew.value) {
  const { data: productData } = await useFetch<Product>(`/admin/products/${route.params.id}`, {
    baseURL: config.public.apiBase, headers: authHeaders,
  })
  if (productData.value) {
    const p = productData.value
    form.value = {
      name: p.name, sku: p.sku, price: p.price, sale_price: p.sale_price,
      description: p.description, thumbnail: p.thumbnail, stock: p.stock,
      is_featured: p.is_featured, is_best_seller: p.is_best_seller,
      is_active: p.is_active, category_id: p.category_id,
      seo_title: p.seo_title, seo_description: p.seo_description,
      seo_keywords: p.seo_keywords, weight: p.weight,
    }
    contentBlocks.value = p.content_blocks ?? []
    productImages.value = (p.images ?? []).map(img => ({ url: img.url, alt: img.alt ?? '' }))
  }
}

async function save() {
  saving.value = true
  try {
    const payload = { ...form.value, images: productImages.value, content_blocks: contentBlocks.value }
    if (isNew.value) {
      await $api('/admin/products', { method: 'POST', headers: authHeaders.value, body: payload })
      toast.value = 'Product created!'
      await navigateTo('/admin/products')
    } else {
      await $api(`/admin/products/${route.params.id}`, {
        method: 'PUT', headers: authHeaders.value, body: payload,
      })
      toast.value = 'Product saved!'
    }
  } catch (e: any) {
    toast.value = `Error: ${e?.data?.message ?? 'Failed to save'}`
  } finally {
    saving.value = false
    setTimeout(() => (toast.value = ''), 3000)
  }
}

const blockTypes = [
  { value: 'rich_text', label: '📝 Rich Text' },
  { value: 'ingredients', label: '🌿 Ingredients' },
  { value: 'faq', label: '❓ FAQ' },
  { value: 'feature_cards', label: '✨ Feature Cards' },
  { value: 'promotional_banner', label: '📢 Promotional Banner' },
]

function addBlock(type: string) {
  const defaults: Record<string, unknown> = {
    rich_text: { html: '<p>Enter text here...</p>' },
    ingredients: { items: [{ name: 'Ingredient', amount: '1 tbsp' }] },
    faq: { items: [{ q: 'Question?', a: 'Answer.' }] },
    feature_cards: { items: [{ icon: '🍵', title: 'Feature', body: 'Description' }] },
    promotional_banner: { title: 'Promo', cta_text: 'Shop Now', cta_url: '/products' },
  }
  contentBlocks.value.push({
    type: type as ContentBlock['type'],
    content: defaults[type] as Record<string, unknown>,
    sort_order: contentBlocks.value.length,
  })
}

function removeBlock(index: number) {
  contentBlocks.value.splice(index, 1)
}

function moveBlock(index: number, direction: 'up' | 'down') {
  const target = direction === 'up' ? index - 1 : index + 1
  if (target < 0 || target >= contentBlocks.value.length) return
  const temp = contentBlocks.value[index]
  contentBlocks.value[index] = contentBlocks.value[target]
  contentBlocks.value[target] = temp
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <NuxtLink to="/admin/products" class="btn-ghost text-sm px-3 py-2">&larr; Products</NuxtLink>
        <h1 class="font-serif text-2xl font-semibold">{{ isNew ? 'New Product' : 'Edit Product' }}</h1>
      </div>
      <UButton :loading="saving" @click="save">Save Product</UButton>
    </div>

    <Transition name="slide-down">
      <div v-if="toast" class="fixed top-20 right-6 z-50 bg-matcha-600 text-white px-5 py-3 rounded-xl shadow-lg">{{ toast }}</div>
    </Transition>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Form -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Basic Info -->
        <div class="card p-6 space-y-4">
          <h2 class="font-serif text-lg font-semibold">Basic Information</h2>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Product Name *</label>
            <input v-model="form.name" type="text" class="input-field" required />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">SKU *</label>
              <input v-model="form.sku" type="text" class="input-field font-mono" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Category</label>
              <select v-model="form.category_id" class="input-field">
                <option value="">No Category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Description</label>
            <textarea v-model="form.description" class="input-field" rows="4" />
          </div>
        </div>

        <!-- Pricing & Inventory -->
        <div class="card p-6 space-y-4">
          <h2 class="font-serif text-lg font-semibold">Pricing & Inventory</h2>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Price (₱) *</label>
              <input v-model.number="form.price" type="number" min="0" step="0.01" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Sale Price (₱)</label>
              <input v-model.number="form.sale_price" type="number" min="0" step="0.01" class="input-field" placeholder="Optional" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">Stock</label>
              <input v-model.number="form.stock" type="number" min="0" class="input-field" />
            </div>
          </div>
        </div>

        <!-- Content Blocks -->
        <div class="card p-6 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="font-serif text-lg font-semibold">Content Blocks</h2>
          </div>

          <div class="flex flex-wrap gap-2">
            <button
              v-for="blockType in blockTypes"
              :key="blockType.value"
              class="px-3 py-1.5 text-sm bg-matcha-50 text-matcha-700 rounded-lg hover:bg-matcha-100 transition-colors"
              @click="addBlock(blockType.value)"
            >
              {{ blockType.label }}
            </button>
          </div>

          <div class="space-y-4">
            <div
              v-for="(block, index) in contentBlocks"
              :key="index"
              class="border border-gray-200 rounded-xl p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-600">{{ blockTypes.find(b => b.value === block.type)?.label ?? block.type }}</span>
                <div class="flex gap-1">
                  <button class="p-1 hover:bg-gray-100 rounded" :disabled="index === 0" @click="moveBlock(index, 'up')">↑</button>
                  <button class="p-1 hover:bg-gray-100 rounded" :disabled="index === contentBlocks.length - 1" @click="moveBlock(index, 'down')">↓</button>
                  <button class="p-1 hover:bg-red-50 text-red-500 rounded" @click="removeBlock(index)">✕</button>
                </div>
              </div>

              <!-- Rich Text Block -->
              <div v-if="block.type === 'rich_text'">
                <textarea
                  v-model="(block.content as any).html"
                  class="input-field text-sm font-mono"
                  rows="5"
                  placeholder="<p>Content HTML...</p>"
                />
              </div>

              <!-- Ingredients Block -->
              <div v-else-if="block.type === 'ingredients'" class="space-y-2">
                <div
                  v-for="(item, i) in (block.content as any).items"
                  :key="i"
                  class="flex gap-2"
                >
                  <input v-model="item.name" type="text" class="input-field text-sm flex-1" placeholder="Ingredient name" />
                  <input v-model="item.amount" type="text" class="input-field text-sm w-28" placeholder="Amount" />
                  <button class="text-red-400 hover:text-red-600" @click="(block.content as any).items.splice(i, 1)">✕</button>
                </div>
                <button
                  class="text-sm text-matcha-600 hover:underline"
                  @click="(block.content as any).items.push({ name: '', amount: '' })"
                >
                  + Add Ingredient
                </button>
              </div>

              <!-- FAQ Block -->
              <div v-else-if="block.type === 'faq'" class="space-y-3">
                <div
                  v-for="(item, i) in (block.content as any).items"
                  :key="i"
                  class="space-y-1"
                >
                  <input v-model="item.q" type="text" class="input-field text-sm" placeholder="Question" />
                  <textarea v-model="item.a" class="input-field text-sm" rows="2" placeholder="Answer" />
                  <button class="text-xs text-red-400 hover:underline" @click="(block.content as any).items.splice(i, 1)">Remove</button>
                </div>
                <button
                  class="text-sm text-matcha-600 hover:underline"
                  @click="(block.content as any).items.push({ q: '', a: '' })"
                >
                  + Add FAQ
                </button>
              </div>

              <!-- Other blocks: raw JSON editor -->
              <div v-else>
                <textarea
                  :value="JSON.stringify(block.content, null, 2)"
                  class="input-field text-xs font-mono"
                  rows="5"
                  @input="block.content = JSON.parse(($event.target as HTMLTextAreaElement).value)"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- SEO -->
        <div class="card p-6 space-y-4">
          <h2 class="font-serif text-lg font-semibold">SEO</h2>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">SEO Title</label>
            <input v-model="form.seo_title" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Meta Description</label>
            <textarea v-model="form.seo_description" class="input-field" rows="2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Keywords</label>
            <input v-model="form.seo_keywords" type="text" class="input-field" placeholder="matcha, latte, ceremonial, ..." />
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status -->
        <div class="card p-5 space-y-4">
          <h2 class="font-serif text-lg font-semibold">Status</h2>
          <label class="flex items-center gap-3 cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="w-4 h-4 text-matcha-500" />
            <span class="text-sm font-medium">Active (visible on store)</span>
          </label>
          <label class="flex items-center gap-3 cursor-pointer">
            <input v-model="form.is_featured" type="checkbox" class="w-4 h-4 text-matcha-500" />
            <span class="text-sm font-medium">Featured Product</span>
          </label>
          <label class="flex items-center gap-3 cursor-pointer">
            <input v-model="form.is_best_seller" type="checkbox" class="w-4 h-4 text-matcha-500" />
            <span class="text-sm font-medium">Best Seller</span>
          </label>
        </div>

        <!-- Thumbnail -->
        <div class="card p-5 space-y-3">
          <h2 class="font-serif text-lg font-semibold">Thumbnail</h2>
          <div v-if="form.thumbnail" class="aspect-square rounded-xl overflow-hidden bg-matcha-50">
            <NuxtImg :src="form.thumbnail" alt="Thumbnail" class="w-full h-full object-cover" width="300" height="300" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Image URL</label>
            <input v-model="form.thumbnail" type="text" class="input-field text-sm" placeholder="https://..." />
          </div>
        </div>

        <!-- Gallery Images -->
        <div class="card p-5 space-y-3">
          <h2 class="font-serif text-lg font-semibold">Gallery</h2>
          <div v-for="(img, i) in productImages" :key="i" class="flex gap-2">
            <input v-model="img.url" type="text" class="input-field text-sm flex-1" placeholder="Image URL" />
            <button class="text-red-400 hover:text-red-600 flex-shrink-0" @click="productImages.splice(i, 1)">✕</button>
          </div>
          <button
            class="text-sm text-matcha-600 hover:underline"
            @click="productImages.push({ url: '', alt: form.name || '' })"
          >
            + Add Image
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
