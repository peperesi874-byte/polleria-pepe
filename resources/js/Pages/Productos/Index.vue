<!-- resources/js/Pages/Admin/Productos/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const props = defineProps({
  productos: { type: Object, required: true },
  filters:   { type: Object, default: () => ({ q: '' }) },
})

const page  = usePage()
const flash = page.props.flash ?? {}

function safeRoute(name, params = {}, absolute = true) {
  try {
    if (typeof route !== 'undefined' && route().has(name)) {
      return route(name, params, absolute)
    }
  } catch {}
  return null
}

/* Búsqueda con debounce */
const q = ref(props.filters.q ?? '')
let t = null
watch(q, (val) => {
  clearTimeout(t)
  t = setTimeout(() => {
    const url = safeRoute('productos.index', { q: val })
    if (url) router.get(url, {}, { preserveState: true, replace: true })
  }, 400)
})

/* Acciones */
const eliminar = (id) => {
  const url = safeRoute('productos.destroy', id)
  if (!url) return
  if (!confirm('¿Eliminar este producto?')) return
  router.delete(url, { preserveScroll: true })
}

/* Formato dinero */
const money = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

/* Placeholder */
const PLACEHOLDER =
  'data:image/svg+xml;utf8,' +
  encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160">
      <rect width="100%" height="100%" rx="14" ry="14" fill="#FFEFD5"/>
      <g fill="#FB923C" opacity="0.7">
        <circle cx="80" cy="64" r="26"/>
        <rect x="36" y="104" width="88" height="30" rx="10"/>
      </g>
    </svg>`
  )

function imgSrc(p) {
  return p.imagenUrl ?? p.imagen_url ?? PLACEHOLDER
}

function stockPillClass(stock) {
  if (stock == null) return 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'
  if (Number(stock) < 10) return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
  return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
}

/* Pequeña etiqueta según stock */
const stockLabel = (stock) => {
  if (stock == null) return 'Sin dato'
  const n = Number(stock)
  if (n === 0) return 'Sin existencias'
  if (n < 5) return 'Crítico'
  if (n < 10) return 'Bajo'
  if (n < 30) return 'Saludable'
  return 'Abundante'
}

const totalProductos = computed(() => props.productos?.total ?? 0)
</script>

<template>
  <Head title="Productos" />

  <AuthenticatedLayout>
    <!-- ===== HEADER NARANJA ===== -->
    <template #header>
      <div class="pt-4 pb-6">
        <div
          class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
        >
          <!-- decor -->
          <div class="pointer-events-none absolute -left-20 -top-24 h-40 w-40 rounded-full bg-white/20 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-44 rounded-full bg-black/20 blur-3xl" />

          <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Izquierda -->
            <div class="space-y-3">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-white/95 backdrop-blur"
              >
                <span class="text-lg">📦</span>
                <span>Catálogo — Pollería Pepe</span>
              </div>

              <h1 class="text-3xl font-extrabold tracking-tight">Gestión de productos</h1>
              <p class="text-sm opacity-90">
                Administra tu catálogo, ajusta precios, stock y organiza todo tu inventario.
              </p>

              <div class="flex flex-wrap gap-2 text-[11px]">
                <span
                  class="inline-flex items-center gap-1 rounded-full bg-white/95 px-3 py-1 font-semibold text-amber-900 shadow-sm"
                >
                  {{ totalProductos }} productos totales
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-black/20 px-3 py-1">
                  Gestión rápida en un solo módulo
                </span>
              </div>
            </div>

            <!-- Derecha -->
            <div class="flex flex-col items-end gap-2">
              <Link
                :href="route('productos.create')"
                class="inline-flex items-center gap-2 rounded-2xl bg-white/95 px-4 py-2 text-xs font-semibold text-amber-700 shadow-sm hover:bg-amber-50"
              >
                ➕ Nuevo producto
              </Link>

              <Link
                :href="route('admin.dashboard')"
                class="inline-flex items-center gap-2 rounded-2xl bg-black/20 px-3 py-2 text-xs font-medium text-white hover:bg-black/30"
              >
                ← Volver al panel
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/20 to-slate-50">
      <div class="mx-auto max-w-7xl px-4 py-6 space-y-6 lg:px-6">
        <!-- Flash -->
        <div
          v-if="flash?.success"
          class="flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-900 shadow-sm"
        >
          <span>✅</span>
          <span>{{ flash.success }}</span>
        </div>

        <!-- Filtros / buscador -->
        <div
          class="flex flex-col gap-3 rounded-3xl bg-white/95 px-4 py-4 shadow-sm ring-1 ring-amber-200/80 md:flex-row md:items-center md:justify-between"
        >
          <div>
            <p class="flex items-center gap-2 text-sm font-semibold text-slate-900">
              <span class="text-lg">🔎</span>
              Catálogo
            </p>
            <p class="text-xs text-slate-500">
              Busca por nombre para ubicar rápido cortes o presentaciones.
            </p>
          </div>

          <div class="flex w-full flex-col items-stretch gap-2 md:w-auto md:flex-row md:items-center">
            <div class="relative md:w-[20rem]">
              <input
                v-model="q"
                type="text"
                placeholder="Buscar por nombre…"
                class="w-full rounded-2xl border border-amber-200 bg-amber-50/60 px-4 py-2 pr-9 text-sm shadow-inner shadow-amber-100 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300/80"
              />
              <span class="absolute inset-y-0 right-3 flex items-center text-amber-400">🔍</span>
            </div>
          </div>
        </div>

        <!-- ===== TABLA MEGA DISEÑADA ===== -->
        <div class="overflow-hidden rounded-[28px] bg-white/95 shadow-lg shadow-amber-100/80 ring-1 ring-amber-100">
          <!-- Encabezado de lista -->
          <div
            class="flex items-center justify-between border-b border-amber-100/80 bg-gradient-to-r from-amber-50 via-amber-50/80 to-white px-5 py-3"
          >
            <div class="flex items-center gap-3">
              <span
                class="flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-500 text-sm text-white shadow-sm shadow-amber-300/80"
              >
                🧺
              </span>
              <div>
                <p class="text-sm font-semibold text-slate-900">Listado de productos</p>
                <p class="text-[11px] text-amber-900/80">
                  Vista tipo tarjetas dentro de la tabla para entender mejor cada ítem.
                </p>
              </div>
            </div>
            <p class="hidden text-[11px] text-amber-900/80 md:block">
              Ordena, edita o elimina desde un solo lugar.
            </p>
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto px-3 py-3">
            <table class="min-w-full border-separate border-spacing-y-3 text-sm">
              <!-- Thead oscuro, pegajoso -->
              <thead>
                <tr
                  class="sticky top-0 z-10 bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-[11px] uppercase tracking-wide text-slate-100"
                >
                  <th class="px-4 py-3 text-left font-semibold rounded-tl-2xl">
                    #
                  </th>
                  <th class="px-4 py-3 text-left font-semibold">
                    Producto
                    <span class="block text-[10px] font-normal text-slate-300">
                      Nombre, imagen y descripción corta
                    </span>
                  </th>
                  <th class="px-4 py-3 text-right font-semibold">
                    Precio
                    <span class="block text-[10px] font-normal text-slate-300">
                      Monto actual de venta
                    </span>
                  </th>
                  <th class="px-4 py-3 text-center font-semibold">
                    Stock
                    <span class="block text-[10px] font-normal text-slate-300">
                      Cantidad y estado
                    </span>
                  </th>
                  <th class="px-4 py-3 text-center font-semibold">
                    Activo
                  </th>
                  <th class="px-4 py-3 text-right font-semibold rounded-tr-2xl">
                    Acciones
                  </th>
                </tr>
              </thead>

              <tbody>
                <!-- Fila / tarjeta -->
                <tr
                  v-for="(p, index) in productos.data"
                  :key="p.id"
                  class="group align-middle text-[13px] text-slate-800"
                >
                  <td class="px-0 py-0" colspan="7">
                    <!-- Card completa dentro de una celda para poder redondear -->
                    <div
                      class="flex items-stretch gap-3 rounded-3xl border border-amber-100/80 bg-white/95 px-4 py-3 shadow-[0_4px_14px_rgba(15,23,42,0.06)] ring-1 ring-amber-50/80 transition-all group-hover:-translate-y-0.5 group-hover:border-amber-300 group-hover:shadow-[0_10px_30px_rgba(249,115,22,0.25)]"
                    >
                      <!-- Barra lateral + # -->
                      <div class="flex flex-col items-center justify-between border-r border-dashed border-amber-100 pr-3 text-xs text-slate-500">
                        <div class="flex items-center gap-2">
                          <span
                            class="h-8 w-1.5 rounded-full bg-gradient-to-b from-amber-400 via-orange-400 to-rose-400"
                          />
                          <span class="font-semibold text-slate-700">
                            #{{ (productos.from ?? 1) + index }}
                          </span>
                        </div>
                        <span class="hidden rounded-full bg-amber-50 px-2 py-0.5 text-[10px] uppercase tracking-wide text-amber-700 md:inline-flex">
                          ID: {{ p.id }}
                        </span>
                      </div>

                      <!-- Producto (imagen + nombre + descripción) -->
                      <div class="flex flex-1 items-center gap-3">
                        <div
                          class="relative h-12 w-12 overflow-hidden rounded-xl border border-amber-100 bg-slate-100 shadow-sm"
                        >
                          <img
                            :src="imgSrc(p)"
                            class="h-full w-full object-cover"
                            @error="$event.target.src = PLACEHOLDER"
                          />
                          <span
                            class="absolute -left-1 -top-1 rounded-br-xl rounded-tl-xl bg-black/40 px-1.5 py-0.5 text-[10px] font-semibold text-amber-100 backdrop-blur"
                          >
                            {{ p.activo ? 'Activo' : 'Inactivo' }}
                          </span>
                        </div>
                        <div class="min-w-0">
                          <p class="truncate text-[14px] font-semibold text-slate-900">
                            {{ p.nombre }}
                          </p>
                          <p class="mt-0.5 line-clamp-2 text-[11px] text-slate-500">
                            {{ p.descripcion || 'Sin descripción' }}
                          </p>
                        </div>
                      </div>

          
                      <!-- Precio -->
                      <div class="flex w-32 flex-col items-end justify-center gap-1 text-right">
                        <span
                          class="text-[15px] font-extrabold"
                          :class="Number(p.stock) < 10 ? 'text-rose-700' : 'text-emerald-700'"
                        >
                          {{ money(p.precio) }}
                        </span>
                        <span class="text-[10px] uppercase tracking-wide text-slate-400">
                          Precio de venta
                        </span>
                      </div>

                      <!-- Stock -->
                      <div class="flex w-40 flex-col items-center justify-center gap-1 text-center">
                        <span
                          class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                          :class="stockPillClass(p.stock)"
                        >
                          📦
                          <span>{{ p.stock ?? '—' }}</span>
                        </span>
                        <span class="text-[10px] text-slate-400">
                          {{ stockLabel(p.stock) }}
                        </span>
                      </div>

                      <!-- Activo -->
                      <div class="flex w-24 flex-col items-center justify-center gap-1">
                        <span
                          class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1"
                          :class="p.activo
                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                            : 'bg-rose-50 text-rose-700 ring-rose-200'"
                        >
                          <span
                            class="h-1.5 w-1.5 rounded-full"
                            :class="p.activo ? 'bg-emerald-500' : 'bg-rose-500'"
                          />
                          {{ p.activo ? 'Sí' : 'No' }}
                        </span>
                        <span class="text-[10px] text-slate-400">
                          Visible en catálogo
                        </span>
                      </div>

                      <!-- Acciones -->
                      <div class="flex w-40 items-center justify-end gap-1">
                        <Link
                          :href="safeRoute('productos.edit', p.id)"
                          class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-amber-700 shadow-sm hover:bg-amber-50"
                        >
                          ✏️ Editar
                        </Link>
                        <button
                          @click="eliminar(p.id)"
                          class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-rose-700 shadow-sm hover:bg-rose-50"
                        >
                          🗑 Eliminar
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>

                <tr v-if="productos.data.length === 0">
                  <td colspan="7" class="px-3 py-8 text-center text-sm text-slate-500">
                    No hay productos disponibles.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginación -->
        <div class="flex justify-end gap-2">
          <Link
            v-for="(lnk, i) in productos.links"
            :key="i"
            :href="lnk.url || '#'"
            v-html="lnk.label"
            :class="[
              'min-w-9 select-none rounded-full border px-3 py-1.5 text-center text-xs font-semibold transition',
              lnk.active
                ? 'border-amber-600 bg-amber-600 text-white shadow-sm shadow-amber-300/80'
                : 'border-amber-100 bg-white text-slate-700 hover:bg-amber-50',
              !lnk.url && 'pointer-events-none opacity-40',
            ]"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
