<script setup lang="ts">
import type { CarouselSlide } from '~/types/models'

definePageMeta({ layout: 'admin', title: 'Carousel Management', middleware: 'admin' })
useSeoMeta({ title: 'Carousel Management' })

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')
const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)
const apiBase = computed(() => (config.public.apiBase || '').replace(/\/api\/?$/, ''))

function normalizeUrl(url?: string | null) {
  if (!url) return ''
  if (/^https?:\/\//i.test(url)) return url
  if (url.startsWith('/')) return apiBase.value + url
  return apiBase.value + (url.startsWith('storage') ? '/' : '/storage/') + url.replace(/^\/+/, '')
}

const { data: slides, refresh } = await useFetch<CarouselSlide[]>('/admin/carousel', {
  baseURL: config.public.apiBase,
  headers: authHeaders,
})

const showForm = ref(false)
const editingSlide = ref<Partial<CarouselSlide>>({})
const saving = ref(false)
const toast = ref('')
const desktopFileInput = ref<HTMLInputElement | null>(null)
const mobileFileInput = ref<HTMLInputElement | null>(null)

function openNew() {
  editingSlide.value = { sort_order: (slides.value?.length ?? 0), is_active: true }
  showForm.value = true
}

function openEdit(slide: CarouselSlide) {
  editingSlide.value = { ...slide }
  showForm.value = true
}

async function uploadMediaFile(file: File) {
  const form = new FormData()
  form.append('file', file)

  const media = await $api('/admin/media', {
    method: 'POST',
    body: form,
  })

  return media.url as string
}

async function handleDesktopFile(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) {
    return
  }

  toast.value = 'Uploading desktop image...'
  try {
    editingSlide.value.desktop_image = await uploadMediaFile(file)
    toast.value = 'Desktop image uploaded.'
  } catch {
    toast.value = 'Upload failed. Please try again.'
  }
}

async function handleMobileFile(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) {
    return
  }

  toast.value = 'Uploading mobile image...'
  try {
    editingSlide.value.mobile_image = await uploadMediaFile(file)
    toast.value = 'Mobile image uploaded.'
  } catch {
    toast.value = 'Upload failed. Please try again.'
  }
}

async function saveSlide() {
  saving.value = true
  try {
    if (editingSlide.value.id) {
      await $api(`/admin/carousel/${editingSlide.value.id}`, {
        method: 'PUT', headers: authHeaders.value, body: editingSlide.value,
      })
    } else {
      await $api('/admin/carousel', {
        method: 'POST', headers: authHeaders.value, body: editingSlide.value,
      })
    }
    await refresh()
    showForm.value = false
    toast.value = 'Slide saved!'
    setTimeout(() => (toast.value = ''), 3000)
  } finally {
    saving.value = false
  }
}

async function deleteSlide(id: number) {
  if (!confirm('Delete this slide?')) return
  await $api(`/admin/carousel/${id}`, { method: 'DELETE', headers: authHeaders.value })
  await refresh()
}

async function toggleSlide(slide: CarouselSlide) {
  await $api(`/admin/carousel/${slide.id}`, {
    method: 'PUT', headers: authHeaders.value, body: { is_active: !slide.is_active },
  })
  await refresh()
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="font-serif text-2xl font-semibold">Carousel Slides</h1>
      <UButton @click="openNew">+ Add Slide</UButton>
    </div>

    <Transition name="slide-down">
      <div v-if="toast" class="fixed top-20 right-6 z-50 bg-matcha-600 text-white px-5 py-3 rounded-xl shadow-lg">{{ toast }}</div>
    </Transition>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-for="slide in slides" :key="slide.id" class="card overflow-hidden">
        <div class="aspect-video relative">
          <NuxtImg
            :src="normalizeUrl(slide.desktop_image)"
            :alt="slide.title || 'Slide'"
            class="w-full h-full object-cover"
            width="400"
            height="225"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent" />
          <div class="absolute bottom-3 left-3 right-3">
            <p class="text-white font-semibold text-sm line-clamp-1">{{ slide.title || 'Untitled slide' }}</p>
            <p class="text-white/70 text-xs line-clamp-1">{{ slide.subtitle }}</p>
          </div>
          <button
            :class="['absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center text-white text-xs', slide.is_active ? 'bg-matcha-500' : 'bg-gray-500']"
            @click="toggleSlide(slide)"
          >
            {{ slide.is_active ? '✓' : '✗' }}
          </button>
        </div>
        <div class="p-4 flex items-center justify-between">
          <div>
            <p v-if="slide.cta_text" class="text-sm text-matcha-600">CTA: {{ slide.cta_text }}</p>
            <p class="text-xs text-gray-400">Order: {{ slide.sort_order }}</p>
          </div>
          <div class="flex gap-2">
            <button class="btn-ghost text-sm px-3 py-1.5" @click="openEdit(slide)">Edit</button>
            <button class="text-red-500 hover:text-red-700 px-3 py-1.5 text-sm" @click="deleteSlide(slide.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Slide Form Modal -->
    <UModal :open="showForm" :title="editingSlide.id ? 'Edit Slide' : 'New Slide'" size="full" @close="showForm = false">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Desktop Image</label>
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <button type="button" class="btn-outline" @click="desktopFileInput?.click()">Upload Desktop Image</button>
              <span class="text-sm text-gray-500">JPEG/PNG/WebP up to 10MB</span>
            </div>
            <input ref="desktopFileInput" type="file" accept="image/*" class="hidden" @change="handleDesktopFile" />
            <input v-model="editingSlide.desktop_image" type="text" class="input-field" placeholder="Or paste image URL" required />
            <div v-if="editingSlide.desktop_image" class="mt-2 rounded-xl overflow-hidden border border-gray-200">
              <NuxtImg
                :src="normalizeUrl(editingSlide.desktop_image)"
                :alt="editingSlide.title || 'Desktop slide image'"
                class="w-full h-48 object-cover"
                width="400"
                height="225"
              />
            </div>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Mobile Image</label>
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <button type="button" class="btn-outline" @click="mobileFileInput?.click()">Upload Mobile Image</button>
              <span class="text-sm text-gray-500">Optional mobile-specific image</span>
            </div>
            <input ref="mobileFileInput" type="file" accept="image/*" class="hidden" @change="handleMobileFile" />
            <input v-model="editingSlide.mobile_image" type="text" class="input-field" placeholder="Or paste mobile image URL" />
            <div v-if="editingSlide.mobile_image" class="mt-2 rounded-xl overflow-hidden border border-gray-200">
              <NuxtImg
                :src="normalizeUrl(editingSlide.mobile_image)"
                :alt="editingSlide.title || 'Mobile slide image'"
                class="w-full h-48 object-cover"
                width="400"
                height="225"
              />
            </div>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Title</label>
          <input v-model="editingSlide.title" type="text" class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-600 mb-1">Subtitle</label>
          <input v-model="editingSlide.subtitle" type="text" class="input-field" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">CTA Button Text</label>
            <input v-model="editingSlide.cta_text" type="text" class="input-field" placeholder="Shop Now" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">CTA URL</label>
            <input v-model="editingSlide.cta_url" type="text" class="input-field" placeholder="/products" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Sort Order</label>
            <input v-model.number="editingSlide.sort_order" type="number" class="input-field" min="0" />
          </div>
          <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="editingSlide.is_active" type="checkbox" class="w-4 h-4 text-matcha-500" />
              <span class="text-sm font-medium">Active</span>
            </label>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <UButton variant="ghost" @click="showForm = false">Cancel</UButton>
          <UButton :loading="saving" @click="saveSlide">Save Slide</UButton>
        </div>
      </div>
    </UModal>
  </div>
</template>
