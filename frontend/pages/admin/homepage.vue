<script setup lang="ts">
import type { HomepageSection } from '~/types/models'

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

function editSection(section: HomepageSection) {
  editingSection.value = section
  sectionSettings.value = { ...(section.settings ?? {}) }
  showEditor.value = true
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

// Drag & drop helpers
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
        <!-- Drag Handle -->
        <div class="text-gray-300 flex flex-col gap-0.5">
          <div class="w-5 h-0.5 bg-current rounded" />
          <div class="w-5 h-0.5 bg-current rounded" />
          <div class="w-5 h-0.5 bg-current rounded" />
        </div>

        <!-- Label -->
        <div class="flex-1">
          <p class="font-medium text-charcoal">{{ sectionTypeLabels[section.type] ?? section.type }}</p>
          <p class="text-sm text-gray-400">{{ section.name }}</p>
        </div>

        <!-- Active Toggle -->
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

        <!-- Edit Button -->
        <button
          class="btn-ghost text-sm px-3 py-2"
          @click="editSection(section)"
        >
          Edit
        </button>
      </div>
    </div>

    <!-- Section Editor Modal -->
    <UModal :open="showEditor" :title="`Edit: ${sectionTypeLabels[editingSection?.type ?? '']}`" size="lg" @close="showEditor = false">
      <div v-if="editingSection" class="space-y-4">
        <!-- About Section Editor -->
        <template v-if="editingSection.type === 'about'">
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
        </template>

        <div class="flex justify-end gap-3 pt-4">
          <UButton variant="ghost" @click="showEditor = false">Cancel</UButton>
          <UButton :loading="saving" @click="saveSection">Save Changes</UButton>
        </div>
      </div>
    </UModal>
  </div>
</template>
