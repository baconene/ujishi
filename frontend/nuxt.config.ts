// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-01-01',
  devtools: { enabled: false },

  future: {
    compatibilityVersion: 4,
  },

  modules: [
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
    '@vueuse/nuxt',
    '@nuxt/image',
  ],

  // Disable folder-name prefixing so <UButton> works (not <UiUButton>),
  // <ShopProductCard> works (not <ShopShopProductCard>), etc.
  components: [
    { path: '~/components/ui', pathPrefix: false },
    { path: '~/components/shop', pathPrefix: false },
    { path: '~/components/homepage', pathPrefix: false },
    { path: '~/components/admin', pathPrefix: false },
    '~/components',
  ],

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
      paymongoPublicKey: process.env.NUXT_PUBLIC_PAYMONGO_KEY || '',
    },
  },

  routeRules: {
    '/admin/**': { ssr: false },
  },

  nitro: {
    // Port for the Node.js server (Forge Daemon will run on this port)
    port: process.env.NITRO_PORT ? Number(process.env.NITRO_PORT) : 3000,
  },

  app: {
    head: {
      titleTemplate: '%s | Uji-Shi Matcha Cafe',
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'theme-color', content: '#5a9e3c' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap',
        },
      ],
    },
  },

  typescript: {
    strict: true,
  },

  image: {
    quality: 85,
    format: ['webp', 'jpeg'],
    screens: {
      xs: 320,
      sm: 640,
      md: 768,
      lg: 1024,
      xl: 1280,
      '2xl': 1536,
    },
  },

  css: ['~/assets/css/main.css'],
})
