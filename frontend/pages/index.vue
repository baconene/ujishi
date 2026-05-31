<script setup lang="ts">
import type { HomepageSection } from '~/types/models'

useSeoMeta({
  title: 'Premium Japanese Matcha Experience',
  description: 'Ceremonial and culinary grade matcha from Uji, Kyoto. Shop drinks, pastries, tea leaves, and accessories at Uji-Shi Matcha Cafe.',
  ogTitle: 'Uji-Shi Matcha Cafe',
  ogDescription: 'Premium Japanese matcha experience in Metro Manila.',
})

const { data: sections, pending, error } = await useFetch<HomepageSection[]>('/homepage', {
  baseURL: useRuntimeConfig().public.apiBase,
})
</script>

<template>
  <div>
    <div v-if="pending" class="flex items-center justify-center min-h-[50vh]">
      <div class="w-10 h-10 border-4 border-matcha-200 border-t-matcha-500 rounded-full animate-spin" />
    </div>

    <div v-else-if="error" class="text-center py-20">
      <p class="text-gray-500">Unable to load homepage. Please try again.</p>
    </div>

    <template v-else-if="sections">
      <HomepageSectionRenderer
        v-for="section in sections"
        :key="section.id"
        :section="section"
      />
    </template>
  </div>
</template>
