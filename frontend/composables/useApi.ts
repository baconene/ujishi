export function useApi() {
  const { $api } = useNuxtApp()
  return $api as typeof $fetch
}

export function useAuthFetch<T>(url: string, options?: Parameters<typeof $fetch>[1]) {
  const config = useRuntimeConfig()
  const authCookie = useCookie<string | null>('auth_token')

  return useFetch<T>(url, {
    baseURL: config.public.apiBase,
    headers: authCookie.value
      ? { Authorization: `Bearer ${authCookie.value}`, Accept: 'application/json' }
      : { Accept: 'application/json' },
    ...options,
  })
}
