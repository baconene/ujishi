<script setup lang="ts">
import { useSwipe } from '@vueuse/core'
import type { CarouselSlide } from '~/types/models'

const props = defineProps<{ slides: CarouselSlide[] }>()

const currentSlide = ref(0)
const direction = ref<'next' | 'prev'>('next')
let autoplayInterval: ReturnType<typeof setInterval> | null = null

function startAutoplay() {
  stopAutoplay()
  if (props.slides.length <= 1) return
  autoplayInterval = setInterval(() => goTo('next'), 5000)
}

function stopAutoplay() {
  if (autoplayInterval) clearInterval(autoplayInterval)
}

function goTo(dir: 'next' | 'prev') {
  direction.value = dir
  if (dir === 'next') {
    currentSlide.value = (currentSlide.value + 1) % props.slides.length
  } else {
    currentSlide.value = (currentSlide.value - 1 + props.slides.length) % props.slides.length
  }
}

function setSlide(index: number) {
  direction.value = index > currentSlide.value ? 'next' : 'prev'
  currentSlide.value = index
  startAutoplay()
}

const carouselEl = ref<HTMLElement | null>(null)

const { direction: swipeDir } = useSwipe(carouselEl, {
  onSwipeEnd() {
    if (swipeDir.value === 'left') { goTo('next'); startAutoplay() }
    else if (swipeDir.value === 'right') { goTo('prev'); startAutoplay() }
  },
})

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
</script>

<template>
  <div
    ref="carouselEl"
    class="relative overflow-hidden bg-matcha-900"
    style="height: clamp(400px, 60vh, 700px)"
    @mouseenter="stopAutoplay"
    @mouseleave="startAutoplay"
  >
    <div class="relative w-full h-full">
      <template v-for="(slide, index) in slides" :key="slide.id">
        <Transition :name="direction === 'next' ? 'slide-left' : 'slide-right'">
          <div v-if="index === currentSlide" class="absolute inset-0">
            <picture class="w-full h-full">
              <source
                v-if="slide.mobile_image"
                :srcset="slide.mobile_image"
                media="(max-width: 767px)"
              />
              <img
                :src="slide.desktop_image"
                :alt="slide.title || 'Hero slide'"
                class="w-full h-full object-cover"
              />
            </picture>
            <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent" />
            <div class="absolute inset-0 flex items-center">
              <div class="max-w-7xl mx-auto px-6 md:px-12 w-full">
                <div class="max-w-xl">
                  <h1
                    v-if="slide.title"
                    class="font-serif text-2xl sm:text-4xl md:text-6xl font-bold text-white leading-tight mb-4"
                  >
                    {{ slide.title }}
                  </h1>
                  <p v-if="slide.subtitle" class="text-cream text-base sm:text-lg md:text-xl mb-6 sm:mb-8 opacity-90">
                    {{ slide.subtitle }}
                  </p>
                  <NuxtLink
                    v-if="slide.cta_text && slide.cta_url"
                    :to="slide.cta_url"
                    class="btn-primary inline-flex"
                  >
                    {{ slide.cta_text }}
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </template>
    </div>

    <!-- Dots -->
    <div v-if="slides.length > 1" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-10">
      <button
        v-for="(_, i) in slides"
        :key="i"
        :class="[
          'h-2 rounded-full transition-all duration-300',
          i === currentSlide ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/70',
        ]"
        @click="setSlide(i)"
      />
    </div>

    <!-- Arrows (hidden on small mobile to save space, dots + swipe suffice) -->
    <template v-if="slides.length > 1">
      <button
        class="hidden sm:flex absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/20 hover:bg-black/50 items-center justify-center text-white transition-all z-10"
        @click="goTo('prev'); startAutoplay()"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <button
        class="hidden sm:flex absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/20 hover:bg-black/50 items-center justify-center text-white transition-all z-10"
        @click="goTo('next'); startAutoplay()"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </template>
  </div>
</template>

<style scoped>
.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all 0.5s ease;
  position: absolute;
  inset: 0;
}
.slide-left-enter-from  { transform: translateX(100%); opacity: 0; }
.slide-left-leave-to    { transform: translateX(-100%); opacity: 0; }
.slide-right-enter-from { transform: translateX(-100%); opacity: 0; }
.slide-right-leave-to   { transform: translateX(100%); opacity: 0; }
</style>
