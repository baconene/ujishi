export function useImageUrl() {
  const config = useRuntimeConfig()
  const apiBase = computed(() => (config.public.apiBase || '').replace(/\/api\/?$/, ''))

  function normalizeUrl(url?: string | null): string {
    if (!url) return ''

    // Replace localhost/127.0.0.1 URLs with the real API base
    const localhostRe = /^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?\//i
    if (localhostRe.test(url)) {
      return apiBase.value + '/' + url.replace(localhostRe, '')
    }

    // Already a proper absolute URL — pass through
    if (/^https?:\/\//i.test(url)) return url

    // Relative path starting with /
    if (url.startsWith('/')) return apiBase.value + url

    // Bare relative path (e.g. "storage/media/...")
    return apiBase.value + (url.startsWith('storage') ? '/' : '/storage/') + url
  }

  return { normalizeUrl, apiBase }
}
