<script setup lang="ts">
import type { Page } from '~/types/models'

const route = useRoute()
const slug = String(route.params.slug || '')

const { data: page, pending, error } = await useFetch<Page>(`/pages/${slug}`, {
  baseURL: useRuntimeConfig().public.apiBase,
})

const title = computed(() => page.value?.seo_title || page.value?.title || 'Page')
const description = computed(() => page.value?.seo_description || 'Learn more about Uji-Shi Matcha Cafe.')

useSeoMeta({ title, description })
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 py-16">
    <div v-if="pending" class="flex items-center justify-center min-h-[40vh]">
      <div class="w-10 h-10 border-4 border-matcha-200 border-t-matcha-500 rounded-full animate-spin" />
    </div>

    <div v-else-if="error" class="text-center py-20">
      <h1 class="font-serif text-3xl font-semibold text-charcoal mb-4">Page Not Found</h1>
      <p class="text-gray-500 mb-8">We couldn't find the page you're looking for.</p>
      <NuxtLink to="/" class="btn-primary">Return Home</NuxtLink>
    </div>

    <div v-else class="prose prose-matcha mx-auto">
      <h1 class="font-serif text-4xl font-semibold mb-6">{{ page.value?.title }}</h1>
      <div v-html="page.value?.content || ''" />
    </div>
  </div>
</template>
