<script setup>
import { computed } from 'vue'

const props = defineProps({
  open: { type: Boolean, default: false },
  product: { type: Object, default: null },
})
const emit = defineEmits(['close', 'add'])

const imgSrc = computed(() => {
  const p = props.product || {}
  return p.imagenUrl || null
})
</script>

<template>
  <transition name="fade">
    <div
      v-if="props.open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click.self="emit('close')"
    >
      <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
        <!-- Imagen -->
        <div class="relative bg-neutral-100">
          <img
            v-if="imgSrc"
            :src="imgSrc"
            :alt="props.product?.nombre"
            class="w-full h-72 object-contain"
          />
          <div v-else class="h-72 flex items-center justify-center text-neutral-400">
            Sin imagen
          </div>

          <button
            class="absolute top-3 right-3 rounded-full bg-white/80 p-2 hover:bg-white shadow"
            @click="emit('close')"
          >
            ✕
          </button>
        </div>

        <!-- Contenido -->
        <div class="p-6">
          <h2 class="text-xl font-semibold text-neutral-900">
            {{ props.product?.nombre }}
          </h2>
          <p class="mt-2 text-sm text-neutral-600">
            {{ props.product?.descripcion }}
          </p>
          <p class="mt-4 text-lg font-bold text-amber-600">
            ${{ Number(props.product?.precio || 0).toFixed(2) }}
          </p>

          <div class="mt-6 flex justify-end">
            <button
              class="rounded-xl bg-amber-500 px-5 py-2 text-white font-semibold hover:bg-amber-600"
              @click="emit('add', props.product)"
            >
              Añadir al carrito
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
