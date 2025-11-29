<!-- resources/js/Pages/Catalogo/Index.vue -->
<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

import CatalogLayout from '@/Layouts/CatalogLayout.vue'
import ProductQuickView from '@/Components/ProductQuickView.vue'

/* ----------------- Helpers generales ----------------- */
function scrollAProductos () {
  const el = document.getElementById('grid-productos')
  el?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function compactParams(obj) {
  const o = { ...obj }
  Object.keys(o).forEach(k => (o[k] === null || o[k] === '' || o[k] === undefined) && delete o[k])
  return o
}

/* ----------------- Datos desde Inertia ----------------- */
const page = usePage()
const productos = computed(() => page.props.productos ?? { data: [], links: [] })
const items     = computed(() => productos.value?.data  ?? [])
const links     = computed(() => productos.value?.links ?? [])

/* ----------------- Filtros ----------------- */
const filters = ref({
  q:    page.props.filters?.q    ?? '',
  min:  page.props.filters?.min  ?? null,
  max:  page.props.filters?.max  ?? null,
  sort: page.props.filters?.sort ?? 'nombre',
  dir:  page.props.filters?.dir  ?? 'asc',
})

watch(
  () => page.props.filters,
  f => {
    if (!f) return
    filters.value = {
      q:    f.q    ?? '',
      min:  f.min  ?? null,
      max:  f.max  ?? null,
      sort: f.sort ?? 'nombre',
      dir:  f.dir  ?? 'asc',
    }
  }
)

/* Selector “bonito” para el ordenamiento */
const sortUI = computed({
  get() {
    if (filters.value.sort === 'recientes') return 'recientes'
    if (filters.value.sort === 'precio' && filters.value.dir === 'asc')  return 'precio_asc'
    if (filters.value.sort === 'precio' && filters.value.dir === 'desc') return 'precio_desc'
    return 'nombre_asc'
  },
  set(v) {
    if (v === 'recientes') {
      filters.value.sort = 'recientes'
      filters.value.dir  = 'desc'
    } else if (v === 'precio_asc') {
      filters.value.sort = 'precio'
      filters.value.dir  = 'asc'
    } else if (v === 'precio_desc') {
      filters.value.sort = 'precio'
      filters.value.dir  = 'desc'
    } else {
      filters.value.sort = 'nombre'
      filters.value.dir  = 'asc'
    }
  }
})

function aplicarFiltros () {
  router.get(route('catalogo.index'), compactParams(filters.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function limpiarFiltros () {
  filters.value.q    = ''
  filters.value.min  = null
  filters.value.max  = null
  sortUI.value       = 'nombre_asc'
  aplicarFiltros()
}

/* ----------------- Quick view / estado ----------------- */
const hayResultados = computed(() => items.value.length > 0)

const quickOpen = ref(false)
const quickItem = ref(null)

function openPreview(p) {
  quickItem.value = p
  quickOpen.value = true
}

function closePreview() {
  quickOpen.value = false
}

/* -------- Carrusel simple del héroe (solo imágenes) -------- */
const currentIndex = ref(0)

const hayProductosHero = computed(() => items.value.length > 0)

const currentHeroProduct = computed(() =>
  hayProductosHero.value ? items.value[currentIndex.value] : null
)

watch(items, () => {
  currentIndex.value = 0
})

function nextHero() {
  if (!hayProductosHero.value) return
  currentIndex.value = (currentIndex.value + 1) % items.value.length
}

let heroTimer = null

onMounted(() => {
  if (!hayProductosHero.value) return
  heroTimer = setInterval(() => {
    nextHero()
  }, 2000) // cambia cada 2 segundos
})

onBeforeUnmount(() => {
  if (heroTimer) clearInterval(heroTimer)
})

/* ----------------- Carrito ----------------- */
function addToCart(producto) {
  if (!producto?.id) return

  router.post(
    route('cliente.carrito.add'),
    {
      producto_id: producto.id,
      qty: 1,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        alert('Producto añadido al carrito')
      },
    }
  )
}

function addFromQuick(p) {
  if (p) addToCart(p)
  quickOpen.value = false
}

/* ----------------- Helpers UI ----------------- */
const fmtMoney = n =>
  new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
  }).format(Number(n ?? 0))

const imgSrc = (p) => {
  if (!p) return '/placeholder-producto.png'

  // misma propiedad que usa el modal
  if (p.imagenUrl) return p.imagenUrl

  // por si en el futuro agregas otras variantes
  if (p.imagen_catalogo_url) return p.imagen_catalogo_url
  if (p.imagen_detalle_url)  return p.imagen_detalle_url
  if (p.imagen_url)          return p.imagen_url
  if (p.imagen)              return `/storage/${p.imagen}`

  return '/placeholder-producto.png'
}
</script>

<template>
  <CatalogLayout>
    <!-- ================= HERO ================= -->
    <section class="bg-gradient-to-b from-amber-50 via-white to-rose-50/40">
      <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 grid gap-10 lg:grid-cols-[1.15fr,0.9fr] items-center"
      >
        <!-- IZQUIERDA -->
        <div class="space-y-5">
          <div class="inline-flex items-center gap-2 rounded-full bg-amber-100/80 px-3 py-1 text-[11px] font-semibold text-amber-800">
            <span class="text-lg">🍗</span>
            <span>Catálogo en línea — Pollería Pepe</span>
          </div>

          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-neutral-900">
            Pollo fresco,
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500">
              calidad premium
            </span>
          </h1>

          <p class="text-sm sm:text-base text-neutral-600 max-w-prose">
            Explora todas las piezas, cortes y presentaciones. Diseñado para que
            elijas rápido lo que necesitas para tu negocio o para tu cocina.
          </p>

          <div class="flex flex-wrap items-center gap-3 pt-2">
            <button
              type="button"
              @click="scrollAProductos"
              class="inline-flex items-center gap-2 rounded-2xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-300/70 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1"
            >
              <span>Ver productos</span>
              <span>↓</span>
            </button>

            <span class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1.5 text-xs text-neutral-600 shadow-sm border border-neutral-200">
              <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
              {{ items.length }} productos disponibles
            </span>
          </div>
        </div>

        <!-- DERECHA: carrusel decorativo solo de imágenes -->
        <div
          class="relative rounded-3xl bg-white/90 ring-1 ring-amber-100 shadow-[0_18px_40px_rgba(248,186,92,0.4)] overflow-hidden"
        >
          <div class="absolute -left-10 -top-10 h-32 w-32 rounded-full bg-amber-300/30 blur-3xl" />
          <div class="absolute -right-10 -bottom-10 h-32 w-32 rounded-full bg-rose-300/25 blur-3xl" />

          <div v-if="hayProductosHero && currentHeroProduct" class="relative p-4 space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">
                Catálogo Pollería Pepe
              </p>
              <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-800">
                {{ currentIndex + 1 }} / {{ items.length }}
              </span>
            </div>

            <!-- Solo la imagen del producto -->
            <div class="relative rounded-2xl overflow-hidden border border-amber-100 bg-neutral-50">
              <img
  :src="imgSrc(currentHeroProduct)"
  :alt="currentHeroProduct?.nombre"
  class="mx-auto h-56 w-auto object-contain drop-shadow-md rounded-xl"
/>

            </div>

            <!-- Puntos de posición -->
            <div class="flex justify-center gap-1 pt-1">
              <span
                v-for="(p, i) in items"
                :key="p.id"
                class="h-1.5 w-5 rounded-full"
                :class="i === currentIndex ? 'bg-amber-500' : 'bg-neutral-300/80'"
              />
            </div>
          </div>

          <!-- Si no hay productos -->
          <div v-else class="relative p-6 text-center text-sm text-neutral-500">
            Próximamente catálogo de productos.
          </div>
        </div>
      </div>
    </section>

    <!-- ================= FILTROS ================= -->
    <section class="border-y border-neutral-200 bg-white/95">
      <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap gap-4 items-end"
      >
        <!-- Buscador -->
        <div class="flex-1 min-w-[220px]">
          <label class="block text-xs font-semibold text-neutral-600 mb-1">
            Buscar producto
          </label>
          <div class="relative">
            <input
              v-model="filters.q"
              type="search"
              placeholder="Nombre, descripción…"
              class="w-full rounded-2xl border border-neutral-300 px-4 py-2.5 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
              @keyup.enter="aplicarFiltros"
            />
            <span
              class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-neutral-400"
            >
              🔍
            </span>
          </div>
        </div>

        <!-- Orden -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-neutral-600">
            Ordenar por
          </label>
          <select
            v-model="sortUI"
            class="rounded-2xl border border-neutral-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
          >
            <option value="nombre_asc">Nombre (A–Z)</option>
            <option value="precio_asc">Precio: menor a mayor</option>
            <option value="precio_desc">Precio: mayor a menor</option>
            <option value="recientes">Más recientes</option>
          </select>
        </div>

        <!-- Min -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-neutral-600">
            Precio mínimo
          </label>
          <input
            v-model.number="filters.min"
            type="number"
            min="0"
            step="1"
            class="w-28 rounded-2xl border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
          />
        </div>

        <!-- Max -->
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-neutral-600">
            Precio máximo
          </label>
          <input
            v-model.number="filters.max"
            type="number"
            min="0"
            step="1"
            class="w-28 rounded-2xl border border-neutral-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
          />
        </div>

        <!-- Botones -->
        <div class="flex flex-wrap gap-2 ml-auto">
          <button
            type="button"
            class="rounded-2xl border border-neutral-300 bg-white px-4 py-2 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 hover:border-amber-400"
            @click="limpiarFiltros"
          >
            Limpiar filtros
          </button>
          <button
            type="button"
            class="rounded-2xl bg-amber-500 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-amber-600"
            @click="aplicarFiltros"
          >
            Aplicar filtros
          </button>
        </div>
      </div>
    </section>

    <!-- ================= GRID ================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h2 class="sr-only">Listado de productos</h2>

      <div
        v-if="!hayResultados"
        class="py-20 text-center text-neutral-500 text-sm"
      >
        No encontramos productos con los filtros actuales.
      </div>

      <div
        v-else
        id="grid-productos"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-7"
      >
        <article
          v-for="p in items"
          :key="p.id"
          class="group relative flex flex-col rounded-3xl border border-neutral-200/80 bg-white/95 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all overflow-hidden"
        >
          <!-- Imagen -->
          <div
class="mx-auto h-40 w-auto object-contain transition-transform duration-200 group-hover:scale-105 rounded-xl"
  @click="openPreview(p)"
>
  <img
    :src="imgSrc(p)"
    :alt="p.nombre"
    class="mx-auto h-40 w-auto object-contain transition group-hover:scale-[1.03] rounded-xl"
  />
            <div
              class="absolute inset-x-0 bottom-0 h-14 bg-gradient-to-t from-black/50 via-black/10 to-transparent"
            ></div>

            <div
              class="absolute bottom-3 left-3 inline-flex items-center rounded-full bg-black/60 px-3 py-1 text-[11px] text-amber-50 gap-1"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
              <span>Disponible</span>
            </div>
          </div>

          <!-- Contenido -->
          <div class="flex-1 p-4 flex flex-col gap-3">
            <div>
              <h3
                class="text-sm font-semibold text-neutral-900 line-clamp-1"
              >
                {{ p.nombre }}
              </h3>
              <p class="mt-0.5 text-xs text-neutral-500 line-clamp-2">
                {{ p.descripcion || 'Producto de catálogo.' }}
              </p>
            </div>

            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <p class="text-[11px] uppercase tracking-wide text-neutral-400">
                  Precio
                </p>
                <p class="text-lg font-bold text-amber-600">
                  {{ fmtMoney(p.precio) }}
                </p>
              </div>

              <button
                type="button"
                @click="openPreview(p)"
                class="inline-flex items-center justify-center rounded-full border border-neutral-200 px-3 py-1.5 text-[11px] font-semibold text-neutral-700 hover:bg-neutral-50"
              >
                Vista rápida
              </button>
            </div>
          </div>

          <!-- Footer acciones -->
          <div
            class="px-4 pb-3 pt-2 border-t border-neutral-100 bg-neutral-50/60 flex items-center justify-between gap-2"
          >
            <span class="text-[11px] text-neutral-500">
              ID: {{ p.id }}
            </span>
            <button
              type="button"
              @click="addToCart(p)"
              class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-600"
            >
              <span>Agregar</span>
              <span>＋</span>
            </button>
          </div>
        </article>
      </div>

      <!-- Paginación -->
      <div
        v-if="links.length"
        class="mt-10 flex flex-wrap items-center justify-center gap-2"
      >
        <template v-for="(link, i) in links" :key="i">
          <button
            v-if="link.url"
            class="rounded-full border px-3 py-1.5 text-xs font-semibold"
            :class="link.active
              ? 'border-amber-600 bg-amber-600 text-white shadow-sm shadow-amber-300/80'
              : 'border-neutral-300 bg-white text-neutral-700 hover:border-amber-400 hover:text-amber-600'"
            v-html="link.label"
            @click="router.visit(link.url, { preserveScroll: true })"
          />
          <span
            v-else
            class="px-3 py-1.5 text-xs text-neutral-400"
            v-html="link.label"
          />
        </template>
      </div>
    </section>

    <!-- Quick view -->
    <ProductQuickView
      :open="quickOpen"
      :product="quickItem"
      @close="closePreview"
      @add="addFromQuick"
    />
  </CatalogLayout>
</template>
