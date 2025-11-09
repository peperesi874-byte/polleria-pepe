<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  role: { type: String, default: 'admin' },   // 'admin' | 'vendedor'
  pedidos: { type: Object, required: true },  // paginator (con links)
  q: String,
  estado: String,
  asignado: String,
  estados: { type: Array, default: () => [] },
})

/* Base por rol (sin Ziggy) */
const base = computed(() => (props.role === 'vendedor' ? 'vendedor' : 'admin'))

/* URL del form (GET clásico) */
const formAction = computed(() => `/${base.value}/pedidos`)

/* Filtros (con valores iniciales) */
const buscador       = ref(props.q ?? '')
const filtroEstado   = ref(props.estado ?? '')
const filtroAsignado = ref(props.asignado ?? '')

/* Autosubmit con debounce para el buscador y selects */
const formRef = ref(null)
let t = null
function autoSubmit() {
  clearTimeout(t)
  t = setTimeout(() => {
    formRef.value?.submit()
  }, 300)
}

/* Formato de moneda */
const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <!-- Encabezado -->
    <header class="flex items-end justify-between flex-wrap gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-semibold text-indigo-800">Pedidos</h1>
        <p class="text-sm text-neutral-500">
          Gestión y seguimiento de pedidos del sistema.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <!-- ← Volver al panel -->
        <Link
          :href="`/${base}/dashboard`"
          class="text-indigo-600 hover:text-indigo-700 text-sm"
        >
          ← Volver al panel
        </Link>
      </div>
    </header>

    <!-- Filtros -->
    <form
      ref="formRef"
      :action="formAction"
      method="GET"
      class="flex flex-wrap items-center gap-3"
    >
      <!-- Buscar -->
      <div class="relative">
        <span
          class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
          aria-hidden="true"
        >🔎</span>
        <input
          name="q"
          v-model="buscador"
          type="search"
          inputmode="search"
          placeholder="Buscar por folio u observaciones…"
          class="w-[280px] md:w-[340px] border rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @input="autoSubmit"
          @keydown.enter.prevent="autoSubmit"
          aria-label="Buscar por folio u observaciones"
        />
      </div>

      <!-- Estado -->
      <div class="relative">
        <select
          name="estado"
          v-model="filtroEstado"
          class="w-[220px] appearance-none pr-9 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @change="autoSubmit"
          aria-label="Filtrar por estado"
        >
          <option value="">Todos los estados</option>
          <option v-for="e in estados" :key="e" :value="e">
            {{ e.replace('_',' ') }}
          </option>
        </select>
        <svg
          class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500"
          xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
        >
          <path fill-rule="evenodd"
            d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
            clip-rule="evenodd" />
        </svg>
      </div>

      <!-- Asignación -->
      <div class="relative">
        <select
          name="asignado"
          v-model="filtroAsignado"
          class="w-[220px] appearance-none pr-9 border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @change="autoSubmit"
          aria-label="Filtrar por asignación"
        >
          <option value="">Asignados: todos</option>
          <option value="none">Sin asignar</option>
          <option value="any">Asignados</option>
        </select>
        <svg
          class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500"
          xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
        >
          <path fill-rule="evenodd"
            d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
            clip-rule="evenodd" />
        </svg>
      </div>

      <button
        type="submit"
        class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm hover:bg-indigo-700"
      >
        Filtrar
      </button>
    </form>

    <!-- Tabla -->
    <div class="overflow-hidden rounded-xl border bg-white">
      <table class="w-full text-sm">
        <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
          <tr>
            <th class="px-4 py-3 text-left">#</th>
            <th class="px-4 py-3 text-left">Folio</th>
            <th class="px-4 py-3 text-left">Estado</th>
            <th class="px-4 py-3 text-left">Tipo</th>
            <th class="px-4 py-3 text-left">Items</th>
            <th class="px-4 py-3 text-left">Asignado a</th>
            <th class="px-4 py-3 text-left">Total</th>
            <th class="px-4 py-3 text-left">Creado</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in pedidos.data" :key="p.id" class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ p.id }}</td>
            <td class="px-4 py-3">{{ p.folio ?? '—' }}</td>
            <td class="px-4 py-3">
              <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                :class="{
                  'bg-rose-100 text-rose-800': p.estado === 'cancelado',
                  'bg-emerald-100 text-emerald-800': p.estado === 'entregado',
                  'bg-amber-100 text-amber-800': p.estado === 'preparando' || p.estado === 'listo',
                  'bg-indigo-100 text-indigo-800': p.estado === 'en_camino',
                  'bg-gray-100 text-gray-700': !['cancelado','entregado','preparando','listo','en_camino'].includes(p.estado)
                }"
              >
                {{ p.estado.replace('_',' ') }}
              </span>
            </td>
            <td class="px-4 py-3 capitalize">{{ p.tipo?.replace('_',' ') }}</td>
            <td class="px-4 py-3">{{ p.items }}</td>
            <td class="px-4 py-3">
              <span v-if="p.repartidor" class="inline-flex rounded-full bg-indigo-100 text-indigo-800 text-xs px-2.5 py-0.5">
                {{ p.repartidor }}
              </span>
              <span v-else class="inline-flex rounded-full bg-gray-100 text-gray-600 text-xs px-2.5 py-0.5">—</span>
            </td>
            <td class="px-4 py-3 font-semibold">{{ money(p.total) }}</td>
            <td class="px-4 py-3">{{ p.created_at }}</td>
            <td class="px-4 py-3 text-right">
              <Link
                :href="`/${base}/pedidos/${p.id}`"
                class="text-indigo-600 hover:text-indigo-700 font-medium"
              >
                Ver detalle →
              </Link>
            </td>
          </tr>

          <tr v-if="!pedidos.data?.length">
            <td colspan="9" class="px-4 py-8 text-center text-neutral-500">
              Sin resultados.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <nav v-if="pedidos.links?.length" class="flex flex-wrap gap-2">
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
      />
    </nav>
  </div>
</template>
