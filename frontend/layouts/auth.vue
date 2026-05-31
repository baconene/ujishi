<template>
  <div class="min-h-screen bg-matcha-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <NuxtLink to="/" class="inline-flex items-center gap-2 group">
          <div class="w-12 h-12 rounded-full bg-matcha-600 flex items-center justify-center text-white font-serif font-bold text-xl">宇</div>
          <span class="font-serif text-2xl font-semibold text-charcoal group-hover:text-matcha-600 transition-colors">Uji-Shi</span>
        </NuxtLink>
      </div>
      <div class="card p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
