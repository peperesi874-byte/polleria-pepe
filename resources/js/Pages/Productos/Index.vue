<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  productos: { type: Object, required: true }, // { data, links, from, ... }
  filters:   { type: Object, default: () => ({ q: '' }) },
})

const page  = usePage()
const flash = page.props.flash ?? {}

/** Ziggy seguro */
function safeRoute(name, params = {}, absolute = true) {
  try {
    if (typeof route !== 'undefined' && route().has(name)) {
      return route(name, params, absolute)
    }
  } catch {}
  return null
}

/** Buscador con debounce */
const q = ref(props.filters.q ?? '')
let t = null
watch(q, (val) => {
  clearTimeout(t)
  t = setTimeout(() => {
    const url = safeRoute('productos.index', { q: val })
    if (url) router.get(url, {}, { preserveState: true, replace: true })
  }, 400)
})

/** Acciones */
const eliminar = (id) => {
  const url = safeRoute('productos.destroy', id)
  if (!url) return
  if (!confirm('¿Eliminar este producto?')) return
  router.delete(url, { preserveScroll: true })
}

/** Formato dinero */
const money = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

/** Placeholder inline */
const PLACEHOLDER =
  'data:image/svg+xml;utf8,' +
  encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160">
      <rect width="100%" height="100%" rx="14" ry="14" fill="#EEF2FF"/>
      <g fill="#6366F1" opacity="0.5">
        <circle cx="80" cy="68" r="28"/>
        <rect x="36" y="106" width="88" height="28" rx="8"/>
      </g>
    </svg>`
  )

/** Resolver imagen */
function imgSrc(p) {
  const cand = p.imagenUrl ?? p.imagen_url ?? null
  return cand || PLACEHOLDER
}

/** Badge de stock */
function stockPillClass(stock) {
  if (stock == null) return 'bg-gray-100 text-gray-600 ring-1 ring-gray-200'
  if (Number(stock) < 10) return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
  return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
}
</script>

<template>
  <Head title="Productos" />

  <AuthenticatedLayout>
    <!-- ===== Header del layout (igual a Reportes) ===== -->
    <template #header>
  <div
    class="rounded-2xl border border-gray-200 bg-gradient-to-r from-indigo-50 to-white px-6 py-4 shadow-sm flex items-center justify-between"
  >
    <!-- IZQUIERDA -->
    <div class="flex items-center gap-4">
      <!-- Icono -->
      <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
        <!-- icono caja / productos -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
          <path d="M21 7.5V18a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V7.5l9-4.5 9 4.5zM12 5.19 6.75 7.8v2.7L12 7.88l5.25 2.62V7.8L12 5.19z"/>
        </svg>
      </div>

      <!-- Títulos -->
      <div>
        <h2 class="text-2xl font-semibold text-gray-900">Productos</h2>
        <p class="text-sm text-gray-500">
          Administra el catálogo y el estado del inventario.
        </p>
      </div>
    </div>

    <!-- DERECHA -->
    <div class="flex items-center gap-2">
      <!-- Volver -->
      <Link
        v-if="safeRoute('admin.dashboard')"
        :href="safeRoute('admin.dashboard')"
        class="text-sm text-indigo-600 hover:underline"
      >
        ← Volver al panel
      </Link>

      <!-- Nuevo producto -->
      <Link
        v-if="safeRoute('productos.create')"
        :href="safeRoute('productos.create')"
        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-white shadow-sm transition hover:bg-indigo-700"
      >
        <span class="-ml-1 text-lg">＋</span>
        Nuevo producto
      </Link>
    </div>
  </div>
</template>


    <!-- ===== Contenido ===== -->
    <div class="mx-auto max-w-7xl p-6">
      <!-- Flash -->
      <div
        v-if="flash?.success"
        class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/70 px-4 py-3 text-emerald-800"
      >
        {{ flash.success }}
      </div>

      <!-- Filtros -->
      <div class="mb-6">
        <div class="relative w-full md:w-[28rem]">
          <input
            v-model="q"
            type="text"
            placeholder="Buscar por nombre…"
            class="w-full rounded-xl border border-gray-300/80 bg-white px-4 py-2.5 pr-10 text-sm shadow-sm outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">🔍</span>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-4 py-3 text-left w-[56px]">#</th>
              <th class="px-4 py-3 text-left w-[84px]">Imagen</th>
              <th class="px-4 py-3 text-left">Nombre</th>
              <th class="px-4 py-3 text-left">Descripción</th>
              <th class="px-4 py-3 text-left">Precio</th>
              <th class="px-4 py-3 text-left">Stock</th>
              <th class="px-4 py-3 text-left">Activo</th>
              <th class="px-4 py-3 text-right w-[180px]">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(p, index) in productos.data"
              :key="p.id"
              class="border-t border-gray-100 odd:bg-white even:bg-gray-50/30 hover:bg-gray-50/80"
            >
              <td class="px-4 py-3 font-medium text-gray-500">
                {{ (productos.from ?? 1) + index }}
              </td>

              <!-- Miniatura -->
              <td class="px-4 py-3">
                <img
                  :src="imgSrc(p)"
                  alt="Producto"
                  class="h-12 w-12 rounded-lg border border-gray-200 bg-white object-cover shadow-sm"
                  @error="$event.target.src = PLACEHOLDER"
                />
              </td>

              <td class="px-4 py-3 font-medium text-gray-900">
                {{ p.nombre }}
                <span
                  v-if="Number(p.stock) < 10"
                  class="ml-2 align-middle rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-rose-200"
                >
                  Stock bajo
                </span>
              </td>

              <td class="px-4 py-3 text-gray-600 max-w-[28ch] truncate" :title="p.descripcion || 'Sin descripción'">
                {{ p.descripcion || '—' }}
              </td>

              <td class="px-4 py-3 font-semibold text-gray-800">
                {{ money(p.precio) }}
              </td>

              <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="stockPillClass(p.stock)">
                  {{ p.stock ?? '—' }}
                </span>
              </td>

              <td class="px-4 py-3">
                <span
                  :class="[
                    'rounded-full px-2.5 py-1 text-xs font-semibold ring-1',
                    p.activo
                      ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                      : 'bg-rose-50 text-rose-700 ring-rose-200',
                  ]"
                >
                  {{ p.activo ? 'Sí' : 'No' }}
                </span>
              </td>

              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <Link
                    v-if="safeRoute('productos.edit', p.id)"
                    :href="safeRoute('productos.edit', p.id)"
                    class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 px-3 py-1.5 text-indigo-700 hover:bg-indigo-50"
                  >
                    ✏️ <span class="text-sm font-medium">Editar</span>
                  </Link>

                  <button
                    v-if="safeRoute('productos.destroy', p.id)"
                    @click="eliminar(p.id)"
                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-rose-700 hover:bg-rose-50"
                  >
                    🗑 <span class="text-sm font-medium">Eliminar</span>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="productos.data.length === 0">
              <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                No hay productos disponibles.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-end gap-2">
        <Link
          v-for="(lnk, i) in productos.links"
          :key="i"
          :href="lnk.url || '#'"
          v-html="lnk.label"
          :class="[
            'min-w-9 select-none rounded-lg border px-3 py-1.5 text-center text-sm transition',
            lnk.active
              ? 'border-indigo-600 bg-indigo-600 text-white'
              : 'border-gray-300 bg-white text-gray-700 hover:bg-indigo-50',
            !lnk.url && 'pointer-events-none opacity-40',
          ]"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
