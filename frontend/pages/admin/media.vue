<script setup lang="ts">
import type { MediaFile, PaginatedResponse } from '~/types/models'

definePageMeta({ layout: 'admin', title: 'Media Library', middleware: 'admin' })
useSeoMeta({ title: 'Media Library' })

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')
const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)
const { normalizeUrl } = useImageUrl()

const page = ref(1)
const uploading = ref(false)
const toast = ref('')
const toastType = ref<'success' | 'error'>('success')
const fileInput = ref<HTMLInputElement | null>(null)
const dropzone = ref<HTMLDivElement | null>(null)
const isDragging = ref(false)
const copied = ref<number | null>(null)

const { data, refresh } = await useFetch<PaginatedResponse<MediaFile>>('/admin/media', {
  baseURL: config.public.apiBase,
  headers: authHeaders,
  query: computed(() => ({ page: page.value })),
  watch: [page],
})

function showToast(msg: string, type: 'success' | 'error' = 'success') {
  toast.value = msg
  toastType.value = type
  setTimeout(() => (toast.value = ''), 3000)
}

async function uploadFiles(files: FileList | File[]) {
  const list = Array.from(files)
  if (!list.length) return
  uploading.value = true
  let uploaded = 0
  let failed = 0
  for (const file of list) {
    try {
      const fd = new FormData()
      fd.append('file', file)
      await $api('/admin/media', { method: 'POST', headers: authHeaders.value, body: fd })
      uploaded++
    } catch {
      failed++
    }
  }
  uploading.value = false
  if (failed > 0) showToast(`${uploaded} uploaded, ${failed} failed.`, 'error')
  else showToast(`${uploaded} file${uploaded > 1 ? 's' : ''} uploaded.`)
  page.value = 1
  await refresh()
}

function onFileChange(e: Event) {
  const files = (e.target as HTMLInputElement).files
  if (files) uploadFiles(files)
  if (fileInput.value) fileInput.value.value = ''
}

function onDrop(e: DragEvent) {
  isDragging.value = false
  if (e.dataTransfer?.files) uploadFiles(e.dataTransfer.files)
}

async function copyUrl(file: MediaFile) {
  const url = normalizeUrl(file.url)
  await navigator.clipboard.writeText(url)
  copied.value = file.id
  setTimeout(() => (copied.value = null), 2000)
}

async function deleteFile(file: MediaFile) {
  if (!confirm(`Delete "${file.filename}"? This cannot be undone.`)) return
  await $api(`/admin/media/${file.id}`, { method: 'DELETE', headers: authHeaders.value })
  showToast('File deleted.')
  await refresh()
}

function formatSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="font-serif text-2xl font-semibold">Media Library</h1>
        <p class="text-gray-500 text-sm mt-1">{{ data?.total ?? 0 }} files uploaded</p>
      </div>
      <UButton @click="fileInput?.click()">Upload Images</UButton>
    </div>

    <!-- Toast -->
    <Transition name="slide-down">
      <div
        v-if="toast"
        :class="['fixed top-20 right-6 z-50 px-5 py-3 rounded-xl shadow-lg text-white', toastType === 'error' ? 'bg-red-500' : 'bg-matcha-600']"
      >
        {{ toast }}
      </div>
    </Transition>

    <!-- Hidden file input -->
    <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" @change="onFileChange" />

    <!-- Drop Zone -->
    <div
      ref="dropzone"
      :class="[
        'border-2 border-dashed rounded-2xl p-10 text-center transition-all cursor-pointer',
        isDragging ? 'border-matcha-500 bg-matcha-50' : 'border-gray-200 hover:border-matcha-400 hover:bg-matcha-50/50',
        uploading && 'pointer-events-none opacity-60',
      ]"
      @click="fileInput?.click()"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
    >
      <div v-if="uploading" class="flex flex-col items-center gap-3">
        <div class="w-8 h-8 border-4 border-matcha-200 border-t-matcha-500 rounded-full animate-spin" />
        <p class="text-matcha-600 font-medium">Uploading...</p>
      </div>
      <div v-else class="flex flex-col items-center gap-2 text-gray-400">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="font-medium text-gray-500">Drop images here or click to browse</p>
        <p class="text-sm">JPEG, PNG, WebP up to 50MB each · Multiple files supported</p>
      </div>
    </div>

    <!-- Image Grid -->
    <div v-if="data?.data?.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
      <div
        v-for="file in data.data"
        :key="file.id"
        class="group card overflow-hidden"
      >
        <!-- Thumbnail -->
        <div class="aspect-square bg-matcha-50 overflow-hidden">
          <img
            :src="normalizeUrl(file.url)"
            :alt="file.alt || file.filename"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            loading="lazy"
          />
        </div>

        <!-- Info + Actions -->
        <div class="p-2 space-y-1.5">
          <p class="text-xs text-gray-500 truncate" :title="file.filename">{{ file.filename }}</p>
          <p class="text-xs text-gray-400">{{ formatSize(file.size) }} · {{ formatDate(file.created_at) }}</p>
          <p v-if="file.width" class="text-xs text-gray-400">{{ file.width }}×{{ file.height }}px</p>

          <!-- Copy URL button -->
          <button
            :class="[
              'w-full text-xs px-2 py-1.5 rounded-lg font-medium transition-all',
              copied === file.id
                ? 'bg-matcha-500 text-white'
                : 'bg-matcha-50 text-matcha-700 hover:bg-matcha-100',
            ]"
            @click="copyUrl(file)"
          >
            {{ copied === file.id ? '✓ Copied!' : 'Copy URL' }}
          </button>

          <!-- URL display (truncated, selectable) -->
          <input
            :value="normalizeUrl(file.url)"
            readonly
            class="w-full text-xs text-gray-400 bg-gray-50 rounded px-1.5 py-1 border border-gray-100 truncate cursor-text"
            @click="($event.target as HTMLInputElement).select()"
          />

          <!-- Delete -->
          <button
            class="w-full text-xs text-red-400 hover:text-red-600 hover:bg-red-50 py-1 rounded transition-colors"
            @click="deleteFile(file)"
          >
            Delete
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="!uploading" class="text-center py-16 text-gray-400">
      <p class="text-lg">No files uploaded yet.</p>
      <p class="text-sm mt-1">Upload your first image above.</p>
    </div>

    <!-- Pagination -->
    <UPagination
      v-if="data && data.last_page > 1"
      :current-page="data.current_page"
      :last-page="data.last_page"
      :total="data.total"
      @change="page = $event"
    />
  </div>
</template>
