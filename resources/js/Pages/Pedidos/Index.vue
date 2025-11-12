<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch, onBeforeUnmount, computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  role: { type: String, default: 'admin' }, // 'admin' | 'vendedor'
  pedidos: Object,
  q: String,
  estado: String,
  asignado: String,
  estados: Array,
})

const base = computed(() => (props.role === 'vendedor' ? 'vendedor' : 'admin'))

const q        = ref(props.q ?? '')
const estado   = ref(props.estado ?? '')
const asignado = ref(props.asignado ?? '')

const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n ?? 0))

function pillColor(e) {
  switch (e) {
    case 'cancelado':  return 'bg-red-100 text-red-700'
    case 'entregado':  return 'bg-green-100 text-green-700'
    case 'listo':      return 'bg-blue-100 text-blue-700'
    case 'en_camino':  return 'bg-indigo-100 text-indigo-700'
    case 'preparando': return 'bg-amber-100 text-amber-700'
    default:           return 'bg-gray-100 text-gray-700'
  }
}

let t = null
const pushFilters = () => {
  const params = {}
  if (q.value) params.q = q.value
  if (estado.value) params.estado = estado.value
  if (asignado.value) params.asignado = asignado.value

  router.get(route(base.value + '.pedidos.index'), params, {
    preserveState: true,
    replace: true,
  })
}

watch([q, estado, asignado], () => {
  clearTimeout(t)
  t = setTimeout(pushFilters, 300)
})

onBeforeUnmount(() => clearTimeout(t))
</script>

<template>
  <AuthenticatedLayout>
    <!-- ===== ENCABEZADO ===== -->
    <template #header>
      <div
        class="flex items-center justify-between rounded-2xl border bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-100 text-indigo-700 text-lg">
            🧺
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">
              {{ base === 'vendedor' ? 'Pedidos en mostrador' : 'Gestión de pedidos' }}
            </h1>
            <p class="text-sm text-gray-500">
              {{ base === 'vendedor'
                ? 'Registra y controla los pedidos de clientes presenciales.'
                : 'Supervisa y administra los pedidos de todo el sistema.' }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- Solo el vendedor ve el botón para crear pedido -->
          <Link
            v-if="base === 'vendedor'"
            :href="route('vendedor.pedidos.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
          >
            + Nuevo pedido
          </Link>

          <Link
            :href="route(base + '.dashboard')"
            class="text-sm text-indigo-600 hover:underline"
          >
            ← Volver al panel
          </Link>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Filtros -->
      <div class="mb-5 flex flex-wrap items-center gap-3">
        <input
          v-model="q"
          type="text"
          placeholder="🔎 Buscar por folio u observaciones…"
          class="w-full md:w-80 rounded-lg border-gray-300 px-4 py-2 focus:ring-indigo-400"
        />

        <select v-model="estado" class="rounded-lg border-gray-300 px-3 py-2 focus:ring-indigo-400">
          <option value="">Todos los estados</option>
          <option v-for="e in estados" :key="e" :value="e">{{ e.replace('_', ' ') }}</option>
        </select>

        <select v-model="asignado" class="rounded-lg border-gray-300 px-3 py-2 focus:ring-indigo-400">
          <option value="">Asignados: todos</option>
          <option value="any">Solo asignados</option>
          <option value="none">Solo sin asignar</option>
        </select>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
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
              <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, i) in pedidos.data" :key="p.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-3 text-gray-600">{{ (pedidos.from ?? 1) + i }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ p.folio ?? '—' }}</td>

              <td class="px-4 py-3">
                <span :class="['px-2 py-1 rounded-full text-xs font-semibold', pillColor(p.estado)]">
                  {{ (p.estado || 'pendiente').replace('_',' ') }}
                </span>
              </td>

              <td class="px-4 py-3 capitalize">{{ (p.tipo || '—').toString().replace('_',' ') }}</td>
              <td class="px-4 py-3">{{ p.items ?? 0 }}</td>

              <td class="px-4 py-3">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs',
                  p.repartidor ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'
                ]">
                  {{ p.repartidor ?? '—' }}
                </span>
              </td>

              <td class="px-4 py-3">{{ money(p.total) }}</td>
              <td class="px-4 py-3 text-gray-500">{{ p.created_at ?? '—' }}</td>

              <td class="px-4 py-3 text-right">
                <Link
                  :href="route(base + '.pedidos.show', p.id)"
                  class="text-indigo-600 hover:text-indigo-700 font-medium"
                >
                  Ver detalle →
                </Link>
              </td>
            </tr>

            <tr v-if="(pedidos.data?.length || 0) === 0">
              <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                No hay pedidos con esos filtros.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-5 flex justify-end gap-2">
        <Link
          v-for="(lnk, i) in pedidos.links"
          :key="i"
          :href="lnk.url || '#'"
          v-html="lnk.label"
          preserve-state
          replace
          :class="[
            'px-3 py-1 rounded-md border text-sm',
            lnk.active
              ? 'bg-indigo-600 text-white border-indigo-600'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-indigo-50',
            !lnk.url && 'pointer-events-none opacity-40'
          ]"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
