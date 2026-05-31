<script setup lang="ts">
import type { CarouselSlide } from '~/types/models'

const props = defineProps<{ slides: CarouselSlide[] }>()

const currentSlide = ref(0)
let autoplayInterval: ReturnType<typeof setInterval>

onMounted(() => {
  autoplayInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % props.slides.length
  }, 5000)
})

onUnmounted(() => clearInterval(autoplayInterval))
</script>

<template>
  <div class="relative overflow-hidden bg-matcha-900" style="height: clamp(400px, 60vh, 700px)">
    <!-- Slides -->
    <TransitionGroup name="carousel" tag="div" class="relative h-full">
      <div
        v-for="(slide, index) in slides"
        v-show="index === currentSlide"
        :key="slide.id"
        class="absolute inset-0"
      >
        <NuxtImg
          :src="slide.desktop_image"
          :alt="slide.title || 'Hero slide'"
          class="w-full h-full object-cover"
          loading="eager"
          width="1920"
          height="700"
        />
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent" />

        <!-- Content -->
        <div class="absolute inset-0 flex items-center">
          <div class="max-w-7xl mx-auto px-6 md:px-12">
            <div class="max-w-xl">
              <h1 v-if="slide.title" class="font-serif text-4xl md:text-6xl font-bold text-white leading-tight mb-4 animate-slide-up">
                {{ slide.title }}
              </h1>
              <p v-if="slide.subtitle" class="text-cream text-lg md:text-xl mb-8 opacity-90">
                {{ slide.subtitle }}
              </p>
              <NuxtLink
                v-if="slide.cta_text && slide.cta_url"
                :to="slide.cta_url"
                class="btn-primary text-base px-8 py-4 inline-flex"
              >
                {{ slide.cta_text }}
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </TransitionGroup>

    <!-- Dots -->
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
      <button
        v-for="(_, i) in slides"
        :key="i"
        :class="[
          'w-2 h-2 rounded-full transition-all duration-300',
          i === currentSlide ? 'w-6 bg-white' : 'bg-white/40',
        ]"
        @click="currentSlide = i"
      />
    </div>

    <!-- Arrows -->
    <button
      class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/20 hover:bg-black/40 flex items-center justify-center text-white transition-all"
      @click="currentSlide = (currentSlide - 1 + slides.length) % slides.length"
    >
      &#8592;
    </button>
    <button
      class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/20 hover:bg-black/40 flex items-center justify-center text-white transition-all"
      @click="currentSlide = (currentSlide + 1) % slides.length"
    >
      &#8594;
    </button>
  </div>
</template>

<style scoped>
.carousel-enter-active, .carousel-leave-active { transition: opacity 0.5s ease; }
.carousel-enter-from, .carousel-leave-to { opacity: 0; }
</style>
