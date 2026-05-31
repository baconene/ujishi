<script setup lang="ts">
const props = withDefaults(defineProps<{
  value: number
  max?: number
  size?: 'sm' | 'md' | 'lg'
  readonly?: boolean
}>(), { max: 5, size: 'md', readonly: true })

const emit = defineEmits<{ change: [value: number] }>()

const stars = computed(() => Array.from({ length: props.max }, (_, i) => i + 1))
const sizeClass = computed(() => ({
  sm: 'w-3.5 h-3.5',
  md: 'w-5 h-5',
  lg: 'w-6 h-6',
}[props.size]))
</script>

<template>
  <div class="flex gap-0.5">
    <svg
      v-for="star in stars"
      :key="star"
      :class="[
        sizeClass,
        star <= value ? 'text-amber-400' : 'text-gray-200',
        !readonly && 'cursor-pointer hover:text-amber-400 transition-colors',
      ]"
      fill="currentColor"
      viewBox="0 0 20 20"
      @click="!readonly && emit('change', star)"
    >
      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
    </svg>
  </div>
</template>
