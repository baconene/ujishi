<script setup lang="ts">
definePageMeta({ layout: 'auth' })
useSeoMeta({ title: 'Login' })

const authStore = useAuthStore()
const route = useRoute()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function login() {
  if (!email.value || !password.value) return
  loading.value = true
  error.value = ''
  try {
    await authStore.login(email.value, password.value)
    const redirect = String(route.query.redirect || '/')
    await navigateTo(redirect)
  } catch (e: unknown) {
    error.value = (e as any)?.data?.message ?? 'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="font-serif text-2xl font-semibold text-center mb-6">Welcome Back</h1>
    <form class="space-y-4" @submit.prevent="login">
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
        <input v-model="email" type="email" class="input-field" placeholder="your@email.com" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
        <input v-model="password" type="password" class="input-field" placeholder="••••••••" required />
      </div>
      <div v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">{{ error }}</div>
      <UButton type="submit" full :loading="loading">Sign In</UButton>
    </form>
    <p class="text-center text-sm text-gray-500 mt-6">
      Don't have an account?
      <NuxtLink to="/auth/register" class="text-matcha-600 font-medium hover:underline">Register</NuxtLink>
    </p>
  </div>
</template>
