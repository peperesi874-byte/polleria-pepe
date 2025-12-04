<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  message: String,
  type: { type: String, default: "success" }
})

const visible = ref(false)

onMounted(() => {
  visible.value = true
  setTimeout(() => (visible.value = false), 2500)
})
</script>

<template>
  <transition name="fade">
    <div
      v-if="visible"
      class="fixed bottom-6 right-6 px-4 py-2 rounded-lg shadow-lg text-white text-sm"
      :class="{
        'bg-emerald-600': type === 'success',
        'bg-rose-600': type === 'error',
        'bg-amber-600': type === 'warning'
      }"
    >
      {{ message }}
    </div>
  </transition>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity .3s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
