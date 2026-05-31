import { defineStore } from 'pinia'
import type { User } from '~/types/models'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = useCookie<string | null>('auth_token', {
    maxAge: 60 * 60 * 24 * 7,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
  })

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'staff')

  const { $api } = useNuxtApp()

  async function login(email: string, password: string) {
    const data = await $api<{ token: string; user: User }>('/auth/login', {
      method: 'POST',
      body: { email, password },
    })
    token.value = data.token
    user.value = data.user
    return data
  }

  async function register(name: string, email: string, password: string, passwordConfirmation: string) {
    const data = await $api<{ token: string; user: User }>('/auth/register', {
      method: 'POST',
      body: { name, email, password, password_confirmation: passwordConfirmation },
    })
    token.value = data.token
    user.value = data.user
    return data
  }

  async function logout() {
    try {
      await $api('/auth/logout', { method: 'POST' })
    } finally {
      token.value = null
      user.value = null
    }
  }

  async function fetchUser() {
    if (!token.value) return
    try {
      user.value = await $api<User>('/auth/user')
    } catch {
      token.value = null
      user.value = null
    }
  }

  return { user, token, isAuthenticated, isAdmin, login, register, logout, fetchUser }
})
