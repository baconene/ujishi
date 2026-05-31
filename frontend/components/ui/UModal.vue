<script setup lang="ts">
const props = defineProps<{
  open: boolean
  title?: string
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
}>()

const emit = defineEmits<{ close: [] }>()
const isFull = computed(() => props.size === 'full')
const maxWidth = computed(() => ({
  sm: 'max-w-sm',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
  full: 'max-w-full h-[calc(100vh-2rem)] sm:h-auto',
}[props.size ?? 'md']))
const modalCorner = computed(() => isFull.value ? 'rounded-none' : 'rounded-2xl')
const panelPadding = computed(() => isFull.value ? 'p-4 sm:p-6 h-full overflow-y-auto' : 'p-6')
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="emit('close')">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="emit('close')" />
        <div :class="['relative w-full bg-white shadow-2xl', modalCorner, maxWidth]">
          <div v-if="title" class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="font-serif text-xl font-semibold">{{ title }}</h3>
            <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors" @click="emit('close')">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div :class="panelPadding">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
</style>
