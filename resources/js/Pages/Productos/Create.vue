<!-- resources/js/Pages/Productos/Create.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { route } from 'ziggy-js'

const form = useForm({
  nombre: '',
  descripcion: '',
  precio: 0,
  stock: 0,
  activo: true,
  imagen: null,
})

/* Preview de imagen */
const preview = ref(null)
const onFile = (e) => {
  const file = e.target.files?.[0] ?? null
  form.imagen = file
  preview.value = file ? URL.createObjectURL(file) : null
}
const clearFile = () => {
  form.imagen = null
  preview.value = null
}

/* Enviar */
const guardar = () => {
  form.post(route('productos.store'), {
    forceFormData: true,
    preserveScroll: true,
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Nuevo producto — Productos" />

    <!-- ⬇️ AHORA el encabezado va en el slot del layout -->
    <template #header>
      <div
        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <div class="rounded-xl bg-indigo-100 text-indigo-700 p-2">➕</div>
          <div>
            <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
              Nuevo producto
            </h2>
            <p class="text-xs text-gray-500">Registra artículos para el catálogo y el inventario.</p>
          </div>
        </div>

        <Link
          :href="route('productos.index')"
          class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-indigo-700 shadow-sm transition hover:bg-indigo-50"
        >
          ← Volver al listado
        </Link>
      </div>
    </template>

    <!-- Contenido de la página -->
    <div class="max-w-6xl mx-auto px-6 py-8">
      <form
        @submit.prevent="guardar"
        enctype="multipart/form-data"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6"
      >
        <!-- Columna izquierda -->
        <div class="lg:col-span-2 space-y-5">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Datos generales</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label for="nombre" class="mb-1 block text-sm text-gray-600">Nombre</label>
                <input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  class="w-full rounded-xl border border-gray-300/80 px-3 py-2 outline-none shadow-sm ring-indigo-200 focus:border-indigo-300 focus:ring-2"
                />
                <p v-if="form.errors.nombre" class="mt-1 text-sm text-rose-600">{{ form.errors.nombre }}</p>
              </div>

              <div>
                <label for="precio" class="mb-1 block text-sm text-gray-600">Precio</label>
                <input
                  id="precio"
                  v-model.number="form.precio"
                  type="number"
                  min="0" step="0.01"
                  class="w-full rounded-xl border border-gray-300/80 px-3 py-2 outline-none shadow-sm ring-indigo-200 focus:border-indigo-300 focus:ring-2"
                />
                <p v-if="form.errors.precio" class="mt-1 text-sm text-rose-600">{{ form.errors.precio }}</p>
              </div>

              <div>
                <label for="stock" class="mb-1 block text-sm text-gray-600">Stock</label>
                <input
                  id="stock"
                  v-model.number="form.stock"
                  type="number"
                  min="0"
                  class="w-full rounded-xl border border-gray-300/80 px-3 py-2 outline-none shadow-sm ring-indigo-200 focus:border-indigo-300 focus:ring-2"
                />
                <p v-if="form.errors.stock" class="mt-1 text-sm text-rose-600">{{ form.errors.stock }}</p>
              </div>

              <div class="md:col-span-2">
                <label for="descripcion" class="mb-1 block text-sm text-gray-600">Descripción</label>
                <textarea
                  id="descripcion"
                  v-model="form.descripcion"
                  rows="3"
                  class="w-full rounded-xl border border-gray-300/80 px-3 py-2 outline-none shadow-sm ring-indigo-200 focus:border-indigo-300 focus:ring-2"
                />
                <p v-if="form.errors.descripcion" class="mt-1 text-sm text-rose-600">{{ form.errors.descripcion }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Estado</h3>
            <label class="inline-flex items-center gap-3 select-none">
              <input
                id="activo"
                type="checkbox"
                v-model="form.activo"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <span
                :class="[
                  'rounded-full px-3 py-1 text-xs font-semibold ring-1',
                  form.activo
                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                    : 'bg-rose-50 text-rose-700 ring-rose-200'
                ]"
              >
                {{ form.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </label>
          </div>
        </div>

        <!-- Columna derecha -->
        <div class="space-y-5">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">Imagen</h3>

            <div class="flex items-start gap-4">
              <div class="shrink-0">
                <img
                  v-if="preview"
                  :src="preview"
                  alt="Vista previa"
                  class="h-28 w-28 rounded-xl border border-gray-200 object-cover"
                />
                <div
                  v-else
                  class="h-28 w-28 rounded-xl border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-gray-400"
                >
                  160×160
                </div>
              </div>

              <div class="flex-1">
                <!-- Input real oculto -->
                <input
                  id="imagen"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="onFile"
                />

                <!-- Botón personalizado -->
                <label
                  for="imagen"
                  class="inline-flex items-center gap-2 rounded-xl border bg-white px-3 py-2 cursor-pointer shadow-sm text-gray-700 hover:bg-gray-50"
                >
                  📁 Seleccionar imagen
                </label>

                <p v-if="form.errors.imagen" class="mt-1 text-sm text-rose-600">{{ form.errors.imagen }}</p>

                <button
                  v-if="preview"
                  type="button"
                  @click="clearFile"
                  class="mt-3 inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                >
                  Quitar imagen
                </button>
              </div>
            </div>

            <p class="mt-3 text-xs text-gray-400">Formatos aceptados: JPG, PNG, WEBP. Tamaño recomendado 800×800.</p>
          </div>

          <div class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 text-sm text-indigo-900">
            <p class="font-semibold mb-1">Consejo</p>
            <p>Completa nombre y precio primero. Puedes subir la imagen al final antes de guardar.</p>
          </div>
        </div>

        <!-- Barra de acciones -->
        <div class="lg:col-span-3 sticky bottom-4 z-10">
          <div class="rounded-2xl border border-gray-200 bg-white/90 backdrop-blur p-3 shadow-lg flex items-center justify-end gap-2">
            <Link
              :href="route('productos.index')"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-indigo-600 px-5 py-2 font-medium text-white transition hover:bg-indigo-700 disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Guardar producto' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
