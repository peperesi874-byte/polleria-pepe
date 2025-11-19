<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const page = usePage()

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      asignados: 0,
      pendientes: 0,
      en_camino: 0,
      entregados_hoy: 0,
    }),
  },
  proximos: {
    type: Array,
    default: () => [],
  },
  enRuta: {
    type: Array,
    default: () => [],
  },
  historial: {
    type: Array,
    default: () => [],
  },
})

const user = computed(() => page.props?.auth?.user ?? {})

const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

const fmtMoney = n =>
  new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
  }).format(Number(n ?? 0))

const fmtDateTime = value => {
  if (!value) return ''
  const d = new Date(value)
  return d.toLocaleString('es-MX')
}

const estadoBadgeClass = estado => {
  switch (estado) {
    case 'pendiente':
    case 'confirmado':
    case 'en_preparacion':
    case 'listo':
      return 'bg-amber-100 text-amber-800'
    case 'en_camino':
    case 'en_reparto':
      return 'bg-sky-100 text-sky-800'
    case 'entregado':
      return 'bg-emerald-100 text-emerald-800'
    case 'cancelado':
      return 'bg-rose-100 text-rose-700'
    default:
      return 'bg-neutral-100 text-neutral-700'
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- HEADER (usa el slot del layout) -->
    <template #header>
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <p class="text-[11px] font-semibold text-neutral-500 uppercase tracking-[0.18em]">
            Panel del repartidor
          </p>
          <h1 class="mt-1 text-2xl md:text-3xl font-bold text-neutral-900 flex items-center gap-2">
            {{ saludo }}, {{ user.name ?? 'Repartidor' }} 👋
          </h1>
          <p class="mt-1 text-sm text-neutral-500 max-w-xl">
            Resumen de tus entregas asignadas, pedidos en ruta y entregas recientes.
          </p>
        </div>

        <div class="flex flex-col items-end gap-2">
          <div class="text-right">
            <p class="text-[11px] uppercase tracking-[0.16em] text-neutral-400">
              Hoy
            </p>
            <p class="text-sm font-medium text-neutral-800">
              {{
                new Date().toLocaleDateString('es-MX', {
                  weekday: 'long',
                  day: '2-digit',
                  month: 'short',
                  year: 'numeric',
                })
              }}
            </p>
          </div>

          <Link
            :href="route('repartidor.pedidos.index')"
            class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-4 py-1.5 text-xs font-semibold text-sky-700 border border-sky-100 hover:bg-sky-100 transition"
          >
            🚚 Ver pedidos asignados
          </Link>
        </div>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-0 pb-8 space-y-8">
      <!-- KPIs -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Asignados -->
        <div class="rounded-2xl border bg-white shadow-sm p-4 flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-semibold text-neutral-500 uppercase tracking-[0.18em]">
              Pedidos asignados
            </p>
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 text-sm">
              📦
            </span>
          </div>
          <p class="text-3xl font-bold text-neutral-900 leading-none">
            {{ props.stats.asignados ?? 0 }}
          </p>
          <p class="text-xs text-neutral-500">
            Total de pedidos asignados a ti.
          </p>
        </div>

        <!-- Por salir -->
        <div class="rounded-2xl border bg-white shadow-sm p-4 flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-semibold text-amber-700 uppercase tracking-[0.18em]">
              Por salir
            </p>
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-amber-50 text-amber-700 text-sm">
              ⏱️
            </span>
          </div>
          <p class="text-3xl font-bold text-neutral-900 leading-none">
            {{ props.stats.pendientes ?? 0 }}
          </p>
          <p class="text-xs text-neutral-500">
            Pendientes o listos sin salir a ruta.
          </p>
        </div>

        <!-- En camino -->
        <div class="rounded-2xl border bg-white shadow-sm p-4 flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-semibold text-sky-700 uppercase tracking-[0.18em]">
              En camino
            </p>
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-sky-50 text-sky-700 text-sm">
              🛵
            </span>
          </div>
          <p class="text-3xl font-bold text-neutral-900 leading-none">
            {{ props.stats.en_camino ?? 0 }}
          </p>
          <p class="text-xs text-neutral-500">
            Entregas actualmente en recorrido.
          </p>
        </div>

        <!-- Entregados hoy -->
        <div class="rounded-2xl border bg-white shadow-sm p-4 flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-[0.18em]">
              Entregados hoy
            </p>
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 text-sm">
              ✅
            </span>
          </div>
          <p class="text-3xl font-bold text-neutral-900 leading-none">
            {{ props.stats.entregados_hoy ?? 0 }}
          </p>
          <p class="text-xs text-neutral-500">
            Pedidos marcados como entregados en la fecha actual.
          </p>
        </div>
      </section>

      <!-- CONTENIDO PRINCIPAL -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Próximas entregas -->
        <div class="lg:col-span-2 space-y-3">
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <h2 class="text-xs font-semibold text-neutral-700 uppercase tracking-[0.2em]">
                Próximas entregas
              </h2>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700"
              >
                {{ props.proximos.length }} por salir
              </span>
            </div>
          </div>

          <div
            v-if="!props.proximos.length"
            class="rounded-2xl border border-dashed bg-gradient-to-r from-neutral-50 to-white p-5 text-xs text-neutral-500"
          >
            No tienes pedidos pendientes ni listos para salir a reparto.
          </div>

          <div
            v-else
            class="rounded-2xl border bg-white shadow-sm overflow-hidden"
          >
            <table class="min-w-full text-sm">
              <thead class="bg-neutral-50 text-neutral-500 text-xs uppercase tracking-wide">
                <tr>
                  <th class="px-4 py-2 text-left">Folio</th>
                  <th class="px-4 py-2 text-left hidden md:table-cell">Zona</th>
                  <th class="px-4 py-2 text-left">Estado</th>
                  <th class="px-4 py-2 text-right">Total</th>
                  <th class="px-4 py-2 text-left hidden sm:table-cell">Creado</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="p in props.proximos"
                  :key="p.id"
                  class="border-t hover:bg-neutral-50/80 transition-colors"
                >
                  <td class="px-4 py-2 align-middle font-medium text-neutral-900">
                    {{ p.folio ?? ('PED-' + p.id) }}
                  </td>
                  <td class="px-4 py-2 align-middle text-xs text-neutral-500 hidden md:table-cell">
                    {{
                      [p.entrega_colonia, p.entrega_municipio].filter(Boolean).join(', ') || 'Sin datos'
                    }}
                  </td>
                  <td class="px-4 py-2 align-middle">
                    <span
                      class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                      :class="estadoBadgeClass(p.estado)"
                    >
                      {{ p.estado }}
                    </span>
                  </td>
                  <td class="px-4 py-2 align-middle text-right font-medium text-neutral-800">
                    {{ fmtMoney(p.total) }}
                  </td>
                  <td class="px-4 py-2 align-middle text-xs text-neutral-500 hidden sm:table-cell">
                    {{ fmtDateTime(p.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Lateral derecha: En ruta + Historial -->
        <div class="space-y-6">
          <!-- En ruta -->
          <div class="rounded-2xl border bg-white shadow-sm p-4 space-y-3">
            <div class="flex items-center justify-between gap-2">
              <h2 class="text-xs font-semibold text-neutral-700 uppercase tracking-[0.2em]">
                En ruta
              </h2>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700"
              >
                {{ props.enRuta.length }} activos
              </span>
            </div>

            <div
              v-if="!props.enRuta.length"
              class="text-xs text-neutral-500"
            >
              No tienes entregas en camino en este momento.
            </div>

            <ul
              v-else
              class="space-y-2 text-sm"
            >
              <li
                v-for="p in props.enRuta"
                :key="p.id"
                class="flex items-center justify-between gap-2 border rounded-xl px-3 py-2 hover:bg-neutral-50 transition"
              >
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-neutral-900 truncate">
                    Pedido #{{ p.folio ?? p.id }}
                  </p>
                  <p class="text-xs text-neutral-500 truncate">
                    {{
                      [p.entrega_colonia, p.entrega_municipio].filter(Boolean).join(', ') ||
                      'Sin datos de zona'
                    }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-neutral-400">
                    {{ fmtMoney(p.total) }}
                  </p>
                  <span
                    class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[11px] font-semibold"
                    :class="estadoBadgeClass(p.estado)"
                  >
                    {{ p.estado }}
                  </span>
                </div>
              </li>
            </ul>
          </div>

          <!-- Historial -->
          <div class="rounded-2xl border bg-white shadow-sm p-4 space-y-3">
            <div class="flex items-center justify-between gap-2">
              <h2 class="text-xs font-semibold text-neutral-700 uppercase tracking-[0.2em]">
                Historial reciente
              </h2>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-50 text-neutral-700"
              >
                {{ props.historial.length }} registros
              </span>
            </div>

            <div
              v-if="!props.historial.length"
              class="text-xs text-neutral-500"
            >
              Aún no hay entregas o cancelaciones recientes.
            </div>

            <ul
              v-else
              class="space-y-2 text-sm max-h-64 overflow-auto pr-1"
            >
              <li
                v-for="p in props.historial"
                :key="p.id"
                class="flex items-center justify-between gap-3 border rounded-xl px-3 py-2 hover:bg-neutral-50 transition"
              >
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-neutral-900 truncate">
                    Pedido #{{ p.folio ?? p.id }}
                  </p>
                  <p class="text-xs text-neutral-500">
                    {{ fmtMoney(p.total) }}
                  </p>
                </div>
                <div class="flex flex-col items-end gap-1">
                  <span
                    class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                    :class="estadoBadgeClass(p.estado)"
                  >
                    {{ p.estado }}
                  </span>
                  <span class="text-[11px] text-neutral-400">
                    {{ fmtDateTime(p.updated_at ?? p.created_at) }}
                  </span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
