<script setup lang="ts">
definePageMeta({ layout: 'auth' })
useSeoMeta({ title: 'Create Account' })

const authStore = useAuthStore()
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const error = ref('')

async function register() {
  loading.value = true
  error.value = ''
  try {
    await authStore.register(name.value, email.value, password.value, passwordConfirmation.value)
    await navigateTo('/')
  } catch (e: unknown) {
    error.value = (e as any)?.data?.message ?? 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="font-serif text-2xl font-semibold text-center mb-6">Create Account</h1>
    <form class="space-y-4" @submit.prevent="register">
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Full Name</label>
        <input v-model="name" type="text" class="input-field" placeholder="Juan dela Cruz" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
        <input v-model="email" type="email" class="input-field" placeholder="your@email.com" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
        <input v-model="password" type="password" class="input-field" placeholder="Min. 8 characters" required minlength="8" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Confirm Password</label>
        <input v-model="passwordConfirmation" type="password" class="input-field" placeholder="Re-enter password" required />
      </div>
      <div v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">{{ error }}</div>
      <UButton type="submit" full :loading="loading">Create Account</UButton>
    </form>
    <p class="text-center text-sm text-gray-500 mt-6">
      Already have an account?
      <NuxtLink to="/auth/login" class="text-matcha-600 font-medium hover:underline">Sign in</NuxtLink>
    </p>
  </div>
</template>
