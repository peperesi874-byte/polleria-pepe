<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
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
  form.post(
    routeSafe('productos.update', producto.value.id, true, `/productos/${producto.value.id}`),
    {
      forceFormData: true,
      preserveScroll: true,
      onStart:  () => form.clearErrors(),
      onFinish: () => form.transform(d => d),
    }
  )
}
</script>

<template>
  <Head title="Editar producto" />

  <AuthenticatedLayout>
    <!-- ===== HERO / BARRA SUPERIOR ===== -->
  <template #header>
  <div class="pt-4 pb-6">
    <div
      class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
    >
      <!-- blobs decorativos -->
      <div class="pointer-events-none absolute -left-20 -top-24 h-40 w-40 rounded-full bg-white/20 blur-3xl" />
      <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-44 rounded-full bg-black/20 blur-3xl" />

      <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <!-- IZQUIERDA -->
        <div class="space-y-3">
          <!-- Chip superior -->
          <div
            class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
          >
            <span class="text-lg">✏️</span>
            <span>Edición de producto — Pollería Pepe</span>
          </div>

          <!-- Título + descripción -->
          <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">
              Editar producto
            </h1>
            <p class="mt-1 text-sm text-amber-50/90">
              Ajusta nombre, precio, stock y la imagen sin perder consistencia en el catálogo.
            </p>
          </div>

          <!-- Chips de contexto -->
          <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-amber-50/90">
            <span
              class="inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 font-semibold text-amber-900 shadow-sm"
            >
              <span class="text-[11px] uppercase tracking-wide text-amber-500">ID</span>
              <span>#{{ producto.id }}</span>
            </span>

            <span
              class="inline-flex max-w-xs items-center gap-1 rounded-full bg-amber-500/20 px-2.5 py-1"
            >
              <span class="text-[11px] uppercase tracking-wide">Producto</span>
              <span class="truncate font-semibold">{{ producto.nombre }}</span>
            </span>

            <!-- Estado rápido -->
            <span
              class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold shadow-sm"
              :class="form.activo
                ? 'bg-emerald-50 text-emerald-800'
                : 'bg-slate-900/40 text-amber-50 border border-white/30'"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="form.activo ? 'bg-emerald-500' : 'bg-rose-400'"
              />
              {{ form.activo ? 'Activo en catálogo' : 'Inactivo / oculto' }}
            </span>
          </div>
        </div>

        <!-- DERECHA -->
        <div class="flex flex-col items-end gap-2">
          <button
            type="button"
            @click="goToProductos"
            class="inline-flex items-center gap-2 rounded-2xl bg-black/25 px-3 py-2 text-xs font-medium text-amber-50 shadow-sm hover:bg-black/35"
          >
            ← Volver al catálogo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/20 to-slate-50">
      <div class="mx-auto max-w-5xl px-4 py-8 lg:px-6">
        <form
          @submit.prevent="guardar"
          enctype="multipart/form-data"
          class="overflow-hidden rounded-[28px] bg-white/95 shadow-[0_18px_45px_rgba(15,23,42,0.10)] ring-1 ring-amber-100"
        >
          <!-- Encabezado interno -->
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-amber-100 bg-amber-50/60 px-6 py-4">
            <div class="space-y-1">
              <p class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-amber-700">
                <span class="h-6 w-6 flex items-center justify-center rounded-full bg-white text-[13px] shadow-sm">
                  🧾
                </span>
                <span>Edición</span>
              </p>
              <h2 class="text-lg md:text-xl font-semibold text-amber-950">
                {{ producto.nombre }}
              </h2>
              <p class="text-xs text-amber-900/80">
                Actualiza la información básica, el stock y la visibilidad de este producto.
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                @click="goToProductos"
                class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs font-medium text-amber-800 shadow-sm hover:bg-amber-50"
              >
                ← Volver
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60"
              >
                {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
              </button>
            </div>
          </div>

          <!-- Contenido -->
          <div class="grid grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-3">
            <!-- Columna izquierda: Campos -->
            <div class="space-y-5 lg:col-span-2">
              <!-- Nombre -->
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700" for="nombre">
                  Nombre
                </label>
                <input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  class="w-full rounded-2xl border border-amber-200 bg-amber-50/40 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                />
                <p v-if="form.errors.nombre" class="mt-1 text-xs text-rose-600">{{ form.errors.nombre }}</p>
              </div>

              <!-- Descripción -->
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700" for="descripcion">
                  Descripción
                </label>
                <textarea
                  id="descripcion"
                  v-model="form.descripcion"
                  rows="4"
                  class="w-full rounded-2xl border border-amber-200 bg-amber-50/40 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                />
                <p v-if="form.errors.descripcion" class="mt-1 text-xs text-rose-600">{{ form.errors.descripcion }}</p>
              </div>

              <!-- Precio / Stock / Activo -->
              <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700" for="precio">
                    Precio
                  </label>
                  <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs text-amber-500">
                      $
                    </span>
                    <input
                      id="precio"
                      v-model.number="form.precio"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-full rounded-2xl border border-amber-200 bg-white px-6 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                    />
                  </div>
                  <p v-if="form.errors.precio" class="mt-1 text-xs text-rose-600">{{ form.errors.precio }}</p>
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700" for="stock">
                    Stock
                  </label>
                  <input
                    id="stock"
                    v-model.number="form.stock"
                    type="number"
                    min="0"
                    class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                  />
                  <p v-if="form.errors.stock" class="mt-1 text-xs text-rose-600">{{ form.errors.stock }}</p>
                </div>

                <div>
                  <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700" for="activo">
                    Estado
                  </label>
                  <select
                    id="activo"
                    v-model="form.activo"
                    class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                  >
                    <option :value="true">Activo (visible)</option>
                    <option :value="false">Inactivo (oculto)</option>
                  </select>
                  <p v-if="form.errors.activo" class="mt-1 text-xs text-rose-600">{{ form.errors.activo }}</p>
                </div>
              </div>
            </div>

            <!-- Columna derecha: Imagen + resumen -->
            <div class="space-y-4">
              <div class="rounded-2xl border border-amber-100 bg-amber-50/40 p-4">
                <h3 class="mb-1 text-sm font-semibold text-amber-900">
                  Imagen del producto
                </h3>
                <p class="mb-3 text-xs text-amber-900/80">
                  Sube una imagen clara del producto para que se vea bien en el catálogo.
                </p>

                <!-- Vista actual / preview -->
                <div class="flex items-start gap-4">
                  <div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl border border-amber-200 bg-white">
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
                      class="flex h-full w-full items-center justify-center text-[11px] text-amber-400"
                    >
                      Sin imagen
                    </div>
                  </div>

                  <!-- Dropzone -->
                  <div
                    class="flex-1 rounded-2xl border border-dashed border-amber-300 bg-amber-50/60 px-4 py-3 text-amber-900"
                    @drop="onDrop"
                    @dragover="onDragOver"
                  >
                    <p class="text-xs">
                      Arrastra y suelta una imagen aquí, o
                      <button type="button" class="font-semibold underline hover:text-amber-800" @click="pickFile">
                        selecciónala desde tu equipo
                      </button>
                      .
                    </p>
                    <p class="mt-1 text-[11px] text-amber-900/70">
                      Formatos: JPG, PNG, WEBP. Ideal en proporción cuadrada.
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
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                  >
                    Quitar nueva imagen
                  </button>

                  <label
                    v-if="producto?.imagenUrl && !preview"
                    class="inline-flex select-none items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100/70"
                  >
                    <input type="checkbox" v-model="form.eliminar_imagen" />
                    Eliminar imagen actual
                  </label>
                </div>

                <p v-if="form.errors.imagen" class="mt-2 text-xs text-rose-600">
                  {{ form.errors.imagen }}
                </p>
              </div>

              <!-- Resumen rápido -->
              <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-xs text-slate-700">
                <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                  Resumen rápido
                </p>
                <div class="space-y-1.5">
                  <div class="flex items-center justify-between">
                    <span>Precio</span>
                    <span class="font-semibold text-emerald-700">
                      ${{ form.precio?.toFixed?.(2) ?? Number(form.precio || 0).toFixed(2) }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Stock</span>
                    <span class="font-semibold text-slate-900">
                      {{ form.stock }} unidades
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span>Estado</span>
                    <span
                      class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                      :class="form.activo
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-slate-800 text-amber-50'"
                    >
                      <span
                        class="h-1.5 w-1.5 rounded-full"
                        :class="form.activo ? 'bg-emerald-500' : 'bg-rose-400'"
                      />
                      {{ form.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pie de acciones -->
          <div class="flex items-center justify-end gap-2 border-t border-amber-100 bg-amber-50/60 px-6 py-4">
            <button
              type="button"
              @click="goToProductos"
              class="rounded-xl border border-amber-200 bg-white px-4 py-2 text-xs font-medium text-amber-800 hover:bg-amber-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-amber-600 px-5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60"
            >
              {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
