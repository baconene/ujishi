<script setup lang="ts">
const props = defineProps<{
  currentPage: number
  lastPage: number
  total: number
}>()

const emit = defineEmits<{ change: [page: number] }>()

const pages = computed(() => {
  const range: (number | '...')[] = []
  const delta = 2
  const left = props.currentPage - delta
  const right = props.currentPage + delta + 1

  for (let i = 1; i <= props.lastPage; i++) {
    if (i === 1 || i === props.lastPage || (i >= left && i < right)) {
      range.push(i)
    }
  }

  const result: (number | '...')[] = []
  let prev: number | null = null
  for (const page of range as number[]) {
    if (prev && page - prev > 1) result.push('...')
    result.push(page)
    prev = page
  }
  return result
})
</script>

<template>
  <div v-if="lastPage > 1" class="flex items-center justify-center gap-2 mt-8">
    <button
      :disabled="currentPage === 1"
      class="px-3 py-2 rounded-lg border border-gray-200 text-sm disabled:opacity-40 hover:border-matcha-400 transition-colors"
      @click="emit('change', currentPage - 1)"
    >
      &larr;
    </button>

    <template v-for="page in pages" :key="page">
      <span v-if="page === '...'" class="px-2 text-gray-400">...</span>
      <button
        v-else
        :class="[
          'w-9 h-9 rounded-lg text-sm font-medium transition-colors',
          page === currentPage
            ? 'bg-matcha-500 text-white'
            : 'border border-gray-200 hover:border-matcha-400',
        ]"
        @click="emit('change', page)"
      >
        {{ page }}
      </button>
    </template>

    <button
      :disabled="currentPage === lastPage"
      class="px-3 py-2 rounded-lg border border-gray-200 text-sm disabled:opacity-40 hover:border-matcha-400 transition-colors"
      @click="emit('change', currentPage + 1)"
    >
      &rarr;
    </button>
  </div>
</template>
