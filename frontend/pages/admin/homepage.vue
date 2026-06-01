<script setup lang="ts">
import type { CarouselSlide, HomepageSection } from '~/types/models'

definePageMeta({ layout: 'admin', title: 'Homepage Builder', middleware: 'admin' })
useSeoMeta({ title: 'Homepage Builder' })

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')

const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)

const { data: sections, refresh } = await useFetch<HomepageSection[]>('/admin/homepage/sections', {
  baseURL: config.public.apiBase,
  headers: authHeaders,
})

const dragging = ref<HomepageSection | null>(null)
const saving = ref(false)
const editingSection = ref<HomepageSection | null>(null)
const sectionSettings = ref<Record<string, unknown>>({})
const showEditor = ref(false)
const toast = ref('')

// Carousel state
type LocalSlide = CarouselSlide & { _isNew?: boolean }
const carouselSlides = ref<LocalSlide[]>([])
const slideFileInput = ref<HTMLInputElement | null>(null)
const pendingSlideUpload = ref<{ slide: LocalSlide; field: 'desktop_image' | 'mobile_image' } | null>(null)
const { normalizeUrl } = useImageUrl()

async function loadSlides() {
  const data = await $api('/admin/carousel', { headers: authHeaders.value }) as CarouselSlide[]
  carouselSlides.value = data
}

function addSlide() {
  carouselSlides.value.push({
    id: 0,
    desktop_image: '',
    mobile_image: '',
    title: '',
    subtitle: '',
    cta_text: '',
    cta_url: '',
    sort_order: carouselSlides.value.length,
    is_active: true,
    _isNew: true,
  })
}

async function saveSlide(slide: LocalSlide) {
  if (!slide.desktop_image) {
    toast.value = 'Desktop image is required.'
    setTimeout(() => (toast.value = ''), 3000)
    return
  }
  saving.value = true
  try {
    const body = {
      desktop_image: slide.desktop_image,
      mobile_image: slide.mobile_image || null,
      title: slide.title || null,
      subtitle: slide.subtitle || null,
      cta_text: slide.cta_text || null,
      cta_url: slide.cta_url || null,
      sort_order: slide.sort_order,
      is_active: slide.is_active,
    }
    if (slide._isNew) {
      const created = await $api('/admin/carousel', { method: 'POST', headers: authHeaders.value, body }) as CarouselSlide
      slide.id = created.id
      delete slide._isNew
    } else {
      await $api(`/admin/carousel/${slide.id}`, { method: 'PUT', headers: authHeaders.value, body })
    }
    toast.value = 'Slide saved!'
  } catch {
    toast.value = 'Failed to save slide.'
  } finally {
    saving.value = false
    setTimeout(() => (toast.value = ''), 3000)
  }
}

async function deleteSlide(slide: LocalSlide, index: number) {
  if (!confirm('Delete this slide?')) return
  if (!slide._isNew) {
    await $api(`/admin/carousel/${slide.id}`, { method: 'DELETE', headers: authHeaders.value })
  }
  carouselSlides.value.splice(index, 1)
  toast.value = 'Slide deleted.'
  setTimeout(() => (toast.value = ''), 3000)
}

function triggerSlideUpload(slide: LocalSlide, field: 'desktop_image' | 'mobile_image') {
  pendingSlideUpload.value = { slide, field }
  slideFileInput.value?.click()
}

async function handleSlideImageUpload(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  const pending = pendingSlideUpload.value
  if (!file || !pending) return
  toast.value = 'Uploading...'
  try {
    const fd = new FormData()
    fd.append('file', file)
    const media = await $api('/admin/media', { method: 'POST', headers: authHeaders.value, body: fd }) as { url: string }
    pending.slide[pending.field] = media.url
    toast.value = 'Image uploaded!'
  } catch {
    toast.value = 'Upload failed.'
  } finally {
    pendingSlideUpload.value = null
    if (slideFileInput.value) slideFileInput.value.value = ''
    setTimeout(() => (toast.value = ''), 3000)
  }
}

const sectionTypeLabels: Record<string, string> = {
  hero_carousel: '🎠 Hero Carousel',
  featured_products: '⭐ Featured Products',
  best_sellers: '🔥 Best Sellers',
  categories: '🏷 Categories',
  promotional_banner: '📢 Promotional Banner',
  about: '📖 About Section',
  reviews: '💬 Reviews',
  newsletter: '📧 Newsletter',
}

async function toggleSection(section: HomepageSection) {
  await $api(`/admin/homepage/sections/${section.id}`, {
    method: 'PUT',
    headers: authHeaders.value,
    body: { is_active: !section.is_active },
  })
  await refresh()
}

async function editSection(section: HomepageSection) {
  editingSection.value = section
  sectionSettings.value = { ...(section.settings ?? {}) }
  showEditor.value = true
  if (section.type === 'hero_carousel') {
    await loadSlides()
  }
}

async function saveSection() {
  if (!editingSection.value) return
  saving.value = true
  try {
    await $api(`/admin/homepage/sections/${editingSection.value.id}`, {
      method: 'PUT',
      headers: authHeaders.value,
      body: { settings: sectionSettings.value },
    })
    await refresh()
    showEditor.value = false
    toast.value = 'Section saved!'
    setTimeout(() => (toast.value = ''), 3000)
  } finally {
    saving.value = false
  }
}

async function saveOrder() {
  saving.value = true
  try {
    await $api('/admin/homepage/sections/reorder', {
      method: 'POST',
      headers: authHeaders.value,
      body: {
        sections: sections.value!.map((s, i) => ({ id: s.id, sort_order: i })),
      },
    })
    toast.value = 'Order saved!'
    setTimeout(() => (toast.value = ''), 3000)
  } finally {
    saving.value = false
  }
}

function onDragStart(section: HomepageSection) {
  dragging.value = section
}

function onDragOver(e: DragEvent, targetSection: HomepageSection) {
  e.preventDefault()
  if (!dragging.value || dragging.value.id === targetSection.id) return
  const list = sections.value!
  const fromIndex = list.findIndex(s => s.id === dragging.value!.id)
  const toIndex = list.findIndex(s => s.id === targetSection.id)
  if (fromIndex !== toIndex) {
    const item = list.splice(fromIndex, 1)[0]
    list.splice(toIndex, 0, item)
  }
}

function onDragEnd() {
  dragging.value = null
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="font-serif text-2xl font-semibold">Homepage Builder</h1>
        <p class="text-gray-500 text-sm mt-1">Drag sections to reorder. Toggle to show/hide. Click edit to customize.</p>
      </div>
      <div class="flex gap-3">
        <NuxtLink to="/" target="_blank" class="btn-outline text-sm px-4 py-2">Preview Site</NuxtLink>
        <UButton :loading="saving" @click="saveOrder">Save Order</UButton>
      </div>
    </div>

    <!-- Toast -->
    <Transition name="slide-down">
      <div v-if="toast" class="fixed top-20 right-6 z-50 bg-matcha-600 text-white px-5 py-3 rounded-xl shadow-lg">
        {{ toast }}
      </div>
    </Transition>

    <!-- Section List -->
    <div class="space-y-3">
      <div
        v-for="section in sections"
        :key="section.id"
        draggable="true"
        :class="[
          'card p-5 flex items-center gap-4 cursor-grab active:cursor-grabbing select-none transition-all',
          !section.is_active && 'opacity-60',
          dragging?.id === section.id && 'opacity-30 scale-95',
        ]"
        @dragstart="onDragStart(section)"
        @dragover="onDragOver($event, section)"
        @dragend="onDragEnd"
      >
        <div class="text-gray-300 flex flex-col gap-0.5">
          <div class="w-5 h-0.5 bg-current rounded" />
          <div class="w-5 h-0.5 bg-current rounded" />
          <div class="w-5 h-0.5 bg-current rounded" />
        </div>

        <div class="flex-1">
          <p class="font-medium text-charcoal">{{ sectionTypeLabels[section.type] ?? section.type }}</p>
          <p class="text-sm text-gray-400">{{ section.name }}</p>
        </div>

        <button
          :class="[
            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
            section.is_active ? 'bg-matcha-500' : 'bg-gray-200',
          ]"
          @click="toggleSection(section)"
        >
          <span
            :class="[
              'inline-block h-4 w-4 rounded-full bg-white shadow transition-transform',
              section.is_active ? 'translate-x-6' : 'translate-x-1',
            ]"
          />
        </button>

        <button class="btn-ghost text-sm px-3 py-2" @click="editSection(section)">
          Edit
        </button>
      </div>
    </div>

    <!-- Hidden file input for slide image upload -->
    <input ref="slideFileInput" type="file" accept="image/*" class="hidden" @change="handleSlideImageUpload" />

    <!-- Section Editor Modal -->
    <UModal :open="showEditor" :title="`Edit: ${sectionTypeLabels[editingSection?.type ?? '']}`" size="lg" @close="showEditor = false">
      <div v-if="editingSection" class="space-y-4">

        <!-- Hero Carousel Editor -->
        <template v-if="editingSection.type === 'hero_carousel'">
          <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
            <div
              v-for="(slide, index) in carouselSlides"
              :key="slide.id || index"
              class="border border-gray-200 rounded-xl p-4 space-y-3"
            >
              <!-- Image Preview -->
              <div class="aspect-video rounded-lg overflow-hidden bg-matcha-50">
                <img
                  v-if="slide.desktop_image"
                  :src="normalizeUrl(slide.desktop_image)"
                  :alt="slide.title || 'Slide'"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-sm">
                  No image — upload one below
                </div>
              </div>

              <!-- Image Uploads -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Desktop Image *</label>
                  <div class="flex gap-2">
                    <input v-model="slide.desktop_image" type="text" class="input-field text-xs flex-1" placeholder="https://..." />
                    <button type="button" class="btn-outline text-xs px-2 flex-shrink-0" @click="triggerSlideUpload(slide, 'desktop_image')">Upload</button>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Mobile Image <span class="text-gray-400">(optional)</span></label>
                  <div class="flex gap-2">
                    <input v-model="slide.mobile_image" type="text" class="input-field text-xs flex-1" placeholder="https://..." />
                    <button type="button" class="btn-outline text-xs px-2 flex-shrink-0" @click="triggerSlideUpload(slide, 'mobile_image')">Upload</button>
                  </div>
                </div>
              </div>

              <!-- Text Fields -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                  <input v-model="slide.title" type="text" class="input-field text-sm" placeholder="Slide headline" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Subtitle</label>
                  <input v-model="slide.subtitle" type="text" class="input-field text-sm" placeholder="Supporting text" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">CTA Button Text</label>
                  <input v-model="slide.cta_text" type="text" class="input-field text-sm" placeholder="Shop Now" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">CTA URL</label>
                  <input v-model="slide.cta_url" type="text" class="input-field text-sm" placeholder="/products" />
                </div>
              </div>

              <!-- Slide Actions -->
              <div class="flex items-center justify-between pt-1 border-t border-gray-100">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="slide.is_active" type="checkbox" class="w-4 h-4 text-matcha-500" />
                  <span class="text-sm text-gray-600">Active</span>
                </label>
                <div class="flex gap-2">
                  <UButton size="sm" :loading="saving" @click="saveSlide(slide)">Save Slide</UButton>
                  <button type="button" class="text-red-400 hover:text-red-600 text-sm px-2" @click="deleteSlide(slide, index)">Delete</button>
                </div>
              </div>
            </div>

            <button type="button" class="btn-outline w-full" @click="addSlide">
              + Add Slide
            </button>
          </div>

          <div class="flex justify-end pt-2 border-t">
            <UButton variant="ghost" @click="showEditor = false">Close</UButton>
          </div>
        </template>

        <!-- About Section Editor -->
        <template v-else-if="editingSection.type === 'about'">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Title</label>
            <input v-model="(sectionSettings as any).title" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Body Text</label>
            <textarea v-model="(sectionSettings as any).body" class="input-field" rows="4" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Image URL</label>
            <input v-model="(sectionSettings as any).image" type="text" class="input-field" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">CTA Text</label>
              <input v-model="(sectionSettings as any).cta_text" type="text" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-600 mb-1">CTA URL</label>
              <input v-model="(sectionSettings as any).cta_url" type="text" class="input-field" />
            </div>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <UButton variant="ghost" @click="showEditor = false">Cancel</UButton>
            <UButton :loading="saving" @click="saveSection">Save Changes</UButton>
          </div>
        </template>

        <!-- Newsletter Editor -->
        <template v-else-if="editingSection.type === 'newsletter'">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Title</label>
            <input v-model="(sectionSettings as any).title" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Subtitle</label>
            <input v-model="(sectionSettings as any).subtitle" type="text" class="input-field" />
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <UButton variant="ghost" @click="showEditor = false">Cancel</UButton>
            <UButton :loading="saving" @click="saveSection">Save Changes</UButton>
          </div>
        </template>

        <!-- Generic Section Title/Subtitle -->
        <template v-else>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Section Title</label>
            <input v-model="(sectionSettings as any).title" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Subtitle</label>
            <input v-model="(sectionSettings as any).subtitle" type="text" class="input-field" />
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <UButton variant="ghost" @click="showEditor = false">Cancel</UButton>
            <UButton :loading="saving" @click="saveSection">Save Changes</UButton>
          </div>
        </template>

      </div>
    </UModal>
  </div>
</template>
