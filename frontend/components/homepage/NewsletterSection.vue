<script setup lang="ts">
defineProps<{ settings?: Record<string, unknown> | null }>()

const email = ref('')
const name = ref('')
const loading = ref(false)
const success = ref(false)
const error = ref('')
const { $api } = useNuxtApp()

async function subscribe() {
  if (!email.value) return
  loading.value = true
  error.value = ''
  try {
    await $api('/newsletter/subscribe', { method: 'POST', body: { email: email.value, name: name.value } })
    success.value = true
    email.value = ''
    name.value = ''
  } catch (e: unknown) {
    error.value = 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="py-20 bg-matcha-800 text-cream px-4">
    <div class="max-w-2xl mx-auto text-center">
      <h2 class="font-serif text-3xl md:text-4xl font-semibold text-cream mb-4">
        {{ (settings as any)?.title ?? 'Join Our Community' }}
      </h2>
      <p class="text-matcha-200 text-lg mb-10">
        {{ (settings as any)?.subtitle ?? 'Get exclusive offers and brewing tips.' }}
      </p>

      <div v-if="success" class="bg-matcha-700 rounded-2xl p-8">
        <p class="text-cream font-semibold text-lg">You're in! Welcome to the Uji-Shi community.</p>
      </div>

      <form v-else class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto" @submit.prevent="subscribe">
        <input
          v-model="email"
          type="email"
          placeholder="your@email.com"
          class="flex-1 px-4 py-3 rounded-lg bg-matcha-700 border border-matcha-600 text-cream placeholder:text-matcha-400 focus:outline-none focus:border-matcha-400"
          required
        />
        <UButton type="submit" :loading="loading" class="bg-cream !text-matcha-800 hover:bg-cream-dark whitespace-nowrap">
          Subscribe
        </UButton>
      </form>
      <p v-if="error" class="text-red-300 text-sm mt-2">{{ error }}</p>
    </div>
  </section>
</template>
