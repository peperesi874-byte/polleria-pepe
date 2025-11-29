<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  links: {
    type: Array,
    default: () => [],
  },
})

// Laravel manda algo así:
// [0] « Previous
// [1] 1
// ...
// [n] Next »
// Tomamos primero, último y el que venga con active = true
const prevLink = computed(() => props.links[0] ?? null)
const nextLink = computed(() =>
  props.links.length ? props.links[props.links.length - 1] : null
)
const currentLink = computed(() =>
  props.links.find(l => l.active) ?? null
)
</script>

<template>
  <nav
    v-if="links && links.length"
    class="flex items-center justify-center gap-2 mt-6"
    aria-label="Paginación de pedidos"
  >
    <!-- Previous -->
    <Link
      v-if="prevLink"
      :href="prevLink.url ?? ''"
      as="button"
      type="button"
      :disabled="!prevLink.url"
      class="px-4 py-2 text-xs sm:text-sm rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm
             hover:bg-slate-50 transition
             disabled:opacity-40 disabled:cursor-default disabled:pointer-events-none"
    >
      « Previous
    </Link>

    <!-- Página actual (botón moradito/ámbar tipo captura) -->
    <button
      v-if="currentLink"
      type="button"
      class="px-4 py-2 text-xs sm:text-sm rounded-full bg-indigo-600 text-white font-semibold shadow-sm"
    >
      {{ currentLink.label }}
    </button>

    <!-- Next -->
    <Link
      v-if="nextLink"
      :href="nextLink.url ?? ''"
      as="button"
      type="button"
      :disabled="!nextLink.url"
      class="px-4 py-2 text-xs sm:text-sm rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm
             hover:bg-slate-50 transition
             disabled:opacity-40 disabled:cursor-default disabled:pointer-events-none"
    >
      Next »
    </Link>
  </nav>
</template>
