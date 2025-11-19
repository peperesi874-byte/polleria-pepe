<script setup>
import { computed } from 'vue'

const props = defineProps({
  producto: { type: Object, required: true },
})
const emit = defineEmits(['add', 'preview'])

const imgSrc = computed(() => {
  const p = props.producto || {}
  return p.imagenUrl || null   // 👈 directo
})
</script>

<template>
  <article
    class="group rounded-2xl border border-neutral-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden"
  >
    <!-- Imagen (click abre vista rápida) -->
    <button
      type="button"
      class="w-full aspect-[4/3] bg-neutral-100 grid place-items-center overflow-hidden"
      @click="emit('preview', props.producto)"
    >
      <img
        v-if="imgSrc"
        :src="imgSrc"
        :alt="props.producto?.nombre"
        class="h-full w-full object-cover transition group-hover:scale-[1.03]"
      />
      <div v-else class="text-neutral-400">Sin imagen</div>
    </button>

    <!-- Datos -->
    <div class="p-4">
      <h3 class="line-clamp-1 font-semibold text-neutral-800">
        {{ props.producto?.nombre }}
      </h3>
      <p class="mt-1 text-sm line-clamp-2 text-neutral-500">
        {{ props.producto?.descripcion }}
      </p>

      <div class="mt-3 flex items-center justify-between">
        <div class="text-lg font-bold text-neutral-800">
          $ {{ Number(props.producto?.precio || 0).toFixed(2) }}
        </div>

        <button
          type="button"
          class="rounded-xl border border-neutral-300 bg-white px-3 py-1.5 text-sm font-medium text-neutral-700 hover:border-amber-500 hover:text-amber-600 transition"
          @click="emit('add', props.producto)"
        >
          Añadir
        </button>
      </div>
    </div>
  </article>
</template>
