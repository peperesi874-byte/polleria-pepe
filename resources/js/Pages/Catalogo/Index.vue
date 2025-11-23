<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { route } from 'ziggy-js' // 👈 necesario para route('...')

import CatalogLayout from '@/Layouts/CatalogLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import ProductQuickView from '@/Components/ProductQuickView.vue'

function scrollAProductos () {
  const el = document.getElementById('grid-productos')
  el?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function compactParams(obj) {
  const o = { ...obj }
  Object.keys(o).forEach(k => (o[k] === null || o[k] === '' || o[k] === undefined) && delete o[k])
  return o
}

const page = usePage()
const productos = computed(() => page.props.productos ?? { data: [], links: [] })
const items     = computed(() => productos.value?.data  ?? [])
const links     = computed(() => productos.value?.links ?? [])

const filters = ref({
  q:    page.props.filters?.q    ?? '',
  min:  page.props.filters?.min  ?? null,
  max:  page.props.filters?.max  ?? null,
  sort: page.props.filters?.sort ?? 'nombre',
  dir:  page.props.filters?.dir  ?? 'asc',
})

watch(() => page.props.filters, f => {
  if (!f) return
  filters.value = {
    q:    f.q    ?? '',
    min:  f.min  ?? null,
    max:  f.max  ?? null,
    sort: f.sort ?? 'nombre',
    dir:  f.dir  ?? 'asc',
  }
})

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

// 🔹 NUEVO: función para agregar un producto al carrito de cliente
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
        // Mensaje sencillo al usuario
        alert('Producto añadido al carrito')
      },
    }
  )
}


// Si quieres, luego podemos usar esto para que el quick view también agregue al carrito
function addFromQuick(p) {
  if (p) {
    addToCart(p)
  }
  quickOpen.value = false
}
</script>

<template>
  <CatalogLayout>
    <!-- HERO -->
    <section class="bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 grid gap-8 lg:grid-cols-2 items-center">
        <div>
          <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight text-neutral-900">
            Pollo fresco, <span class="text-amber-600">calidad Premium</span>
          </h1>
          <p class="mt-4 text-neutral-600 max-w-prose">
            Descubre nuestro catálogo: piezas, cortes y presentaciones para cada receta.
          </p>
          <div class="mt-6 flex flex-wrap gap-3">
            <button
              type="button"
              @click="scrollAProductos"
              class="rounded-xl bg-amber-500 px-5 py-2.5 font-semibold text-white shadow hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400"
            >
              Ver productos
            </button>
          </div>
        </div>
        <div class="hidden lg:block">
          <img :src="'/logo.jpg'" alt="Catálogo Pollería Pepe" width="840" height="520"
               loading="eager" decoding="async"
               class="w-full h-auto max-h-[420px] object-contain rounded-2xl border border-neutral-200 shadow-sm" />
        </div>
      </div>
    </section>

    <!-- Filtros -->
    <section class="border-b border-neutral-200 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
          <label class="text-sm text-neutral-600">Ordenar por:</label>
          <select v-model="sortUI" @change="aplicarFiltros"
                  class="rounded-lg border border-neutral-300 px-3 py-1.5 text-sm bg-white">
            <option value="nombre_asc">Nombre (A–Z)</option>
            <option value="precio_asc">Precio (menor a mayor)</option>
            <option value="precio_desc">Precio (mayor a menor)</option>
            <option value="recientes">Más recientes</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <label class="text-sm text-neutral-600">Mín:</label>
          <input v-model.number="filters.min" type="number" min="0" step="1"
                 class="w-28 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm" />
        </div>
        <div class="flex items-center gap-2">
          <label class="text-sm text-neutral-600">Máx:</label>
          <input v-model.number="filters.max" type="number" min="0" step="1"
                 class="w-28 rounded-lg border border-neutral-300 px-3 py-1.5 text-sm" />
        </div>

        <button class="ml-auto rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm font-medium text-neutral-700 hover:border-amber-500 hover:text-amber-600"
                @click="aplicarFiltros">
          Aplicar filtros
        </button>
      </div>
    </section>

    <!-- Grid -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <h2 class="sr-only">Productos</h2>

      <div v-if="!hayResultados" class="text-center text-neutral-500 py-24">
        No encontramos productos con los filtros actuales.
      </div>

      <div
        id="grid-productos"
        v-else
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
      >
        <ProductCard
          v-for="p in items"
          :key="p.id"
          :producto="p"
          @add="() => addToCart(p)"
          @preview="openPreview(p)"
        />
      </div>

      <div v-if="links.length" class="mt-10 flex flex-wrap items-center justify-center gap-2">
        <template v-for="(link, i) in links" :key="i">
          <button v-if="link.url"
                  class="rounded-lg border px-3 py-1.5 text-sm"
                  :class="link.active
                    ? 'border-amber-500 text-amber-600 font-semibold'
                    : 'border-neutral-300 text-neutral-700 hover:border-amber-400 hover:text-amber-600'"
                  v-html="link.label"
                  @click="router.visit(link.url, { preserveScroll: true })" />
          <span v-else class="px-3 py-1.5 text-sm text-neutral-400" v-html="link.label" />
        </template>
      </div>
    </section>

    <ProductQuickView
      :open="quickOpen"
      :product="quickItem"
      @close="closePreview"
      @add="addFromQuick"
    />
  </CatalogLayout>
</template>
