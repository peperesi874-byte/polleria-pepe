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

    <!-- 🌈 HEADER SUPER COLORIDO -->
    <template #header>
      <div class="pt-4 pb-6">
        <div
          class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-7 py-6 text-white shadow-[0_22px_55px_rgba(249,115,22,0.45)]"
        >
          <!-- decor -->
          <div class="pointer-events-none absolute -left-16 -top-20 h-40 w-40 rounded-full bg-white/20 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-44 rounded-full bg-black/25 blur-3xl" />

          <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Izquierda -->
            <div class="space-y-3">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
              >
                <span class="text-lg">➕</span>
                <span>Alta de producto — Catálogo Pollería Pepe</span>
              </div>

              <div>
                <h1 class="text-3xl font-extrabold tracking-tight drop-shadow-sm">
                  Registrar nuevo producto
                </h1>
                <p class="mt-1 text-sm text-amber-50/90">
                  Define nombre, precio, stock y una buena imagen para mostrarlo en el catálogo.
                </p>
              </div>

              <div class="flex flex-wrap gap-2 text-[11px] text-amber-50/95">
                <span
                  class="inline-flex items-center gap-1 rounded-full bg-white/95 px-3 py-1 font-semibold text-amber-900 shadow-sm"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                  Paso 1 · Información básica
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-black/15 px-3 py-1">
                  Nombre · precio · stock · descripción · imagen
                </span>
              </div>
            </div>

            <!-- Derecha -->
            <div class="flex flex-col items-end gap-2">
              <Link
                :href="route('productos.index')"
                class="inline-flex items-center gap-2 rounded-2xl bg-black/25 px-3 py-2 text-xs font-medium text-amber-50 hover:bg-black/35"
              >
                ← Volver al listado
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🌤 CONTENIDO -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/25 to-slate-50">
      <div class="mx-auto max-w-7xl px-4 pb-10 pt-2 lg:px-6">
        <form
          @submit.prevent="guardar"
          enctype="multipart/form-data"
          class="grid grid-cols-1 gap-6 lg:grid-cols-3"
        >
          <!-- 📝 COLUMNA IZQUIERDA: DATOS -->
          <div class="space-y-5 lg:col-span-2">
            <!-- Datos generales -->
            <div class="rounded-3xl border border-amber-100 bg-white/95 p-6 shadow-sm">
              <div class="mb-4 flex items-center justify-between gap-2">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                    Datos generales
                  </p>
                  <p class="text-[11px] text-slate-500">
                    Son los campos que verá el cliente en el catálogo.
                  </p>
                </div>
                <span
                  class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200"
                >
                  🧾 Ficha de producto
                </span>
              </div>

              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Nombre -->
                <div class="md:col-span-2">
                  <label for="nombre" class="mb-1 block text-xs font-semibold text-slate-700">
                    Nombre del producto
                  </label>
                  <input
                    id="nombre"
                    v-model="form.nombre"
                    type="text"
                    placeholder="Ej. Pechuga de pollo marinada"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm outline-none ring-1 ring-slate-100 focus:border-amber-300 focus:bg-white focus:ring-2 focus:ring-amber-300/80"
                  />
                  <p v-if="form.errors.nombre" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.nombre }}
                  </p>
                </div>

                <!-- Precio -->
                <div>
                  <label for="precio" class="mb-1 block text-xs font-semibold text-slate-700">
                    Precio (MXN)
                  </label>
                  <div class="relative">
                    <span
                      class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs font-semibold text-slate-400"
                    >
                      $
                    </span>
                    <input
                      id="precio"
                      v-model.number="form.precio"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-7 py-2.5 text-sm outline-none ring-1 ring-slate-100 focus:border-amber-300 focus:bg-white focus:ring-2 focus:ring-amber-300/80"
                    />
                  </div>
                  <p v-if="form.errors.precio" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.precio }}
                  </p>
                </div>

                <!-- Stock -->
                <div>
                  <label for="stock" class="mb-1 block text-xs font-semibold text-slate-700">
                    Stock disponible
                  </label>
                  <input
                    id="stock"
                    v-model.number="form.stock"
                    type="number"
                    min="0"
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm outline-none ring-1 ring-slate-100 focus:border-amber-300 focus:bg-white focus:ring-2 focus:ring-amber-300/80"
                  />
                  <p v-if="form.errors.stock" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.stock }}
                  </p>
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                  <label for="descripcion" class="mb-1 block text-xs font-semibold text-slate-700">
                    Descripción
                  </label>
                  <textarea
                    id="descripcion"
                    v-model="form.descripcion"
                    rows="3"
                    placeholder="Describe cómo se vende (por kilo, pieza, combo, etc.) y algo que lo haga atractivo."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm outline-none ring-1 ring-slate-100 focus:border-amber-300 focus:bg-white focus:ring-2 focus:ring-amber-300/80"
                  />
                  <p v-if="form.errors.descripcion" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.descripcion }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Estado -->
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50/60 p-5 shadow-sm">
              <div class="mb-2 flex items-center justify-between gap-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                  Estado de publicación
                </p>
                <span class="text-[11px] text-emerald-700/80">
                  Puedes desactivarlo sin borrarlo del sistema.
                </span>
              </div>

              <label class="inline-flex cursor-pointer items-center gap-3 select-none">
                <input
                  id="activo"
                  type="checkbox"
                  v-model="form.activo"
                  class="h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"
                />
                <span
                  :class="[
                    'rounded-full px-3 py-1 text-xs font-semibold ring-1 transition',
                    form.activo
                      ? 'bg-emerald-600 text-white ring-emerald-700 shadow-sm'
                      : 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                  ]"
                >
                  {{ form.activo ? 'Producto visible en el catálogo' : 'Producto oculto (inactivo)' }}
                </span>
              </label>
            </div>
          </div>

          <!-- 🖼 COLUMNA DERECHA: IMAGEN + TIP -->
          <div class="space-y-5">
            <!-- Imagen -->
            <div class="rounded-3xl border border-amber-100 bg-white/95 p-6 shadow-sm">
              <div class="mb-3 flex items-center justify-between gap-2">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                    Imagen del producto
                  </p>
                  <p class="text-[11px] text-slate-500">
                    Ayuda a reconocerlo rápido en el catálogo y en el carrito.
                  </p>
                </div>
                <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] text-amber-700 ring-1 ring-amber-200">
                  📷 Vista previa
                </span>
              </div>

              <div class="flex items-start gap-4">
                <!-- Preview -->
                <div class="shrink-0">
                  <img
                    v-if="preview"
                    :src="preview"
                    alt="Vista previa"
                    class="h-28 w-28 rounded-2xl border border-amber-100 object-cover shadow-sm"
                  />
                  <div
                    v-else
                    class="flex h-28 w-28 items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-amber-50/50 text-[11px] text-amber-400"
                  >
                    800×800
                  </div>
                </div>

                <!-- Controles -->
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
                    class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50/80 px-3 py-2 text-xs font-semibold text-amber-900 shadow-sm hover:bg-amber-100"
                  >
                    <span>📁</span>
                    <span>Seleccionar imagen</span>
                  </label>

                  <p v-if="form.errors.imagen" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.imagen }}
                  </p>

                  <button
                    v-if="preview"
                    type="button"
                    @click="clearFile"
                    class="mt-3 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
                  >
                    🗑 Quitar imagen
                  </button>

                  <p class="mt-3 text-[11px] leading-relaxed text-slate-400">
                    Formatos: JPG, PNG, WEBP. Tamaño sugerido <strong>800×800</strong> y fondo claro
                    para que combine con el diseño del sistema.
                  </p>
                </div>
              </div>
            </div>

            <!-- Tip -->
            <div class="rounded-3xl border border-indigo-100 bg-indigo-50/60 p-4 text-[12px] text-indigo-900">
              <p class="mb-1 flex items-center gap-2 font-semibold">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/80 text-xs">
                  💡
                </span>
                Consejo rápido
              </p>
              <p>
                Primero llena <strong>nombre</strong>, <strong>precio</strong> y
                <strong>stock</strong>. Si te hace falta la foto, puedes subirla después
                editando el producto desde el catálogo.
              </p>
            </div>
          </div>

          <!-- 🧷 BARRA DE ACCIONES STICKY -->
          <div class="lg:col-span-3 sticky bottom-4 z-10 mt-2">
            <div
              class="flex items-center justify-end gap-2 rounded-2xl border border-slate-200 bg-white/90 px-3 py-3 shadow-lg backdrop-blur"
            >
              <Link
                :href="route('productos.index')"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-2xl bg-orange-500 px-5 py-2 text-xs font-semibold text-white shadow-sm shadow-orange-300/60 transition hover:bg-orange-600 disabled:opacity-60"
              >
                {{ form.processing ? 'Guardando…' : 'Guardar producto' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
