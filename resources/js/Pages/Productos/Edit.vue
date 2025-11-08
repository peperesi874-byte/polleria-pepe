<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, toRef } from 'vue'

/* ========= Props ========= */
const props = defineProps({
  producto: { type: Object, required: true },
})

const producto = toRef(props, 'producto')

/* ========= Helpers ========= */
/** Devuelve una URL segura a partir de una ruta de Ziggy; si no existe, usa fallback */
function routeSafe(name, params = {}, absolute = true, fallback = '/productos') {
  try {
    if (typeof route !== 'undefined' && route().has(name)) {
      return route(name, params, absolute)
    }
  } catch {}
  return fallback
}

/** Navega SIEMPRE al listado de productos (no usa history.back) */
function goToProductos() {
  const url = routeSafe('productos.index', {}, true, '/productos')
  // Usamos Inertia si es posible para mantener layout/estado; si no, redirigimos.
  try {
    router.get(url, {}, { replace: true })
  } catch {
    window.location.href = url
  }
}

/* ========= Form ========= */
const form = useForm({
  nombre:          producto.value.nombre ?? '',
  descripcion:     producto.value.descripcion ?? '',
  precio:          Number(producto.value.precio ?? 0),
  stock:           Number(producto.value.stock ?? 0),
  activo:          !!producto.value.activo,
  imagen:          null,          // archivo nuevo
  eliminar_imagen: false,         // marcar para borrar imagen actual
})

/* Preview nueva imagen */
const preview = ref(null)
const onFile = (e) => {
  const file = e.target.files?.[0] ?? null
  form.imagen = file
  preview.value = file ? URL.createObjectURL(file) : null
  if (file) form.eliminar_imagen = false
}
const clearNewFile = () => {
  form.imagen = null
  preview.value = null
}

/* Guardar: POST + _method=put para multipart/form-data */
const guardar = () => {
  form.transform((data) => ({ ...data, _method: 'put' }))
  form.post(routeSafe('productos.update', producto.value.id, true, `/productos/${producto.value.id}`), {
    forceFormData: true,
    preserveScroll: true,
    onStart:  () => form.clearErrors(),
    onFinish: () => form.transform(d => d),
  })
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="text-2xl">✏️</span>
        <h1 class="text-3xl font-bold text-gray-900">Editar producto</h1>
      </div>

      <!-- VOLVER: SIEMPRE al listado -->
      <button
        type="button"
        @click="goToProductos"
        class="rounded-lg border border-indigo-200 bg-white px-3 py-1.5 text-indigo-700 hover:bg-indigo-50"
        title="Volver a Productos"
      >
        ← Volver
      </button>
    </div>

    <!-- Card -->
    <form
      @submit.prevent="guardar"
      enctype="multipart/form-data"
      class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
    >
      <!-- Nombre -->
      <div>
        <label class="mb-1 block text-sm text-gray-600" for="nombre">Nombre</label>
        <input
          id="nombre"
          v-model="form.nombre"
          type="text"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
        />
        <p v-if="form.errors.nombre" class="mt-1 text-sm text-rose-600">
          {{ form.errors.nombre }}
        </p>
      </div>

      <!-- Precio / Stock / Activo -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
          <label class="mb-1 block text-sm text-gray-600" for="precio">Precio</label>
          <input
            id="precio"
            v-model.number="form.precio"
            type="number"
            min="0" step="0.01"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <p v-if="form.errors.precio" class="mt-1 text-sm text-rose-600">{{ form.errors.precio }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm text-gray-600" for="stock">Stock</label>
          <input
            id="stock"
            v-model.number="form.stock"
            type="number" min="0"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <p v-if="form.errors.stock" class="mt-1 text-sm text-rose-600">{{ form.errors.stock }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm text-gray-600" for="activo">Activo</label>
          <select
            id="activo"
            v-model="form.activo"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          >
            <option :value="true">Sí</option>
            <option :value="false">No</option>
          </select>
          <p v-if="form.errors.activo" class="mt-1 text-sm text-rose-600">{{ form.errors.activo }}</p>
        </div>
      </div>

      <!-- Descripción -->
      <div>
        <label class="mb-1 block text-sm text-gray-600" for="descripcion">Descripción</label>
        <textarea
          id="descripcion" v-model="form.descripcion" rows="3"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
        />
        <p v-if="form.errors.descripcion" class="mt-1 text-sm text-rose-600">{{ form.errors.descripcion }}</p>
      </div>

      <!-- Imagen -->
      <div>
        <label class="mb-2 block text-sm text-gray-600">Imagen</label>

        <div class="flex flex-wrap items-center gap-4">
          <!-- Actual -->
          <img
            v-if="!preview && producto?.imagenUrl"
            :src="producto.imagenUrl"
            alt="Imagen actual"
            class="h-16 w-16 rounded-lg border border-gray-200 object-cover"
          />
          <span v-else-if="!preview" class="text-xs text-gray-400">Sin imagen</span>

          <!-- Preview nueva -->
          <img
            v-if="preview"
            :src="preview"
            alt="Nueva imagen"
            class="h-16 w-16 rounded-lg border border-gray-200 object-cover"
          />
        </div>

        <div class="mt-2 flex items-center gap-2">
          <input type="file" accept="image/*" @change="onFile" />
          <button
            v-if="preview"
            type="button"
            @click="clearNewFile"
            class="rounded-md border border-gray-300 px-2 py-1 text-sm text-gray-700 hover:bg-gray-50"
          >
            Quitar nueva imagen
          </button>
        </div>

        <p v-if="form.errors.imagen" class="mt-1 text-sm text-rose-600">{{ form.errors.imagen }}</p>

        <!-- Eliminar actual (solo si no hay nueva) -->
        <label v-if="producto?.imagenUrl && !preview" class="mt-2 flex select-none items-center gap-2">
          <input type="checkbox" v-model="form.eliminar_imagen" />
          <span class="text-sm text-gray-600">Eliminar imagen actual</span>
        </label>
      </div>

      <!-- Acciones -->
      <div class="flex justify-end gap-2 pt-2">
        <!-- Cancelar también te manda al listado fijo -->
        <button
          type="button"
          @click="goToProductos"
          class="rounded-md border px-4 py-2 text-gray-700 hover:bg-gray-50"
        >
          Cancelar
        </button>

        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-md bg-indigo-600 px-5 py-2 font-medium text-white transition hover:bg-indigo-700 disabled:opacity-60"
        >
          {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
        </button>
      </div>
    </form>
  </div>
</template>
