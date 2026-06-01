export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const authCookie = useCookie<string | null>('auth_token')

  const api = $fetch.create({
    baseURL: config.public.apiBase,
    onRequest({ options }) {
      const headers = new Headers(options.headers as HeadersInit | undefined)
      headers.set('Accept', 'application/json')
      if (authCookie.value) {
        headers.set('Authorization', `Bearer ${authCookie.value}`)
      }
      options.headers = Object.fromEntries(headers.entries())
    },
    onResponseError({ response }) {
      if (response.status === 401) {
        authCookie.value = null
        navigateTo('/auth/login')
      }
    },
  })

  return { provide: { api } }
})
