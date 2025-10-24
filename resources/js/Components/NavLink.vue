<script setup>
import { computed } from 'vue'
const props = defineProps({ href: String, active: Boolean })
const cls = computed(() =>
  'px-3 py-2 rounded-lg text-sm font-medium transition ' +
  (props.active
    ? 'bg-yellow-500 text-white'
    : 'text-neutral-700 hover:text-neutral-900 hover:bg-neutral-100')
)
</script>

<template>
  <a :href="href" :class="cls"><slot /></a>
</template>
