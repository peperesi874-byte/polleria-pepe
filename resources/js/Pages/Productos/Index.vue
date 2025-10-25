<script setup>
import { ref, watch } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  productos: { type: Object, required: true }, // paginación (data, links, from, etc.)
  filters:   { type: Object, default: () => ({ q: '' }) },
})

const page  = usePage()
const flash = page.props.flash ?? {}

// -------------------- helper seguro para Ziggy --------------------
function safeRoute(name, params = {}, absolute = true) {
  try {
    if (typeof route !== 'undefined' && route().has(name)) {
      return route(name, params, absolute)
    }
  } catch (e) {}
  return null
}

// -------------------- buscador --------------------
const q = ref(props.filters.q ?? '')
let t = null
watch(q, (val) => {
  clearTimeout(t)
  t = setTimeout(() => {
    const url = safeRoute('productos.index', { q: val })
    if (url) router.get(url, {}, { preserveState: true, replace: true })
  }, 400)
})

// -------------------- acciones --------------------
const eliminar = (id) => {
  const url = safeRoute('productos.destroy', id)
  if (!url) return
  if (!confirm('¿Eliminar este producto?')) return
  router.delete(url, { preserveScroll: true })
}

const money = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

// (opcional) placeholder local si no hay imagen.
// crea este archivo si quieres usarlo.
const PLACEHOLDER = '/images/placeholder-product.png'
</script>

<template>
  <div class="max-w-6xl mx-auto px-6 py-8">
    <!-- Flash -->
    <div v-if="flash?.success" class="mb-4 rounded-md bg-green-50 text-green-800 px-4 py-3">
      {{ flash.success }}
    </div>

    <!-- Título + CTA -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-3xl font-semibold text-indigo-800">🐔 Productos</h1>

      <!-- Botón solo si Ziggy tiene la ruta -->
      <Link
        v-if="safeRoute('productos.create')"
        :href="safeRoute('productos.create')"
        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition"
      >
        Nuevo producto
      </Link>
    </div>

    <!-- Buscador -->
    <div class="mb-6">
      <input
        v-model="q"
        type="text"
        placeholder="🔍 Buscar por nombre..."
        class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"
      />
    </div>

    <!-- Tabla -->
    <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm bg-white">
      <table class="w-full border-collapse text-sm">
        <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-left w-16">#</th>
            <th class="px-4 py-3 text-left">Imagen</th>
            <th class="px-4 py-3 text-left">Nombre</th>
            <th class="px-4 py-3 text-left">Descripción</th>
            <th class="px-4 py-3 text-left">Precio</th>
            <th class="px-4 py-3 text-left">Stock</th>
            <th class="px-4 py-3 text-left">Activo</th>
            <th class="px-4 py-3 text-right">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(p, index) in productos.data" :key="p.id" class="hover:bg-gray-50 transition">
            <td class="px-4 py-3 font-medium text-gray-600">
              {{ (productos.from ?? 1) + index }}
            </td>

            <!-- Miniatura (usa imagenUrl si viene, o /storage/imagen, o placeholder) -->
            <td class="px-4 py-3">
              <img
                :src="p.imagenUrl || (p.imagen ? `/storage/${p.imagen}` : PLACEHOLDER)"
                alt="Producto"
                class="h-10 w-10 object-cover rounded-md border bg-white"
              />
            </td>

            <td class="px-4 py-3 text-gray-800 font-medium">{{ p.nombre }}</td>

            <td class="px-4 py-3 text-gray-600 max-w-xs truncate" :title="p.descripcion || 'Sin descripción'">
              {{ p.descripcion || '—' }}
            </td>

            <td class="px-4 py-3 text-gray-700">{{ money(p.precio) }}</td>

            <td class="px-4 py-3 font-semibold" :class="p.stock < 10 ? 'text-red-600' : 'text-gray-700'">
              {{ p.stock }}
              <span v-if="p.stock < 10" class="ml-1 text-xs text-red-500">⚠ Stock bajo</span>
            </td>

            <td class="px-4 py-3">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  p.activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700',
                ]"
              >
                {{ p.activo ? 'Sí' : 'No' }}
              </span>
            </td>

            <td class="px-4 py-3 text-right space-x-3">
              <Link
                v-if="safeRoute('productos.edit', p.id)"
                :href="safeRoute('productos.edit', p.id)"
                class="text-indigo-600 hover:text-indigo-700 font-medium text-sm"
              >
                ✏️ Editar
              </Link>

              <button
                v-if="safeRoute('productos.destroy', p.id)"
                @click="eliminar(p.id)"
                class="text-red-600 hover:text-red-700 font-medium text-sm"
              >
                🗑 Eliminar
              </button>
            </td>
          </tr>

          <tr v-if="productos.data.length === 0">
            <td colspan="8" class="px-4 py-6 text-center text-gray-500">
              No hay productos disponibles.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div class="flex justify-end mt-5 space-x-2">
      <Link
        v-for="(lnk, i) in productos.links"
        :key="i"
        :href="lnk.url || '#'"
        v-html="lnk.label"
        :class="[
          'px-3 py-1 rounded-md border text-sm transition',
          lnk.active
            ? 'bg-indigo-600 text-white border-indigo-600'
            : 'bg-white text-gray-700 border-gray-300 hover:bg-indigo-50',
          !lnk.url && 'pointer-events-none opacity-40',
        ]"
      />
    </div>
  </div>
</template>
