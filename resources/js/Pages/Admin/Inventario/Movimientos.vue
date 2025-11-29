<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  producto: Object,
  inventario: Object,
  movimientos: Object,   // paginator
  filters: Object,       // { tipo, q, desde, hasta }
})

/* ---------------- Filtros ---------------- */
const tipo  = ref(props.filters?.tipo  ?? '')
const q     = ref(props.filters?.q     ?? '')
const desde = ref(props.filters?.desde ?? '')
const hasta = ref(props.filters?.hasta ?? '')

let t = null
watch([tipo, q, desde, hasta], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('admin.inventario.movimientos', props.producto.id),
      { tipo: tipo.value, q: q.value, desde: desde.value, hasta: hasta.value },
      { preserveState: true, replace: true }
    )
  }, 300)
})

/** URL para exportar CSV respetando filtros actuales */
function urlExportCsv () {
  const params = new URLSearchParams()
  params.set('producto_id', props.producto.id)
  if (tipo.value)  params.set('tipo',  tipo.value)
  if (q.value)     params.set('q',     q.value)
  if (desde.value) params.set('desde', desde.value)
  if (hasta.value) params.set('hasta', hasta.value)
  return route('admin.inventario.historial.csv') + '?' + params.toString()
}

const badge = (t) => {
  const map = {
    entrada: 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
    salida:  'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
    ajuste:  'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200',
  }
  return (map[t] ?? 'bg-slate-100 text-slate-700 ring-1 ring-slate-200') +
    ' inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold'
}
</script>

<template>
  <Head :title="`Historial • ${producto.nombre}`" />

  <AuthenticatedLayout>
    <!-- ===== HEADER ESTILO POLLERÍA PEPE ===== -->
    <template #header>
      <div class="mx-auto max-w-7xl px-4 lg:px-6">
        <div
          class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 px-6 py-5 shadow-lg ring-1 ring-amber-400/70"
        >
          <!-- decor -->
          <div class="pointer-events-none absolute -left-16 -top-16 h-32 w-32 rounded-full bg-white/20 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-32 w-32 rounded-full bg-black/20 blur-3xl" />

          <div class="relative flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-start gap-3">
              <div class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20 text-amber-50 text-xl shadow-md shadow-black/20">
                🧾
              </div>

              <div class="space-y-1">
                <div
                  class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
                >
                  <span class="uppercase tracking-wide">Historial de movimientos</span>
                  <span class="h-1 w-1 rounded-full bg-emerald-400" />
                  <span class="truncate max-w-[160px] sm:max-w-xs">{{ producto.nombre }}</span>
                </div>

                <h2 class="text-2xl font-extrabold tracking-tight text-white">
                  Kardex de inventario
                </h2>
                <p class="text-xs text-amber-50/90">
                  Revisa entradas, salidas y ajustes registrados para este producto.
                </p>
              </div>
            </div>

            <div class="flex flex-col items-end gap-2">
              <a
                :href="urlExportCsv()"
                class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-white/95 px-4 py-1.5 text-xs font-semibold text-amber-800 shadow-sm hover:bg-amber-50"
                title="Exportar CSV"
              >
                ⬇️ Exportar CSV
              </a>

              <Link
                :href="route('admin.inventario.index')"
                class="inline-flex items-center gap-1 text-xs font-medium text-amber-50/90 hover:text-white hover:underline"
              >
                ← Volver a inventario
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/15 to-slate-50">
      <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
        <!-- Resumen -->
        <div class="grid gap-4 sm:grid-cols-3">
          <div
            class="rounded-3xl border border-amber-100 bg-white/95 p-4 shadow-sm flex flex-col gap-1"
          >
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Stock actual
            </p>
            <p
              class="text-2xl font-extrabold"
              :class="(inventario.stock_actual ?? 0) <= (inventario.stock_minimo ?? 0)
                ? 'text-rose-600'
                : 'text-emerald-700'"
            >
              {{ inventario.stock_actual }}
            </p>
            <p
              v-if="(inventario.stock_actual ?? 0) <= (inventario.stock_minimo ?? 0)"
              class="text-[11px] font-medium text-rose-600"
            >
              ⚠ Producto bajo mínimo
            </p>
          </div>

          <div class="rounded-3xl border border-amber-100 bg-white/95 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Stock mínimo
            </p>
            <p class="mt-1 text-2xl font-extrabold text-slate-900">
              {{ inventario.stock_minimo }}
            </p>
            <p class="text-[11px] text-slate-500">
              Umbral para alertas de inventario.
            </p>
          </div>

          <div class="rounded-3xl border border-amber-100 bg-white/95 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
              Precio unitario
            </p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-700">
              {{
                new Intl.NumberFormat('es-MX', {
                  style: 'currency',
                  currency: 'MXN'
                }).format(producto.precio ?? 0)
              }}
            </p>
            <p class="text-[11px] text-slate-500">
              Se usa para reportes y valuación de inventario.
            </p>
          </div>
        </div>

        <!-- Filtros -->
        <div
          class="flex flex-wrap items-end gap-3 rounded-2xl bg-white/95 px-4 py-4 shadow-sm ring-1 ring-amber-100"
        >
          <div class="mb-2 w-full sm:w-auto">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
              Filtros de historial
            </p>
            <p class="text-[11px] text-slate-500">
              Combina tipo, texto y fechas para acotar los movimientos.
            </p>
          </div>

          <div class="flex flex-wrap items-end gap-3 w-full sm:w-auto">
            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-600">Tipo</label>
              <select
                v-model="tipo"
                class="w-40 rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              >
                <option value="">Todos</option>
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
                <option value="ajuste">Ajuste</option>
              </select>
            </div>

            <div class="flex-1 min-w-[180px]">
              <label class="mb-1 block text-xs font-semibold text-slate-600">
                Buscar (motivo)
              </label>
              <div class="relative">
                <input
                  v-model="q"
                  type="text"
                  placeholder="ej. compra, merma…"
                  class="w-full rounded-2xl border border-amber-200 bg-amber-50/40 px-3 py-2 pr-8 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                />
                <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-amber-400 text-sm">
                  🔍
                </span>
              </div>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-600">Desde</label>
              <input
                v-model="desde"
                type="date"
                class="w-36 rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold text-slate-600">Hasta</label>
              <input
                v-model="hasta"
                type="date"
                class="w-36 rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
            </div>
          </div>
        </div>

        <!-- Tabla estilo inventario -->
<div
  class="overflow-x-auto rounded-3xl border border-amber-100 bg-gradient-to-b from-amber-50/50 to-white shadow-sm"
>
  <table class="min-w-full text-sm border-collapse">
    <thead>
      <tr
        class="bg-slate-950 text-[11px] font-semibold uppercase tracking-wide text-amber-50"
      >
        <th class="px-4 py-3 text-left w-16 border-b border-slate-900/70">
          #
        </th>
        <th class="px-4 py-3 text-left border-b border-slate-900/70">
          Producto
        </th>
        <th class="px-4 py-3 text-left border-b border-slate-900/70">
          Tipo
        </th>
        <th class="px-4 py-3 text-center border-b border-slate-900/70">
          Cantidad
        </th>
        <th class="px-4 py-3 text-center border-b border-slate-900/70">
          Δ
        </th>
        <th class="px-4 py-3 text-center border-b border-slate-900/70">
          Stock resultante
        </th>
        <th class="px-4 py-3 text-left border-b border-slate-900/70">
          Motivo
        </th>
        <th class="px-4 py-3 text-left border-b border-slate-900/70">
          Usuario
        </th>
        <th class="px-4 py-3 text-right border-b border-slate-900/70">
          Fecha
        </th>
      </tr>
    </thead>

    <tbody class="text-xs sm:text-sm">
      <tr
        v-for="m in movimientos.data"
        :key="m.id"
        class="group align-middle border-t border-amber-100 last:border-b-0 text-slate-800"
        :class="[
          // mismo patrón que inventario: blanco / cremita
          m.nro % 2 === 0 ? 'bg-white' : 'bg-amber-50/40',
          'hover:bg-amber-50/90 transition-colors'
        ]"
      >
        <td class="px-4 py-2.5 text-[12px] text-slate-500">
          {{ m.nro }}
        </td>

        <td class="px-4 py-2.5 font-medium text-slate-900">
          {{ m.producto || producto.nombre }}
        </td>

        <td class="px-4 py-2.5">
          <span :class="badge(m.tipo)">{{ m.tipo }}</span>
        </td>

        <td class="px-4 py-2.5 text-center">
          {{ m.cantidad }}
        </td>

        <td
          class="px-4 py-2.5 text-center font-semibold"
          :class="m.delta > 0
            ? 'text-emerald-600'
            : (m.delta < 0 ? 'text-rose-600' : 'text-slate-700')"
        >
          {{ m.delta > 0 ? `+${m.delta}` : m.delta }}
        </td>

        <td class="px-4 py-2.5 text-center font-semibold text-amber-900">
          {{ m.stock_resultante }}
        </td>

        <td class="px-4 py-2.5 text-slate-700">
          {{ m.motivo ?? '—' }}
        </td>

        <td class="px-4 py-2.5 text-slate-700">
          {{ m.by }}
        </td>

        <td class="px-4 py-2.5 text-right text-[12px] text-slate-500">
          {{ m.fecha }}
        </td>
      </tr>

      <tr v-if="movimientos.data.length === 0">
        <td
          colspan="9"
          class="px-4 py-6 text-center text-sm text-slate-500 bg-amber-50/60"
        >
          No hay movimientos con esos filtros.
        </td>
      </tr>
    </tbody>
  </table>
</div>


        

        

        <!-- Paginación -->
        <div class="flex justify-end gap-2">
          <Link
            v-for="(lnk, i) in movimientos.links"
            :key="i"
            :href="lnk.url || '#'"
            v-html="lnk.label"
            :class="[
              'min-w-9 select-none rounded-full border px-3 py-1.5 text-center text-xs font-semibold transition',
              lnk.active
                ? 'border-amber-600 bg-amber-600 text-white shadow-sm shadow-amber-300/80'
                : 'border-amber-100 bg-white text-slate-700 hover:bg-amber-50',
              !lnk.url && 'pointer-events-none opacity-40',
            ]"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
