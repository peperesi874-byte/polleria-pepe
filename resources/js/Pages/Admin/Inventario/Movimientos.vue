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
    entrada: 'bg-emerald-100 text-emerald-700',
    salida:  'bg-rose-100 text-rose-700',
    ajuste:  'bg-indigo-100 text-indigo-700',
  }
  return (map[t] ?? 'bg-gray-100 text-gray-700') + ' px-2 py-0.5 rounded-full text-xs font-semibold'
}
</script>

<template>
  <Head :title="`Historial • ${producto.nombre}`" />

  <AuthenticatedLayout>
    <!-- ===== Header unificado (como los demás módulos) ===== -->
    <template #header>
      <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60 border">
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-100 text-indigo-700 text-lg">🧾</div>
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">
              Historial de movimientos
            </h2>
            <p class="mt-0.5 text-sm text-gray-500">
              Producto: <span class="font-medium text-gray-800">{{ producto.nombre }}</span>
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <a
            :href="urlExportCsv()"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50"
            title="Exportar CSV"
          >
            ⬇️ Exportar CSV
          </a>

          <Link
            :href="route('admin.inventario.index')"
            class="text-sm text-indigo-600 hover:underline"
          >
            ← Volver a inventario
          </Link>
        </div>
      </div>
    </template>

    <!-- ===== Contenido ===== -->
    <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
      <!-- Resumen -->
      <div class="grid sm:grid-cols-3 gap-4">
        <div class="rounded-xl border bg-white p-4">
          <div class="text-sm text-gray-500">Stock actual</div>
          <div
            class="text-2xl font-bold"
            :class="(inventario.stock_actual ?? 0) <= (inventario.stock_minimo ?? 0) ? 'text-rose-600' : 'text-emerald-700'"
          >
            {{ inventario.stock_actual }}
          </div>
        </div>
        <div class="rounded-xl border bg-white p-4">
          <div class="text-sm text-gray-500">Stock mínimo</div>
          <div class="text-2xl font-bold text-gray-800">{{ inventario.stock_minimo }}</div>
        </div>
        <div class="rounded-xl border bg-white p-4">
          <div class="text-sm text-gray-500">Precio</div>
          <div class="text-2xl font-bold text-gray-800">
            {{ new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(producto.precio ?? 0) }}
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="rounded-xl border bg-white p-4 flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Tipo</label>
          <select v-model="tipo" class="rounded-md border-gray-300 focus:ring-indigo-300">
            <option value="">Todos</option>
            <option value="entrada">Entrada</option>
            <option value="salida">Salida</option>
            <option value="ajuste">Ajuste</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Buscar (motivo)</label>
          <input v-model="q" type="text" class="rounded-md border-gray-300 focus:ring-indigo-300" placeholder="ej. compra, merma..." />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Desde</label>
          <input v-model="desde" type="date" class="rounded-md border-gray-300 focus:ring-indigo-300" />
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Hasta</label>
          <input v-model="hasta" type="date" class="rounded-md border-gray-300 focus:ring-indigo-300" />
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left w-16">#</th>
              <th class="px-4 py-3 text-left">Producto</th>
              <th class="px-4 py-3 text-left">Tipo</th>
              <th class="px-4 py-3 text-left">Cantidad</th>
              <th class="px-4 py-3 text-left">Δ</th>
              <th class="px-4 py-3 text-left">Stock resultante</th>
              <th class="px-4 py-3 text-left">Motivo</th>
              <th class="px-4 py-3 text-left">Usuario</th>
              <th class="px-4 py-3 text-left">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in movimientos.data" :key="m.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-600">{{ m.nro }}</td>
              <td class="px-4 py-3 text-gray-800 font-medium">{{ m.producto || producto.nombre }}</td>
              <td class="px-4 py-3">
                <span :class="badge(m.tipo)">{{ m.tipo }}</span>
              </td>
              <td class="px-4 py-3">{{ m.cantidad }}</td>
              <td class="px-4 py-3" :class="m.delta > 0 ? 'text-emerald-700' : (m.delta < 0 ? 'text-rose-700' : 'text-gray-700')">
                {{ m.delta > 0 ? `+${m.delta}` : m.delta }}
              </td>
              <td class="px-4 py-3 font-semibold text-gray-800">{{ m.stock_resultante }}</td>
              <td class="px-4 py-3 text-gray-700">{{ m.motivo ?? '—' }}</td>
              <td class="px-4 py-3 text-gray-700">{{ m.by }}</td>
              <td class="px-4 py-3 text-gray-500">{{ m.fecha }}</td>
            </tr>

            <tr v-if="movimientos.data.length === 0">
              <td colspan="9" class="px-4 py-6 text-center text-gray-500">
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
            'px-3 py-1 rounded-md border text-sm',
            lnk.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-indigo-50',
            !lnk.url && 'pointer-events-none opacity-40'
          ]"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
