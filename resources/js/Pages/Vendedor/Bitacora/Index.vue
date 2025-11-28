<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  registros: Object, // paginator que viene del controlador
})

const items = computed(() => props.registros?.data ?? [])
const links = computed(() => props.registros?.links ?? [])
const total = computed(() => props.registros?.total ?? items.value.length)

/* 🔹 Icono según módulo (sólo visual, por texto) */
function moduloIcon(modulo) {
  const m = (modulo || '').toLowerCase()
  if (m.includes('pedido')) return '📦'
  if (m.includes('repartidor')) return '🏍️'
  if (m.includes('pago') || m.includes('cobro')) return '💳'
  if (m.includes('inventario')) return '📊'
  return '⚙️'
}

/* 🔹 Color de pill según módulo */
function moduloPillClass(modulo) {
  const m = (modulo || '').toLowerCase()
  if (m.includes('pedido')) return 'bg-amber-100 text-amber-800 ring-amber-200'
  if (m.includes('repartidor')) return 'bg-indigo-100 text-indigo-800 ring-indigo-200'
  if (m.includes('inventario')) return 'bg-emerald-100 text-emerald-800 ring-emerald-200'
  return 'bg-slate-100 text-slate-700 ring-slate-200'
}
</script>

<template>
  <AuthenticatedLayout role="vendedor">
    <Head title="Bitácora" />

    <!-- ===== HEADER NARANJA POLLERÍA (Vendedor) ===== -->
    <template #header>
      <div
        class="mx-auto max-w-7xl rounded-[32px] bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-6 py-4 shadow-[0_26px_70px_rgba(249,115,22,0.55)] ring-1 ring-amber-400/60 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
      >
        <!-- Izquierda -->
        <div class="flex items-start gap-3">
          <span
            class="inline-grid h-11 w-11 place-items-center rounded-2xl bg-black/20 text-2xl text-amber-50 shadow-md shadow-black/30"
          >
            📑
          </span>
          <div>
            <div
              class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
            >
              <span class="text-xs uppercase tracking-wide">Bitácora</span>
              <span class="h-1 w-1 rounded-full bg-amber-300"></span>
              <span>Panel del vendedor</span>
            </div>

            <h1 class="mt-2 text-2xl md:text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">
              Historial de movimientos
            </h1>
            <p class="mt-1 text-sm text-amber-50/90">
              Revisa las acciones que has realizado sobre los pedidos: cambios de estado,
              cancelaciones, registros nuevos, etc.
            </p>

            <p class="mt-2 text-[11px] text-amber-100/90">
              Se muestran primero los movimientos más recientes. Ideal para aclarar dudas
              con clientes o ver qué hiciste en cierto folio.
            </p>
          </div>
        </div>

        <!-- Derecha -->
        <div
          class="flex flex-col gap-2 rounded-2xl bg-white/95 px-4 py-3 text-xs text-slate-800 shadow-md ring-1 ring-white/70 backdrop-blur md:w-[260px]"
        >
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-semibold text-slate-700">
              Registros en tu bitácora
            </p>
            <span
              class="inline-flex items-center justify-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800"
            >
              {{ total }}
            </span>
          </div>

          <p class="text-[11px] text-slate-500">
            Sólo se registran movimientos realizados por tu usuario sobre pedidos.
          </p>

          <div class="mt-1 flex justify-end">
            <Link
              :href="route('vendedor.dashboard')"
              class="inline-flex items-center gap-1 rounded-xl bg-amber-500/95 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-600"
            >
              ← Volver al panel
            </Link>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/10 to-slate-50">
      <div class="mx-auto max-w-7xl p-6 space-y-4">
        <!-- Card tabla -->
        <div
          class="overflow-hidden rounded-3xl border border-amber-100 bg-white/95 shadow-lg shadow-amber-100/70 backdrop-blur"
        >
          <!-- Encabezado de sección -->
          <div
            class="flex items-center justify-between border-b border-amber-100/70 bg-gradient-to-r from-amber-50 via-amber-50/70 to-white px-5 py-3"
          >
            <div class="flex items-center gap-3">
              <span
                class="inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-amber-500 text-sm text-white shadow-sm shadow-amber-300/80"
              >
                🕒
              </span>
              <div>
                <p class="text-sm font-semibold text-slate-900">Últimos movimientos</p>
                <p class="text-[11px] text-amber-900/80">
                  Línea de tiempo con lo más reciente que hiciste.
                </p>
              </div>
            </div>
            <span class="hidden text-[11px] text-amber-900/70 md:inline">
              Se registran sólo acciones relacionadas con pedidos.
            </span>
          </div>

          <!-- Tabla súper estilizada -->
          <div class="max-h-[70vh] overflow-auto">
            <table class="w-full border-separate border-spacing-0 text-sm">
              <!-- Header principal -->
              <thead>
                <tr class="sticky top-0 z-20 bg-slate-900 text-[11px] uppercase tracking-wide text-slate-100">
                  <th class="px-5 py-3 text-left w-[210px] font-semibold">
                    Momento
                    <span class="block text-[10px] font-normal text-slate-300">
                      Fecha y número de movimiento
                    </span>
                  </th>
                  <th class="px-5 py-3 text-left w-[220px] font-semibold">
                    Módulo
                    <span class="block text-[10px] font-normal text-slate-300">
                      Dónde se registró la acción
                    </span>
                  </th>
                  <th class="px-5 py-3 text-left font-semibold">
                    Descripción del movimiento
                    <span class="block text-[10px] font-normal text-slate-300">
                      Lo que realmente pasó (texto detallado)
                    </span>
                  </th>
                </tr>
              </thead>

              <tbody>
                <!-- Línea vertical de timeline solo en la primera columna -->
                <tr v-if="items.length">
                  <td colspan="3" class="relative p-0">
                    <div
                      class="pointer-events-none absolute left-[42px] top-0 bottom-0 w-px bg-gradient-to-b from-amber-300 via-amber-200 to-transparent"
                    />
                  </td>
                </tr>

                <tr
                  v-for="(b, idx) in items"
                  :key="b.id ?? idx"
                  class="group transition-all"
                >
                  <!-- MOMENTO -->
                  <td
                    class="relative align-top bg-amber-50/40 px-5 py-4 text-xs sm:text-[13px] text-slate-600"
                  >
                    <div class="flex items-start gap-3">
                      <!-- punto timeline -->
                      <div class="pt-1">
                        <span
                          class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-[11px] text-amber-500 shadow-sm shadow-amber-200/80 group-hover:bg-amber-500 group-hover:text-white"
                        >
                          ●
                        </span>
                      </div>

                      <div class="space-y-0.5">
                        <p class="font-semibold text-slate-900">
                          {{ b.fecha }}
                        </p>
                        <p class="text-[11px] text-amber-700/90">
                          Movimiento #{{ total - idx }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <!-- MÓDULO -->
                  <td class="align-top bg-white px-5 py-4">
                    <div
                      class="inline-flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2 ring-1 ring-slate-100 shadow-[0_1px_0_rgba(15,23,42,0.04)] group-hover:bg-slate-900 group-hover:text-slate-50 group-hover:ring-slate-900/80 transition-colors"
                    >
                      <span
                        class="grid h-8 w-8 place-items-center rounded-xl bg-white text-base shadow-sm group-hover:bg-slate-800 group-hover:text-amber-200"
                      >
                        {{ moduloIcon(b.modulo) }}
                      </span>
                      <div class="flex flex-col gap-0.5">
                        <span
                          class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold capitalize ring-1"
                          :class="moduloPillClass(b.modulo)"
                        >
                          <span class="h-1.5 w-1.5 rounded-full bg-current/70" />
                          {{ b.modulo || '—' }}
                        </span>
                        <span class="text-[11px] text-slate-500 group-hover:text-slate-300">
                          Acción registrada en este módulo
                        </span>
                      </div>
                    </div>
                  </td>

                  <!-- DESCRIPCIÓN -->
                  <td class="align-top bg-white px-5 py-4">
                    <div
                      class="relative rounded-2xl border border-slate-100 bg-slate-50/90 px-4 py-3 text-sm text-slate-800 shadow-[0_1px_0_rgba(15,23,42,0.06)] group-hover:border-amber-300 group-hover:bg-amber-50/90 group-hover:shadow-md transition-colors"
                    >
                      <!-- comillas decorativas -->
                      <span
                        class="pointer-events-none absolute -left-3 -top-3 inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-sm text-amber-400 shadow-md shadow-amber-100"
                      >
                        "
                      </span>
                      <p>
                        {{ b.descripcion || '—' }}
                      </p>
                    </div>
                  </td>
                </tr>

                <tr v-if="items.length === 0">
                  <td colspan="3" class="px-4 py-10 text-center text-sm text-slate-500 bg-white">
                    Aún no hay movimientos registrados en tu bitácora.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="links.length" class="mt-4 flex justify-end gap-2">
          <Link
            v-for="(l, i) in links"
            :key="i"
            :href="l.url || '#'"
            v-html="l.label"
            :class="[
              'min-w-9 select-none rounded-full border px-3 py-1.5 text-center text-sm transition',
              l.active
                ? 'border-amber-600 bg-amber-600 text-white shadow-sm shadow-amber-300/80'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-amber-50',
              !l.url && 'pointer-events-none opacity-40',
            ]"
            preserve-state
            preserve-scroll
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
