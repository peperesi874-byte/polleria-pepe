<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref, toRef } from 'vue'

/* ========= Props ========= */
const props = defineProps({
  producto: { type: Object, required: true },
})
const producto = toRef(props, 'producto')

/* ========= Rutas seguras / Navegación ========= */
function routeSafe(name, params = {}, absolute = true, fallback = '/productos') {
  try {
    if (typeof route !== 'undefined' && route().has(name)) {
      return route(name, params, absolute)
    }
  } catch {}
  return fallback
}
function goToProductos() {
  const url = routeSafe('productos.index', {}, true, '/productos')
  try { router.get(url, {}, { replace: true }) } catch { window.location.href = url }
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

/* ========= Imagen (preview / drop) ========= */
const preview = ref(null)
const inputFile = ref(null)

function pickFile() {
  inputFile.value?.click()
}
function onFile(e) {
  const file = e.target.files?.[0] ?? null
  form.imagen = file
  preview.value = file ? URL.createObjectURL(file) : null
  if (file) form.eliminar_imagen = false
}
function clearNewFile() {
  form.imagen = null
  preview.value = null
  if (inputFile.value) inputFile.value.value = ''
}
function onDrop(e) {
  e.preventDefault()
  const file = e.dataTransfer?.files?.[0]
  if (!file) return
  form.imagen = file
  preview.value = URL.createObjectURL(file)
  form.eliminar_imagen = false
}
function onDragOver(e) { e.preventDefault() }

/* ========= Guardar ========= */
function guardar() {
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
  <div class="max-w-5xl mx-auto px-6 py-8">
    <!-- Card principal -->
    <form
      @submit.prevent="guardar"
      enctype="multipart/form-data"
      class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
    >
      <!-- Encabezado interno del card (no barra superior global) -->
      <div class="flex flex-wrap items-center justify-between gap-3 border-b px-6 py-4">
        <div>
          <h1 class="text-xl md:text-2xl font-semibold text-gray-900">Editar producto</h1>
          <p class="text-xs text-gray-500">Actualiza la información básica, estado y la imagen.</p>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="goToProductos"
            class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-indigo-700 shadow-sm transition hover:bg-indigo-50"
            title="Volver a Productos"
          >
            ← Volver
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60"
          >
            {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
          </button>
        </div>
      </div>

      <!-- Contenido -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
        <!-- Columna izquierda: Campos -->
        <div class="lg:col-span-2 space-y-5">
          <!-- Nombre -->
          <div>
            <label class="mb-1 block text-sm text-gray-600" for="nombre">Nombre</label>
            <input
              id="nombre"
              v-model="form.nombre"
              type="text"
              class="w-full rounded-xl border border-gray-300/80 bg-white px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
            />
            <p v-if="form.errors.nombre" class="mt-1 text-sm text-rose-600">{{ form.errors.nombre }}</p>
          </div>

          <!-- Descripción -->
          <div>
            <label class="mb-1 block text-sm text-gray-600" for="descripcion">Descripción</label>
            <textarea
              id="descripcion"
              v-model="form.descripcion"
              rows="4"
              class="w-full rounded-xl border border-gray-300/80 bg-white px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
            />
            <p v-if="form.errors.descripcion" class="mt-1 text-sm text-rose-600">{{ form.errors.descripcion }}</p>
          </div>

          <!-- Precio / Stock / Activo -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="mb-1 block text-sm text-gray-600" for="precio">Precio</label>
              <input
                id="precio"
                v-model.number="form.precio"
                type="number" min="0" step="0.01"
                class="w-full rounded-xl border border-gray-300/80 bg-white px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
              />
              <p v-if="form.errors.precio" class="mt-1 text-sm text-rose-600">{{ form.errors.precio }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm text-gray-600" for="stock">Stock</label>
              <input
                id="stock"
                v-model.number="form.stock"
                type="number" min="0"
                class="w-full rounded-xl border border-gray-300/80 bg-white px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
              />
              <p v-if="form.errors.stock" class="mt-1 text-sm text-rose-600">{{ form.errors.stock }}</p>
            </div>

            <div>
              <label class="mb-1 block text-sm text-gray-600" for="activo">Activo</label>
              <select
                id="activo"
                v-model="form.activo"
                class="w-full rounded-xl border border-gray-300/80 bg-white px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
              >
                <option :value="true">Sí</option>
                <option :value="false">No</option>
              </select>
              <p v-if="form.errors.activo" class="mt-1 text-sm text-rose-600">{{ form.errors.activo }}</p>
            </div>
          </div>
        </div>

        <!-- Columna derecha: Imagen -->
        <div class="space-y-4">
          <div class="rounded-2xl border border-gray-200 p-4">
            <h3 class="mb-2 text-sm font-medium text-gray-800">Imagen del producto</h3>

            <!-- Vista actual / preview -->
            <div class="flex items-start gap-4">
              <div class="w-24 h-24 shrink-0 rounded-xl border border-gray-200 bg-white overflow-hidden">
                <img
                  v-if="preview"
                  :src="preview"
                  alt="Nueva imagen"
                  class="h-full w-full object-cover"
                />
                <img
                  v-else-if="producto?.imagenUrl"
                  :src="producto.imagenUrl"
                  alt="Imagen actual"
                  class="h-full w-full object-cover"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center text-xs text-gray-400"
                >
                  Sin imagen
                </div>
              </div>

              <!-- Dropzone -->
              <div
                class="flex-1 rounded-xl border border-dashed border-indigo-200 bg-indigo-50/50 p-4 text-indigo-700/90"
                @drop="onDrop"
                @dragover="onDragOver"
              >
                <p class="text-sm">
                  Arrastra y suelta una imagen aquí, o
                  <button type="button" class="underline hover:text-indigo-800" @click="pickFile">
                    selecciónala
                  </button>
                </p>
                <p class="text-xs text-indigo-900/60 mt-1">
                  PNG, JPG o WEBP. Tamaño recomendado cuadrado.
                </p>
                <input
                  ref="inputFile"
                  type="file"
                  class="hidden"
                  accept="image/*"
                  @change="onFile"
                />
              </div>
            </div>

            <!-- Controles imagen -->
            <div class="mt-3 flex flex-wrap items-center gap-2">
              <button
                v-if="preview"
                type="button"
                @click="clearNewFile"
                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
              >
                Quitar nueva imagen
              </button>

              <label
                v-if="producto?.imagenUrl && !preview"
                class="inline-flex select-none items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm text-rose-700 hover:bg-rose-100/70"
              >
                <input type="checkbox" v-model="form.eliminar_imagen" />
                Eliminar imagen actual
              </label>
            </div>

            <p v-if="form.errors.imagen" class="mt-2 text-sm text-rose-600">{{ form.errors.imagen }}</p>
          </div>
        </div>
      </div>

      <!-- Pie de acciones (sticky dentro del card) -->
      <div class="flex items-center justify-end gap-2 border-t px-6 py-4 bg-gray-50/60">
        <button
          type="button"
          @click="goToProductos"
          class="rounded-xl border px-4 py-2 text-gray-700 hover:bg-gray-100"
        >
          Cancelar
        </button>
        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-xl bg-indigo-600 px-5 py-2 text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60"
        >
          {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
        </button>
      </div>
    </form>
  </div>
</template>
