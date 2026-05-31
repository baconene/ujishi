export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const authCookie = useCookie<string | null>('auth_token')

  const api = $fetch.create({
    baseURL: config.public.apiBase,
    onRequest({ options }) {
      if (authCookie.value) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${authCookie.value}`,
        }
      }
      options.headers = {
        ...options.headers,
        Accept: 'application/json',
      }
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
