<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  fecha: {
    type: String,
    default: null,
  },
  resumen: {
    type: Object,
    default: () => ({}),
  },
})

/* 🔹 Fecha seleccionada */
const fecha = ref(props.fecha ?? new Date().toISOString().slice(0, 10))

/* 🔹 Normalizar números */
const stats = computed(() => ({
  total:           Number(props.resumen.total           ?? 0),
  pendientes:      Number(props.resumen.pendientes      ?? 0),
  en_camino:       Number(props.resumen.en_camino       ?? 0),
  entregados:      Number(props.resumen.entregados      ?? 0),
  cancelados:      Number(props.resumen.cancelados      ?? 0),
  mostrador:       Number(props.resumen.mostrador       ?? 0),
  domicilio:       Number(props.resumen.domicilio       ?? 0),
  total_importe:   Number(props.resumen.total_importe   ?? 0),
  mostrador_monto: Number(props.resumen.mostrador_monto ?? 0),
  domicilio_monto: Number(props.resumen.domicilio_monto ?? 0),
}))

const fmtInt = n =>
  new Intl.NumberFormat('es-MX').format(Number(n ?? 0))

const fmtMoney = n =>
  new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
  }).format(Number(n ?? 0))

/* 🔹 Consultar otro día */
const consultar = () => {
  router.get(
    route('vendedor.reportes.operativos'),
    { fecha: fecha.value },
    { preserveState: true, preserveScroll: true },
  )
}

/* 🔹 Ir a pedidos con filtros */
const irAPedidos = (params = {}) => {
  router.get(
    route('vendedor.pedidos.index'),
    params,
    { preserveScroll: true },
  )
}
</script>

<template>
  <AuthenticatedLayout role="vendedor">
    <Head title="Reportes operativos" />

    <!-- ===== HEADER NARANJA POLLERÍA ===== -->
    <template #header>
      <div
        class="mx-auto max-w-7xl rounded-[32px] bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-6 py-4 shadow-[0_26px_70px_rgba(249,115,22,0.55)] ring-1 ring-amber-400/60 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
      >
        <!-- Izquierda -->
        <div class="space-y-2">
          <div
            class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
          >
            <span class="text-lg">📝</span>
            <span>Reportes operativos — Pollería Pepe</span>
          </div>

          <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">
              Reporte diario de pedidos
            </h1>
            <p class="mt-1 text-sm text-amber-50/90">
              Revisa de un vistazo cuántos pedidos hubo, en qué estado están y cuánto se vendió.
            </p>
          </div>

          <p class="text-[11px] text-amber-100/90">
            Usa las tarjetas para saltar directo a la vista de pedidos ya filtrada.
          </p>
        </div>

        <!-- Derecha: fecha + consultar + volver -->
        <div
          class="flex flex-col gap-2 rounded-2xl bg-white/95 px-4 py-3 text-xs text-slate-800 shadow-md ring-1 ring-white/70 backdrop-blur md:w-[340px]"
        >
          <label class="text-[11px] font-semibold text-slate-700">
            Fecha del reporte
          </label>
          <div class="flex items-center gap-2">
            <input
              v-model="fecha"
              type="date"
              class="flex-1 rounded-xl border border-slate-200 px-2.5 py-1.5 text-xs shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
            />
            <button
              type="button"
              @click="consultar"
              class="inline-flex items-center gap-1 rounded-xl bg-amber-500 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-600"
            >
              Consultar
            </button>
          </div>

          <div class="mt-1 flex justify-between items-center">
            <p class="text-[11px] text-slate-500">
              Total día: <span class="font-semibold text-slate-800">{{ fmtInt(stats.total) }}</span> pedidos
            </p>
            <Link
              :href="route('vendedor.dashboard')"
              class="text-[11px] font-medium text-amber-600 hover:text-amber-700 hover:underline"
            >
              ← Volver al panel
            </Link>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO NUEVO DISEÑO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/10 to-slate-50">
      <div class="mx-auto max-w-7xl space-y-6 p-6">

        <!-- FILA 1: TARJETA GRANDE + IMPORTE / MIX MOSTRADOR VS DOMICILIO -->
        <section class="grid gap-4 lg:grid-cols-3">
          <!-- Resumen grande -->
          <div
            class="lg:col-span-2 rounded-3xl bg-gradient-to-br from-amber-50 via-white to-emerald-50 p-5 shadow-md ring-1 ring-amber-100/80"
          >
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              <div>
                <div
                  class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200/80 shadow-sm"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
                  <span>Resumen del día</span>
                </div>

                <p class="mt-2 text-sm text-amber-950">
                  Para esta fecha se registraron
                  <span class="font-semibold">{{ fmtInt(stats.total) }}</span>
                  pedidos por un total de
                  <span class="font-semibold">{{ fmtMoney(stats.total_importe) }}</span>.
                </p>

                <div class="mt-3 grid gap-3 text-xs sm:grid-cols-3">
                  <div class="rounded-2xl bg-white/80 px-3 py-2 shadow-sm ring-1 ring-amber-100">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase">Pendientes</p>
                    <p class="mt-1 text-lg font-semibold text-amber-700">
                      {{ fmtInt(stats.pendientes) }}
                    </p>
                  </div>
                  <div class="rounded-2xl bg-white/80 px-3 py-2 shadow-sm ring-1 ring-sky-100">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase">En camino</p>
                    <p class="mt-1 text-lg font-semibold text-sky-700">
                      {{ fmtInt(stats.en_camino) }}
                    </p>
                  </div>
                  <div class="rounded-2xl bg-white/80 px-3 py-2 shadow-sm ring-1 ring-emerald-100">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase">Entregados</p>
                    <p class="mt-1 text-lg font-semibold text-emerald-700">
                      {{ fmtInt(stats.entregados) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Sparkline / mini gráfica decorativa -->
              <div class="w-full max-w-xs self-stretch rounded-2xl bg-slate-900 p-4 text-slate-50 shadow-inner">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-300">
                  Flujo del día
                </p>
                <p class="mt-1 text-xs text-slate-200">
                  Pedidos por estado (pendiente → en camino → entregado).
                </p>

                <svg viewBox="0 0 100 40" class="mt-3 h-20 w-full text-amber-400">
                  <polyline
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    :points="`0,30 20,${30 - Math.min(stats.pendientes*2,25)} 50,${30 - Math.min(stats.en_camino*2,25)} 80,${30 - Math.min(stats.entregados*2,25)} 100,28`"
                  />
                </svg>

                <div class="mt-2 grid grid-cols-3 gap-1 text-[11px]">
                  <div>
                    <p class="text-slate-400">Pend.</p>
                    <p class="font-semibold text-amber-300">{{ fmtInt(stats.pendientes) }}</p>
                  </div>
                  <div>
                    <p class="text-slate-400">En camino</p>
                    <p class="font-semibold text-sky-300">{{ fmtInt(stats.en_camino) }}</p>
                  </div>
                  <div>
                    <p class="text-slate-400">Entreg.</p>
                    <p class="font-semibold text-emerald-300">{{ fmtInt(stats.entregados) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Importe total + chips de tipo -->
          <div class="space-y-3">
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600">
                Importe total
              </p>
              <p class="mt-2 text-2xl font-semibold text-gray-900">
                {{ fmtMoney(stats.total_importe) }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                Suma de todos los pedidos del día.
              </p>
            </div>

            <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
              <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-600">
                Distribución por tipo
              </p>
              <div class="mt-2 flex flex-col gap-1 text-xs text-gray-700">
                <p>
                  <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-800">
                    🧾 Mostrador
                  </span>
                  <span class="ml-2">{{ fmtInt(stats.mostrador) }} pedidos</span>
                </p>
                <p>
                  <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 font-semibold text-sky-800">
                    🏍️ Domicilio
                  </span>
                  <span class="ml-2">{{ fmtInt(stats.domicilio) }} pedidos</span>
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- FILA 2: TARJETAS POR ESTADO (CLICKEABLES) -->
        <section class="space-y-3">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
            Pedidos por estado
          </h2>

          <div class="grid gap-4 md:grid-cols-4">
            <!-- Pendientes -->
            <div
              class="cursor-pointer rounded-2xl border border-amber-100 bg-amber-50/70 p-4 text-amber-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
              @click="irAPedidos({ estado: 'pendiente' })"
            >
              <p class="text-[11px] font-semibold uppercase tracking-wide">Pendientes</p>
              <p class="mt-2 text-2xl font-semibold">
                {{ fmtInt(stats.pendientes) }}
              </p>
              <p class="mt-1 text-xs text-amber-900/80">
                Aún por atender.
              </p>
            </div>

            <!-- En camino -->
            <div
              class="cursor-pointer rounded-2xl border border-sky-100 bg-sky-50/70 p-4 text-sky-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
              @click="irAPedidos({ estado: 'en_camino' })"
            >
              <p class="text-[11px] font-semibold uppercase tracking-wide">En camino</p>
              <p class="mt-2 text-2xl font-semibold">
                {{ fmtInt(stats.en_camino) }}
              </p>
              <p class="mt-1 text-xs text-sky-900/80">
                Van en ruta al cliente.
              </p>
            </div>

            <!-- Entregados -->
            <div
              class="cursor-pointer rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-emerald-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
              @click="irAPedidos({ estado: 'entregado' })"
            >
              <p class="text-[11px] font-semibold uppercase tracking-wide">Entregados</p>
              <p class="mt-2 text-2xl font-semibold">
                {{ fmtInt(stats.entregados) }}
              </p>
              <p class="mt-1 text-xs text-emerald-900/80">
                Completados correctamente.
              </p>
            </div>

            <!-- Cancelados -->
            <div
              class="cursor-pointer rounded-2xl border border-rose-100 bg-rose-50/70 p-4 text-rose-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
              @click="irAPedidos({ estado: 'cancelado' })"
            >
              <p class="text-[11px] font-semibold uppercase tracking-wide">Cancelados</p>
              <p class="mt-2 text-2xl font-semibold">
                {{ fmtInt(stats.cancelados) }}
              </p>
              <p class="mt-1 text-xs text-rose-900/80">
                Pedidos anulados.
              </p>
            </div>
          </div>
        </section>

        <!-- FILA 3: MOSTRADOR / DOMICILIO CON MONTOS -->
        <section class="space-y-3">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
            Detalle por tipo de pedido
          </h2>

          <div class="grid gap-4 md:grid-cols-2">
            <!-- Mostrador -->
            <div
              class="cursor-pointer rounded-3xl border border-indigo-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
              @click="irAPedidos({ tipo: 'mostrador' })"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-600">
                    Pedidos en mostrador
                  </p>
                  <p class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ fmtInt(stats.mostrador) }} pedidos
                  </p>
                  <p class="mt-1 text-xs text-gray-500">
                    Monto: {{ fmtMoney(stats.mostrador_monto) }}
                  </p>
                </div>
                <div
                  class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                >
                  🧾
                </div>
              </div>

              <div class="mt-3 h-2 w-full rounded-full bg-indigo-50">
                <div
                  class="h-2 rounded-full bg-indigo-500"
                  :style="{ width: stats.total ? Math.min((stats.mostrador / stats.total) * 100, 100) + '%' : '0%' }"
                />
              </div>
            </div>

            <!-- Domicilio -->
            <div
              class="cursor-pointer rounded-3xl border border-purple-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
              @click="irAPedidos({ tipo: 'domicilio' })"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-purple-600">
                    Pedidos a domicilio
                  </p>
                  <p class="mt-2 text-2xl font-semibold text-gray-900">
                    {{ fmtInt(stats.domicilio) }} pedidos
                  </p>
                  <p class="mt-1 text-xs text-gray-500">
                    Monto: {{ fmtMoney(stats.domicilio_monto) }}
                  </p>
                </div>
                <div
                  class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
                >
                  🏍️
                </div>
              </div>

              <div class="mt-3 h-2 w-full rounded-full bg-purple-50">
                <div
                  class="h-2 rounded-full bg-purple-500"
                  :style="{ width: stats.total ? Math.min((stats.domicilio / stats.total) * 100, 100) + '%' : '0%' }"
                />
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>