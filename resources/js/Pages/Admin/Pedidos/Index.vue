<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  pedidos: Object,        // paginator
  filters: Object,        // { q, estado, asignado }
  estados: Array,
})

const q = ref(props.filters?.q ?? '')
const estado = ref(props.filters?.estado ?? '')
const asignado = ref(props.filters?.asignado ?? '') // '', 'any', 'none'

let t = null
watch([q, estado, asignado], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('admin.pedidos.index'),
      { q: q.value, estado: estado.value, asignado: asignado.value },
      { preserveState: true, replace: true }
    )
  }, 300)
})

const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)
</script>

<template>
  <div class="max-w-7xl mx-auto px-6 py-8">
    <div class="mb-6 flex items-center justify-between gap-3">
      <h1 class="text-2xl md:text-3xl font-semibold text-indigo-800">📦 Pedidos</h1>
    </div>

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
        <option v-for="e in estados" :key="e" :value="e">
          {{ e.replace('_',' ') }}
        </option>
      </select>

      <!-- 👇 Nuevo filtro -->
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
            <th class="px-4 py-3 text-left">Asignado a</th> <!-- 👈 Nueva columna -->
            <th class="px-4 py-3 text-left">Total</th>
            <th class="px-4 py-3 text-left">Creado</th>
            <th class="px-4 py-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(p, i) in pedidos.data"
            :key="p.id"
            class="hover:bg-gray-50 transition"
          >
            <td class="px-4 py-3 text-gray-600">{{ (pedidos.from ?? 1) + i }}</td>
            <td class="px-4 py-3 font-medium text-gray-800">{{ p.folio ?? '—' }}</td>
            <td class="px-4 py-3">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-semibold',
                  p.estado === 'cancelado' ? 'bg-red-100 text-red-700'
                  : p.estado === 'entregado' ? 'bg-green-100 text-green-700'
                  : 'bg-amber-100 text-amber-700'
                ]"
              >
                {{ p.estado.replace('_',' ') }}
              </span>
            </td>
            <td class="px-4 py-3 capitalize">{{ p.tipo.replace('_',' ') }}</td>
            <td class="px-4 py-3">{{ p.items }}</td>
            <td class="px-4 py-3">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs',
                  p.repartidor ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'
                ]"
              >
                {{ p.repartidor ?? '—' }}
              </span>
            </td>
            <td class="px-4 py-3">{{ money(p.total) }}</td>
            <td class="px-4 py-3 text-gray-500">{{ p.created_at }}</td>
            <td class="px-4 py-3 text-right">
              <Link
                :href="route('admin.pedidos.show', p.id)"
                class="text-indigo-600 hover:text-indigo-700 font-medium"
              >Ver detalle →</Link>
            </td>
          </tr>

          <tr v-if="pedidos.data.length === 0">
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
</template>
