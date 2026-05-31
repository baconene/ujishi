<script setup lang="ts">
withDefaults(defineProps<{
  variant?: 'primary' | 'outline' | 'ghost' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  loading?: boolean
  disabled?: boolean
  type?: 'button' | 'submit' | 'reset'
  full?: boolean
}>(), {
  variant: 'primary',
  size: 'md',
  loading: false,
  disabled: false,
  type: 'button',
  full: false,
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed',
      full && 'w-full',
      size === 'sm' && 'px-3 py-2 text-sm',
      size === 'md' && 'px-5 py-3 text-sm',
      size === 'lg' && 'px-8 py-4 text-base',
      variant === 'primary' && 'bg-matcha-500 text-white hover:bg-matcha-600 active:bg-matcha-700',
      variant === 'outline' && 'border-2 border-matcha-500 text-matcha-600 hover:bg-matcha-500 hover:text-white',
      variant === 'ghost' && 'text-matcha-600 hover:bg-matcha-50',
      variant === 'danger' && 'bg-red-500 text-white hover:bg-red-600',
    ]"
  >
    <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <slot />
  </button>
</template>
