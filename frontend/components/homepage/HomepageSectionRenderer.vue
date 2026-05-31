<script setup lang="ts">
import type { HomepageSection } from '~/types/models'

defineProps<{ section: HomepageSection }>()
</script>

<template>
  <div>
    <template v-if="section.type === 'hero_carousel' && Array.isArray(section.data) && section.data.length">
      <HomepageHeroCarousel :slides="section.data" />
    </template>

    <template v-else-if="section.type === 'featured_products' && Array.isArray(section.data)">
      <section class="py-16 px-4 max-w-7xl mx-auto">
        <h2 class="section-title">{{ (section.settings as any)?.title ?? 'Featured Products' }}</h2>
        <p class="section-subtitle">{{ (section.settings as any)?.subtitle }}</p>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
          <ShopProductCard v-for="product in section.data" :key="product.id" :product="product" />
        </div>
        <div class="text-center mt-10">
          <NuxtLink to="/products?featured=true" class="btn-outline">View All Products</NuxtLink>
        </div>
      </section>
    </template>

    <template v-else-if="section.type === 'best_sellers' && Array.isArray(section.data)">
      <section class="py-16 bg-matcha-50 px-4">
        <div class="max-w-7xl mx-auto">
          <h2 class="section-title">{{ (section.settings as any)?.title ?? 'Best Sellers' }}</h2>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <ShopProductCard v-for="product in section.data" :key="product.id" :product="product" />
          </div>
        </div>
      </section>
    </template>

    <template v-else-if="section.type === 'categories' && Array.isArray(section.data)">
      <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
          <h2 class="section-title">{{ (section.settings as any)?.title ?? 'Categories' }}</h2>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <NuxtLink
              v-for="cat in section.data"
              :key="cat.id"
              :to="`/products?category=${cat.slug}`"
              class="card p-4 flex flex-col items-center gap-3 group text-center hover:shadow-matcha transition-all"
            >
              <NuxtImg
                :src="cat.image || 'https://placehold.co/120x120/e5f2db/5a9e3c?text=' + cat.name"
                :alt="cat.name"
                class="w-16 h-16 rounded-full object-cover group-hover:scale-110 transition-transform"
                width="64"
                height="64"
              />
              <span class="text-sm font-medium text-charcoal group-hover:text-matcha-600 transition-colors">{{ cat.name }}</span>
            </NuxtLink>
          </div>
        </div>
      </section>
    </template>

    <template v-else-if="section.type === 'promotional_banner' && section.data">
      <section class="py-8 px-4">
        <div class="max-w-7xl mx-auto">
          <NuxtLink :to="(section.data as any).link_url || '#'" class="block rounded-2xl overflow-hidden group">
            <NuxtImg
              :src="(section.data as any).image"
              :alt="(section.data as any).title"
              class="w-full h-auto group-hover:scale-105 transition-transform duration-500"
            />
          </NuxtLink>
        </div>
      </section>
    </template>

    <template v-else-if="section.type === 'about' && section.settings">
      <section class="py-16 px-4 bg-cream-dark">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="font-serif text-3xl md:text-4xl font-semibold text-charcoal mb-6">
              {{ (section.settings as any).title }}
            </h2>
            <p class="text-gray-600 text-lg leading-relaxed mb-8">{{ (section.settings as any).body }}</p>
            <NuxtLink v-if="(section.settings as any).cta_url" :to="(section.settings as any).cta_url" class="btn-primary">
              {{ (section.settings as any).cta_text ?? 'Learn More' }}
            </NuxtLink>
          </div>
          <NuxtImg
            :src="(section.settings as any).image"
            :alt="(section.settings as any).title"
            class="rounded-2xl w-full aspect-video object-cover shadow-card"
          />
        </div>
      </section>
    </template>

    <template v-else-if="section.type === 'reviews' && Array.isArray(section.data)">
      <section class="py-16 px-4 bg-matcha-50">
        <div class="max-w-7xl mx-auto">
          <h2 class="section-title">{{ (section.settings as any)?.title ?? 'Reviews' }}</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
              v-for="review in section.data"
              :key="review.id"
              class="card p-6 flex flex-col gap-3"
            >
              <URating :value="review.rating" size="sm" />
              <p v-if="review.title" class="font-semibold text-charcoal">{{ review.title }}</p>
              <p class="text-gray-600 text-sm leading-relaxed line-clamp-4">{{ review.body }}</p>
              <div class="flex items-center gap-2 mt-auto pt-2 border-t border-gray-100">
                <div class="w-8 h-8 rounded-full bg-matcha-100 flex items-center justify-center text-matcha-700 font-medium text-sm">
                  {{ review.user?.name?.[0] ?? 'U' }}
                </div>
                <span class="text-sm font-medium">{{ review.user?.name ?? 'Customer' }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>

    <template v-else-if="section.type === 'newsletter'">
      <HomepageNewsletterSection :settings="section.settings" />
    </template>
  </div>
</template>
