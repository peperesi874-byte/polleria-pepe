<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  filtros: { type: Object, default: () => ({ desde: '', hasta: '' }) },
  resumen: { type: Object, default: () => ({}) },
  series:  { type: Array,  default: () => [] }, // [{ fecha, pedidos, ingresos }]
})

/* 🔎 Filtros */
const desde = ref(props.filtros.desde ?? '')
const hasta = ref(props.filtros.hasta ?? '')

/* Helpers visuales */
const btn = (variant = 'solid') =>
  [
    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-300',
    variant === 'solid'
      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
      : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
  ].join(' ')

const card = 'rounded-2xl border border-gray-200 bg-white p-5 shadow-sm'

const query = computed(() => {
  const q = new URLSearchParams()
  if (desde.value) q.set('desde', desde.value)
  if (hasta.value) q.set('hasta', hasta.value)
  return q.toString()
})

const CSV_URL  = computed(() => `/admin/reportes/ingresos/export/csv?${query.value}`)
const VIEW_URL = computed(() => `/admin/reportes/ingresos?${query.value}`)

/* Formato moneda */
function money(n) {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
  }).format(Number(n ?? 0))
}

/* 🧮 KPIs generales */
const totalDias = computed(() => (props.series ?? []).length)
const ingresosTotal = computed(() => Number(props.resumen.ingresos_total ?? 0))
const pedidosTotal  = computed(() => Number(props.resumen.pedidos ?? 0))

const promedioPorDia = computed(() =>
  totalDias.value > 0 ? ingresosTotal.value / totalDias.value : 0
)

const promedioPorTicket = computed(() =>
  pedidosTotal.value > 0 ? ingresosTotal.value / pedidosTotal.value : 0
)

/* 🚚 Desglose mostrador / domicilio */
const mostrador = computed(() => props.resumen.mostrador ?? { pedidos: 0, ingresos: 0 })
const domicilio = computed(() => props.resumen.domicilio ?? { pedidos: 0, ingresos: 0 })

/* 🆕 Detalle de un solo día (para gerente) */
const selectedDia = ref(props.series?.[0]?.fecha ?? '')

const detalleDia = computed(() => {
  if (!selectedDia.value) return null
  return (props.series ?? []).find(r => r.fecha === selectedDia.value) ?? null
})
</script>

<template>
  <Head title="Ingresos" />
  <AuthenticatedLayout>
    <!-- ENCABEZADO -->
    <template #header>
      <div
        class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-2xl bg-amber-100 text-amber-700">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path
                d="M12 1.5c-5.8 0-10.5 4.7-10.5 10.5S6.2 22.5 12 22.5 22.5 17.8 22.5 12 17.8 1.5 12 1.5Zm.8 16.4v1.1h-1.6v-1.1c-1.7-.2-3-1.3-3.1-3.1h2c.1.9.7 1.6 2 1.6 1.2 0 1.9-.6 1.9-1.4 0-.7-.5-1.1-1.8-1.4l-1-.2c-2-.4-3.1-1.3-3.1-3 0-1.6 1.2-2.8 3-3.1V4.5h1.6v1.1c1.6.2 2.7 1.2 2.9 2.8h-2c-.1-.8-.7-1.4-1.7-1.4-1.1 0-1.8.5-1.8 1.3 0 .7.5 1.1 1.8 1.3l.9.2c2.1.4 3.1 1.3 3.1 3 0 1.7-1.2 3-3.1 3.2Z"
              />
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">Ingresos</h2>
            <p class="text-sm text-gray-500">
              Vista para gerencia: ingresos de pedidos entregados, por día y por tipo de venta.
            </p>
          </div>
        </div>

        <a href="/admin/reportes" class="text-sm text-indigo-600 hover:underline">
          ← Volver a reportes
        </a>
      </div>
    </template>

    <div class="mx-auto max-w-7xl p-6 space-y-6">
      <!-- FILTROS -->
      <div :class="card">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">
          Filtros de periodo
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Desde</label>
            <input
              v-model="desde"
              type="date"
              class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-300"
            />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Hasta</label>
            <input
              v-model="hasta"
              type="date"
              class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-300"
            />
          </div>
          <div class="flex gap-2">
            <a :href="VIEW_URL" :class="btn('solid')">Aplicar</a>
            <a :href="CSV_URL" :class="btn('outline')">Exportar CSV</a>
          </div>
        </div>
      </div>

      <!-- KPIs PRINCIPALES -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Ingresos totales -->
        <div :class="card">
          <p class="text-xs text-gray-500">Ingresos totales del periodo</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">
            {{ money(ingresosTotal) }}
          </p>
        </div>

        <!-- Pedidos totales -->
        <div :class="card">
          <p class="text-xs text-gray-500">Pedidos entregados</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">
            {{ pedidosTotal }}
          </p>
        </div>

        <!-- Promedio por día -->
        <div :class="card">
          <p class="text-xs text-gray-500">Ingresos promedio por día</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">
            {{ money(promedioPorDia) }}
          </p>
          <p class="mt-1 text-[11px] text-gray-500">
            Calculado sobre {{ totalDias }} día(s) con ventas.
          </p>
        </div>

        <!-- Promedio por ticket -->
        <div :class="card">
          <p class="text-xs text-gray-500">Ticket promedio</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">
            {{ money(promedioPorTicket) }}
          </p>
          <p class="mt-1 text-[11px] text-gray-500">
            Ingreso promedio por pedido entregado.
          </p>
        </div>
      </div>

      <!-- DESGLOSE POR TIPO + DETALLE DE UN DÍA -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Mostrador -->
        <div :class="card">
          <p class="text-xs text-gray-500 mb-1">Ventas en mostrador</p>
          <p class="text-lg font-semibold text-gray-900">
            {{ money(mostrador.ingresos) }}
          </p>
          <p class="mt-1 text-sm text-gray-600">
            Pedidos: <span class="font-semibold">{{ mostrador.pedidos }}</span>
          </p>
        </div>

        <!-- Domicilio -->
        <div :class="card">
          <p class="text-xs text-gray-500 mb-1">Ventas a domicilio</p>
          <p class="text-lg font-semibold text-gray-900">
            {{ money(domicilio.ingresos) }}
          </p>
          <p class="mt-1 text-sm text-gray-600">
            Pedidos: <span class="font-semibold">{{ domicilio.pedidos }}</span>
          </p>
        </div>

        <!-- Detalle de un día -->
        <div :class="card">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-gray-500">Detalle de un día</p>
          </div>

          <label class="block text-[11px] text-gray-500 mb-1">Selecciona fecha</label>
          <input
            v-model="selectedDia"
            type="date"
            class="w-full rounded-lg border-gray-300 mb-3 focus:ring-2 focus:ring-indigo-300"
          />

          <div v-if="detalleDia">
            <p class="text-sm text-gray-600">
              Fecha:
              <span class="font-medium text-gray-900">{{ detalleDia.fecha }}</span>
            </p>
            <p class="mt-1 text-sm text-gray-600">
              Pedidos:
              <span class="font-semibold text-gray-900">{{ detalleDia.pedidos }}</span>
            </p>
            <p class="mt-1 text-sm text-gray-600">
              Ingresos del día:
              <span class="font-semibold text-gray-900">
                {{ money(detalleDia.ingresos) }}
              </span>
            </p>
          </div>
          <div v-else class="text-xs text-gray-500 mt-1">
            No hay registros para esa fecha dentro del periodo seleccionado.
          </div>
        </div>
      </div>

      <!-- TABLA POR DÍA -->
      <div :class="card">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">
          Detalle diario de ingresos
        </h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
              <tr>
                <th class="px-4 py-2 text-left">Fecha</th>
                <th class="px-4 py-2 text-right">Pedidos</th>
                <th class="px-4 py-2 text-right">Ingresos</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="r in series"
                :key="r.fecha"
                class="border-t hover:bg-gray-50/60"
              >
                <td class="px-4 py-2">{{ r.fecha }}</td>
                <td class="px-4 py-2 text-right">
                  {{ Number(r.pedidos ?? 0) }}
                </td>
                <td class="px-4 py-2 text-right">
                  {{ money(r.ingresos) }}
                </td>
              </tr>
              <tr v-if="series.length === 0">
                <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                  Sin resultados para el rango dado.
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50 font-medium">
              <tr>
                <td class="px-4 py-2 text-right">Totales:</td>
                <td class="px-4 py-2 text-right">{{ pedidosTotal }}</td>
                <td class="px-4 py-2 text-right">{{ money(ingresosTotal) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
