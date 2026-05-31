<script setup lang="ts">
definePageMeta({ layout: 'admin', title: 'Settings', middleware: 'admin' })
useSeoMeta({ title: 'Settings' })

const { $api } = useNuxtApp()
const config = useRuntimeConfig()
const authCookie = useCookie<string | null>('auth_token')
const authHeaders = computed(() =>
  authCookie.value ? { Authorization: `Bearer ${authCookie.value}` } : {},
)

const { data: rawSettings } = await useFetch<Record<string, string>>('/admin/settings', {
  baseURL: config.public.apiBase, headers: authHeaders,
})

const settings = ref<Record<string, string>>({})
onMounted(() => { settings.value = { ...(rawSettings.value ?? {}) } })

const saving = ref(false)
const toast = ref('')
const logoInput = ref<HTMLInputElement | null>(null)

async function save() {
  saving.value = true
  try {
    await $api('/admin/settings', {
      method: 'PUT', headers: authHeaders.value, body: { settings: settings.value },
    })
    toast.value = 'Settings saved!'
    setTimeout(() => (toast.value = ''), 3000)
  } finally {
    saving.value = false
  }
}

async function uploadLogo(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) {
    return
  }

  toast.value = 'Uploading brand logo...'
  try {
    const form = new FormData()
    form.append('file', file)

    const media = await $api('/admin/media', {
      method: 'POST', body: form,
    })

    settings.value.logo_url = media.url as string
    toast.value = 'Brand logo uploaded.'
  } catch {
    toast.value = 'Upload failed. Please try again.'
  }
}

const logoPreview = computed(() => settings.value.logo_url || '')

const groups = [
  { title: 'General', keys: ['store_name', 'store_tagline', 'store_email', 'store_phone', 'store_address'] },
  { title: 'Shipping', keys: ['free_shipping_threshold', 'default_shipping_fee'] },
  { title: 'Social Media', keys: ['facebook_url', 'instagram_url', 'tiktok_url'] },
  { title: 'SEO', keys: ['meta_title', 'meta_description'] },
]

function labelFor(key: string) {
  return key.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="font-serif text-2xl font-semibold">Settings</h1>
      <UButton :loading="saving" @click="save">Save All</UButton>
    </div>

    <Transition name="slide-down">
      <div v-if="toast" class="fixed top-20 right-6 z-50 bg-matcha-600 text-white px-5 py-3 rounded-xl shadow-lg">{{ toast }}</div>
    </Transition>

    <div class="card p-6 space-y-4">
      <h2 class="font-serif text-lg font-semibold">Brand</h2>
      <div class="grid grid-cols-3 gap-4 items-start">
        <label class="text-sm font-medium text-gray-600 col-span-1">Brand Logo</label>
        <div class="col-span-2 space-y-3">
          <div class="flex items-center gap-3">
            <button type="button" class="btn-outline" @click="logoInput?.click()">Upload Logo</button>
            <span class="text-sm text-gray-500">Upload an image or paste a URL below.</span>
          </div>
          <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="uploadLogo" />
          <input v-model="settings.logo_url" type="text" class="input-field" placeholder="Logo image URL" />
          <div v-if="logoPreview" class="rounded-xl overflow-hidden border border-gray-200 w-48">
            <NuxtImg
              :src="logoPreview"
              alt="Brand logo preview"
              class="w-full h-24 object-contain bg-white"
              width="192"
              height="96"
            />
          </div>
        </div>
      </div>
    </div>
    <div v-for="group in groups" :key="group.title" class="card p-6 space-y-4">
      <h2 class="font-serif text-lg font-semibold">{{ group.title }}</h2>
      <div
        v-for="key in group.keys"
        :key="key"
        class="grid grid-cols-3 gap-4 items-center"
      >
        <label class="text-sm font-medium text-gray-600 col-span-1">{{ labelFor(key) }}</label>
        <input v-model="settings[key]" type="text" class="input-field col-span-2" />
      </div>
    </div>
  </div>
</template>
