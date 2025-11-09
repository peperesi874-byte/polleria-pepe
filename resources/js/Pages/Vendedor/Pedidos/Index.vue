<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  pedidos: { type: Object, required: true },
  q: String,
  estado: String,
  asignado: String,
  estados: { type: Array, default: () => [] },
})

const base = 'vendedor'

// Formato de dinero
const money = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

// Filtros
const buscador = ref(props.q ?? '')
const filtroEstado = ref(props.estado ?? '')
const filtroAsignado = ref(props.asignado ?? '')

function buildQuery() {
  const p = new URLSearchParams()
  if (buscador.value) p.set('q', buscador.value)
  if (filtroEstado.value) p.set('estado', filtroEstado.value)
  if (filtroAsignado.value) p.set('asignado', filtroAsignado.value)
  const qs = p.toString()
  return qs ? `?${qs}` : ''
}

function applyFilters() {
  router.visit(`/${base}/pedidos${buildQuery()}`, {
    preserveScroll: true,
    preserveState: true,
  })
}

function onEnter(e) {
  if (e.key === 'Enter') {
    e.preventDefault()
    applyFilters()
  }
}

function clearFilters() {
  buscador.value = ''
  filtroEstado.value = ''
  filtroAsignado.value = ''
  applyFilters()
}
</script>

<template>
  <div class="max-w-7xl mx-auto p-6 space-y-6">
    <header class="flex items-end justify-between flex-wrap gap-3">
      <div>
        <h1 class="text-2xl md:text-3xl font-semibold text-indigo-800">Pedidos</h1>
        <p class="text-sm text-neutral-500">Panel del vendedor</p>
      </div>

      <!-- Filtros -->
      <div class="flex flex-wrap items-center gap-3">
        <input
          v-model="buscador"
          @keydown="onEnter"
          placeholder="Buscar folio / observaciones…"
          class="w-full sm:w-80 rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />

        <select
          v-model="filtroEstado"
          class="w-52 rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">Todos los estados</option>
          <option v-for="e in estados" :key="e" :value="e">{{ e.replace('_',' ') }}</option>
        </select>

        <select
          v-model="filtroAsignado"
          class="w-52 rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="">Asignados: todos</option>
          <option value="none">Sin asignar</option>
          <option value="any">Con asignación</option>
        </select>

        <button
          @click="applyFilters"
          class="rounded-lg bg-indigo-600 text-white px-4 py-2 text-sm font-medium hover:bg-indigo-700"
        >
          Filtrar
        </button>

        <button
          @click="clearFilters"
          class="rounded-lg border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          Limpiar
        </button>
      </div>
    </header>

    <!-- Tabla -->
    <div class="overflow-hidden rounded-xl border bg-white">
      <table class="w-full text-sm">
        <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
          <tr>
            <th class="px-3 py-3 text-left w-10">#</th>
            <th class="px-3 py-3 text-left">Folio</th>
            <th class="px-3 py-3 text-left">Estado</th>
            <th class="px-3 py-3 text-left">Tipo</th>
            <th class="px-3 py-3 text-left">Items</th>
            <th class="px-3 py-3 text-left">Asignado</th>
            <th class="px-3 py-3 text-left">Total</th>
            <th class="px-3 py-3 text-left">Creado</th>
            <th class="px-3 py-3"></th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(p, i) in pedidos.data" :key="p.id" class="border-t hover:bg-gray-50">
            <td class="px-3 py-3">{{ (pedidos.from ?? 1) + i }}</td>
            <td class="px-3 py-3">{{ p.folio || '—' }}</td>

            <td class="px-3 py-3">
              <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="{
                  'bg-gray-100 text-gray-800': p.estado==='pendiente',
                  'bg-amber-100 text-amber-800': p.estado==='preparando',
                  'bg-blue-100 text-blue-800': p.estado==='listo' || p.estado==='en_camino',
                  'bg-green-100 text-green-800': p.estado==='entregado',
                  'bg-rose-100 text-rose-800': p.estado==='cancelado'
                }"
              >
                {{ p.estado.replace('_',' ') }}
              </span>
            </td>

            <td class="px-3 py-3 capitalize">{{ p.tipo?.replace('_',' ') }}</td>
            <td class="px-3 py-3">{{ p.items }}</td>

            <td class="px-3 py-3">
              <span v-if="p.repartidor" class="inline-flex rounded-full bg-indigo-100 text-indigo-800 px-2.5 py-0.5 text-xs">
                {{ p.repartidor }}
              </span>
              <span v-else class="text-gray-400">—</span>
            </td>

            <td class="px-3 py-3 font-semibold">{{ money(p.total) }}</td>
            <td class="px-3 py-3">{{ p.created_at || '—' }}</td>

            <td class="px-3 py-3 text-right">
              <Link
                :href="route('vendedor.pedidos.show', p.id)"
                class="text-indigo-600 hover:text-indigo-800 font-medium"
              >
                Ver detalle →
              </Link>
            </td>
          </tr>

          <tr v-if="!pedidos.data?.length">
            <td colspan="9" class="px-3 py-8 text-center text-gray-500">
              Sin resultados con los filtros actuales.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <nav v-if="pedidos.links?.length" class="flex flex-wrap gap-2 justify-end">
      <Link
        v-for="l in pedidos.links"
        :key="(l.url || '') + l.label"
        :href="l.url || '#'"
        v-html="l.label"
        class="px-3 py-1.5 rounded border text-sm"
        :class="[
          l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'text-neutral-700 hover:bg-neutral-100',
          !l.url && 'opacity-40 pointer-events-none'
        ]"
        preserve-scroll
        preserve-state
      />
    </nav>
  </div>
</template>
