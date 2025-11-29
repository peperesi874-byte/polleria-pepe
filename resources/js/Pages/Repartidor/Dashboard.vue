<!-- resources/js/Pages/Repartidor/Dashboard.vue -->
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
    <!-- ===== HEADER ===== -->
    <template #header>
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-50 via-indigo-50 to-emerald-50 px-6 py-5 ring-1 ring-sky-100 shadow-sm"
      >
        <div class="pointer-events-none absolute -right-16 -top-10 h-32 w-32 rounded-full bg-sky-200/30 blur-2xl" />
        <div class="pointer-events-none absolute -left-10 bottom-0 h-28 w-28 rounded-full bg-emerald-200/40 blur-2xl" />

        <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="flex items-start gap-3">
            <div
              class="grid h-11 w-11 place-items-center rounded-2xl bg-sky-600 text-white shadow-md shadow-sky-200 text-xl"
            >
              🚚
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700/80">
                Panel del repartidor
              </p>
              <h1 class="mt-1 flex items-center gap-2 text-2xl md:text-3xl font-bold text-neutral-900">
                {{ saludo }}, {{ user.name ?? 'Repartidor' }} 👋
              </h1>
              <p class="mt-1 text-sm text-neutral-600 max-w-xl">
                Resumen de tus entregas asignadas, pedidos en ruta y entregas recientes.
              </p>
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <div class="rounded-xl bg-white/80 px-3 py-2 text-right shadow-sm border border-sky-50">
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
              class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-4 py-1.5 text-xs font-semibold text-sky-800 shadow-sm hover:bg-sky-100 hover:border-sky-300 transition"
            >
              🧾 Ver pedidos asignados
            </Link>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-white to-sky-50/40">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-0 pb-8 pt-6 space-y-8">

        <!-- ===== KPIs ===== -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Asignados -->
          <Link
            :href="route('repartidor.pedidos.index')"
            class="group relative overflow-hidden rounded-2xl border border-indigo-100 bg-white/95 p-4 flex flex-col gap-2 shadow-sm hover:-translate-y-1 hover:shadow-xl transition-all cursor-pointer"
          >
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-400 via-indigo-500 to-sky-500" />
            <div class="flex items-center justify-between mt-1">
              <p class="text-[11px] font-semibold text-neutral-600 uppercase tracking-[0.18em]">
                Pedidos asignados
              </p>
              <span
                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 text-sm group-hover:bg-indigo-100"
              >
                📦
              </span>
            </div>
            <p class="text-3xl font-extrabold text-indigo-700 leading-none mt-1">
              {{ props.stats.asignados ?? 0 }}
            </p>
            <p class="text-xs text-neutral-500">
              Total de pedidos a tu nombre.
            </p>
            <div class="mt-2 flex items-center justify-between text-[11px] text-neutral-400">
              <span class="inline-flex items-center gap-1">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" /> Activos
              </span>
              <span class="opacity-70">Tap para ver más →</span>
            </div>
          </Link>

          <!-- Por salir -->
          <Link
            :href="route('repartidor.pedidos.index')"
            class="group relative overflow-hidden rounded-2xl border border-amber-100 bg-white/95 p-4 flex flex-col gap-2 shadow-sm hover:-translate-y-1 hover:shadow-xl transition-all cursor-pointer"
          >
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400" />
            <div class="flex items-center justify-between mt-1">
              <p class="text-[11px] font-semibold text-amber-700 uppercase tracking-[0.18em]">
                Por salir
              </p>
              <span
                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-700 text-sm group-hover:bg-amber-100"
              >
                ⏱️
              </span>
            </div>
            <p class="text-3xl font-extrabold text-amber-700 leading-none mt-1">
              {{ props.stats.pendientes ?? 0 }}
            </p>
            <p class="text-xs text-neutral-500">
              Pendientes o listos sin salir a ruta.
            </p>
            <div class="mt-2 h-1.5 w-full rounded-full bg-amber-50 overflow-hidden">
              <div class="h-full w-2/3 rounded-full bg-amber-400/80" />
            </div>
          </Link>

          <!-- En camino -->
          <Link
            :href="route('repartidor.pedidos.index')"
            class="group relative overflow-hidden rounded-2xl border border-sky-100 bg-white/95 p-4 flex flex-col gap-2 shadow-sm hover:-translate-y-1 hover:shadow-xl transition-all cursor-pointer"
          >
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-400 via-sky-500 to-indigo-500" />
            <div class="flex items-center justify-between mt-1">
              <p class="text-[11px] font-semibold text-sky-700 uppercase tracking-[0.18em]">
                En camino
              </p>
              <span
                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-50 text-sky-700 text-sm group-hover:bg-sky-100"
              >
                🛵
              </span>
            </div>
            <p class="text-3xl font-extrabold text-sky-700 leading-none mt-1">
              {{ props.stats.en_camino ?? 0 }}
            </p>
            <p class="text-xs text-neutral-500">
              Entregas actualmente en recorrido.
            </p>
            <div class="mt-2 flex items-center gap-1 text-[11px] text-sky-700/70">
              <span class="inline-flex h-1.5 w-1.5 rounded-full bg-sky-400" />
              Ruta activa
            </div>
          </Link>

          <!-- Entregados hoy -->
          <Link
            :href="route('repartidor.pedidos.index')"
            class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white/95 p-4 flex flex-col gap-2 shadow-sm hover:-translate-y-1 hover:shadow-xl transition-all cursor-pointer"
          >
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-500" />
            <div class="flex items-center justify-between mt-1">
              <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-[0.18em]">
                Entregados hoy
              </p>
              <span
                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 text-sm group-hover:bg-emerald-100"
              >
                ✅
              </span>
            </div>
            <p class="text-3xl font-extrabold text-emerald-700 leading-none mt-1">
              {{ props.stats.entregados_hoy ?? 0 }}
            </p>
            <p class="text-xs text-neutral-500">
              Pedidos marcados como entregados hoy.
            </p>
            <div class="mt-2 flex items-center justify-between text-[11px] text-emerald-700/70">
              <span>Buen ritmo de entrega</span>
              <span class="text-emerald-500 font-semibold">✔</span>
            </div>
          </Link>
        </section>

        <!-- ===== CONTENIDO PRINCIPAL (más color en la parte de abajo) ===== -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- PRÓXIMAS ENTREGAS -->
          <div class="lg:col-span-2 space-y-3">
            <!-- Título -->
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <div class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                  📍
                </div>
                <div>
                  <h2 class="text-[11px] font-semibold text-neutral-700 uppercase tracking-[0.2em]">
                    Próximas entregas
                  </h2>
                  <p class="text-[11px] text-neutral-400">
                    Organiza la ruta según colonia, zona y hora de creación.
                  </p>
                </div>
                <span
                  class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700"
                >
                  {{ props.proximos.length }} por salir
                </span>
              </div>
            </div>

            <!-- Empty state -->
            <div
              v-if="!props.proximos.length"
              class="relative overflow-hidden rounded-3xl border border-dashed border-amber-200 bg-gradient-to-r from-amber-50 via-orange-50/60 to-emerald-50/50 px-5 py-6 flex items-center gap-4"
            >
              <div
                class="hidden sm:flex h-12 w-12 items-center justify-center rounded-2xl bg-white/80 shadow-inner shadow-amber-100 text-2xl"
              >
                🗺️
              </div>
              <div class="space-y-1 text-sm">
                <p class="font-semibold text-amber-900">
                  No tienes pedidos pendientes ni listos para salir a reparto.
                </p>
                <p class="text-[13px] text-amber-800/80 max-w-xl">
                  Cuando te asignen nuevos pedidos aparecerán aquí para que armes tu ruta del día.
                </p>
                <p class="text-[11px] text-amber-700/70 mt-1 flex items-center gap-1">
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                  Consejo: agrupa por colonia y horario para ahorrar tiempo en tus recorridos.
                </p>
              </div>
            </div>

            <!-- Tabla / tarjetas cuando sí hay datos -->
            <div
              v-else
              class="rounded-3xl border border-slate-100 bg-white/95 shadow-sm overflow-hidden"
            >
              <!-- Encabezado -->
              <div class="flex items-center justify-between px-4 py-2 border-b border-slate-100 bg-slate-50/70">
                <div class="flex items-center gap-2 text-xs text-neutral-600">
                  <span class="h-2 w-2 rounded-full bg-emerald-400" />
                  <span>Pedidos listos para organizar ruta</span>
                </div>
                <span class="hidden sm:inline text-[11px] text-neutral-400">
                  Vista tipo tabla · desktop | tarjetas compactas · móvil
                </span>
              </div>

              <!-- Desktop: tabla -->
              <div class="hidden md:block">
                <table class="min-w-full text-sm">
                  <thead
                    class="bg-gradient-to-r from-sky-50 via-indigo-50 to-emerald-50 text-neutral-600 text-[11px] uppercase tracking-wide"
                  >
                    <tr>
                      <th class="px-4 py-2 text-left">Folio</th>
                      <th class="px-4 py-2 text-left">Zona</th>
                      <th class="px-4 py-2 text-left">Estado</th>
                      <th class="px-4 py-2 text-right">Total</th>
                      <th class="px-4 py-2 text-left">Creado</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <tr
                      v-for="p in props.proximos"
                      :key="p.id"
                      class="hover:bg-slate-50/80 transition-colors"
                    >
                      <td class="px-4 py-2 align-middle font-semibold text-neutral-900">
                        {{ p.folio ?? ('PED-' + p.id) }}
                      </td>
                      <td class="px-4 py-2 align-middle text-xs text-neutral-500">
                        {{
                          [p.entrega_colonia, p.entrega_municipio].filter(Boolean).join(', ') || 'Sin datos'
                        }}
                      </td>
                      <td class="px-4 py-2 align-middle">
                        <span
                          class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                          :class="estadoBadgeClass(p.estado)"
                        >
                          {{ p.estado }}
                        </span>
                      </td>
                      <td class="px-4 py-2 align-middle text-right font-semibold text-neutral-800">
                        {{ fmtMoney(p.total) }}
                      </td>
                      <td class="px-4 py-2 align-middle text-[11px] text-neutral-500">
                        {{ fmtDateTime(p.created_at) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Móvil: tarjetas -->
              <div class="md:hidden divide-y divide-slate-100">
                <div
                  v-for="p in props.proximos"
                  :key="p.id"
                  class="px-4 py-3 hover:bg-slate-50/80 transition-colors"
                >
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-neutral-900">
                        {{ p.folio ?? ('PED-' + p.id) }}
                      </p>
                      <p class="text-[11px] text-neutral-500">
                        {{
                          [p.entrega_colonia, p.entrega_municipio].filter(Boolean).join(', ') || 'Sin datos'
                        }}
                      </p>
                    </div>
                    <span
                      class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                      :class="estadoBadgeClass(p.estado)"
                    >
                      {{ p.estado }}
                    </span>
                  </div>
                  <div class="mt-2 flex items-center justify-between text-[11px] text-neutral-500">
                    <span>{{ fmtDateTime(p.created_at) }}</span>
                    <span class="font-semibold text-neutral-800">{{ fmtMoney(p.total) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- COLUMNA DERECHA: EN RUTA + HISTORIAL -->
          <div class="space-y-6">
            <!-- EN RUTA -->
            <div class="relative overflow-hidden rounded-3xl border border-sky-100 bg-white/95 shadow-sm p-4 space-y-3">
              <div class="pointer-events-none absolute -right-6 -top-6 h-16 w-16 rounded-full bg-sky-100/60 blur-xl" />
              <div class="relative space-y-3">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <div class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                      🛣️
                    </div>
                    <h2 class="text-[11px] font-semibold text-neutral-700 uppercase tracking-[0.2em]">
                      En ruta
                    </h2>
                  </div>
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700"
                  >
                    {{ props.enRuta.length }} activos
                  </span>
                </div>

                <div
                  v-if="!props.enRuta.length"
                  class="text-xs text-neutral-500 flex items-center gap-2 rounded-2xl border border-dashed border-sky-200 bg-sky-50/40 px-3 py-3"
                >
                  <span class="text-lg">🕊️</span>
                  <span>No tienes entregas en camino en este momento.</span>
                </div>

                <ul
                  v-else
                  class="space-y-2 text-sm"
                >
                  <li
                    v-for="p in props.enRuta"
                    :key="p.id"
                    class="flex items-center justify-between gap-2 rounded-2xl border border-slate-100 px-3 py-2 hover:bg-slate-50 transition"
                  >
                    <div class="flex items-start gap-2 min-w-0">
                      <span class="mt-1 h-2 w-2 rounded-full bg-sky-400" />
                      <div class="flex-1 min-w-0">
                        <p class="font-medium text-neutral-900 truncate">
                          Pedido #{{ p.folio ?? p.id }}
                        </p>
                        <p class="text-[11px] text-neutral-500 truncate">
                          {{
                            [p.entrega_colonia, p.entrega_municipio].filter(Boolean).join(', ') ||
                            'Sin datos de zona'
                          }}
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="text-[11px] text-neutral-400">
                        {{ fmtMoney(p.total) }}
                      </p>
                      <span
                        class="mt-1 inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold"
                        :class="estadoBadgeClass(p.estado)"
                      >
                        {{ p.estado }}
                      </span>
                    </div>
                  </li>
                </ul>
              </div>
            </div>

            <!-- HISTORIAL -->
            <div class="relative overflow-hidden rounded-3xl border border-slate-100 bg-white/95 shadow-sm p-4 space-y-3">
              <div class="pointer-events-none absolute -left-8 -bottom-8 h-16 w-16 rounded-full bg-emerald-100/60 blur-xl" />
              <div class="relative space-y-3">
                <div class="flex items-center justify-between gap-2">
                  <div class="flex items-center gap-2">
                    <div class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                      📜
                    </div>
                    <h2 class="text-[11px] font-semibold text-neutral-700 uppercase tracking-[0.2em]">
                      Historial reciente
                    </h2>
                  </div>
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-50 text-neutral-700"
                  >
                    {{ props.historial.length }} registros
                  </span>
                </div>

                <div
                  v-if="!props.historial.length"
                  class="text-xs text-neutral-500 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-3 py-3 flex items-center gap-2"
                >
                  <span class="text-lg">🕒</span>
                  <span>Aún no hay entregas o cancelaciones recientes.</span>
                </div>

                <ul
                  v-else
                  class="space-y-2 text-sm max-h-64 overflow-auto pr-1"
                >
                  <li
                    v-for="p in props.historial"
                    :key="p.id"
                    class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 px-3 py-2 hover:bg-slate-50 transition"
                  >
                    <div class="flex items-start gap-2 min-w-0">
                      <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400" />
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-neutral-900 truncate">
                          Pedido #{{ p.folio ?? p.id }}
                        </p>
                        <p class="text-[11px] text-neutral-500">
                          {{ fmtMoney(p.total) }}
                        </p>
                      </div>
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
          </div>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
