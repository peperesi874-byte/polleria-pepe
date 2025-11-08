<!-- resources/js/Pages/Admin/Bitacora/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const props = defineProps({
  logs: Object,
  filters: Object,
  // opcional: lista de módulos desde el backend; si no llega, usamos un set por defecto
  availableModules: { type: Array, default: () => [] },
})

/** Filtros */
const modulo  = ref(props.filters?.modulo ?? '')
const q       = ref(props.filters?.q ?? '')
const user_id = ref(props.filters?.user_id ?? '')

/** Opciones de módulo (desplegable) */
const moduleOptions = computed(() => {
  const base = props.availableModules.length
    ? props.availableModules
    : ['Usuarios', 'Pedidos', 'Inventario', 'Reportes', 'Productos']
  // normalizamos a objetos {value,label}
  return [{ value: '', label: 'Todos los módulos' }].concat(
    base.map(m => ({ value: String(m), label: String(m) }))
  )
})

/** Debounce para filtros */
let t = null
watch([modulo, q, user_id], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('admin.bitacora.index'),
      { modulo: modulo.value, q: q.value, user_id: user_id.value || '' },
      { preserveState: true, replace: true }
    )
  }, 300)
})

/** Limpiar */
const clearFilters = () => {
  modulo.value = ''
  q.value = ''
  user_id.value = ''
  router.get(route('admin.bitacora.index'), {}, { preserveState: false, replace: true })
}
</script>

<template>
  <Head title="Bitácora" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">📓</span>
        <h2 class="text-2xl font-semibold text-gray-800">Bitácora de acciones</h2>
        <button
          type="button"
          @click="clearFilters"
          class="ml-auto inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm hover:bg-neutral-50"
          title="Limpiar filtros"
        >
          🧹 Limpiar
        </button>
      </div>
    </template>

    <div class="max-w-7xl mx-auto p-6 space-y-5">
      <!-- Filtros -->
      <div class="rounded-2xl border bg-white p-4 flex flex-wrap items-center gap-3">
        <!-- Módulo: ahora desplegable -->
        <div class="flex-1 min-w-[220px]">
          <label class="block text-xs font-medium text-neutral-500 mb-1">Módulo</label>
          <select
            v-model="modulo"
            class="w-full rounded-xl border px-3 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
          >
            <option v-for="opt in moduleOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>

        <!-- Palabra clave -->
        <div class="flex-1 min-w-[260px]">
          <label class="block text-xs font-medium text-neutral-500 mb-1">Buscar palabra clave</label>
          <input
            v-model="q"
            placeholder="Ej. movimiento, cancelado, precio..."
            class="w-full rounded-xl border px-3 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
          />
        </div>

        <!-- ID Usuario -->
        <div class="w-40">
          <label class="block text-xs font-medium text-neutral-500 mb-1">ID Usuario</label>
          <input
            v-model="user_id"
            type="number"
            min="0"
            placeholder="0"
            class="w-full rounded-xl border px-3 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
          />
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-2xl border bg-white">
        <table class="w-full text-sm">
          <thead class="bg-indigo-50 text-indigo-900 uppercase text-xs">
            <tr>
              <th class="px-3 py-3 text-left">Fecha</th>
              <th class="px-3 py-3 text-left">Usuario</th>
              <th class="px-3 py-3 text-left">Módulo</th>
              <th class="px-3 py-3 text-left">Acción</th>
              <th class="px-3 py-3 text-left">ID Ref</th>
              <th class="px-3 py-3 text-left">IP</th>
              <th class="px-3 py-3 text-left">Meta</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="l in props.logs.data" :key="l.id" class="border-t hover:bg-gray-50">
              <td class="px-3 py-2">{{ l.fecha }}</td>
              <td class="px-3 py-2">{{ l.user }}</td>
              <td class="px-3 py-2">
                <span class="inline-flex items-center rounded-full bg-neutral-100 text-neutral-700 px-2 py-0.5 text-xs">
                  {{ l.modulo }}
                </span>
              </td>
              <td class="px-3 py-2">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs ring-1"
                      :class="{
                        'bg-emerald-50 text-emerald-700 ring-emerald-200': ['crear','alta','insertar'].some(k => (l.accion||'').includes(k)),
                        'bg-amber-50 text-amber-700 ring-amber-200': ['actualizar','editar','update'].some(k => (l.accion||'').includes(k)),
                        'bg-rose-50 text-rose-700 ring-rose-200': ['eliminar','borrar','delete'].some(k => (l.accion||'').includes(k)),
                        'bg-sky-50 text-sky-700 ring-sky-200': ['exportar','pdf','csv'].some(k => (l.accion||'').includes(k)),
                        'bg-indigo-50 text-indigo-700 ring-indigo-200': true
                      }">
                  {{ l.accion }}
                </span>
              </td>
              <td class="px-3 py-2">
                <Link v-if="l.ref_id" :href="'#'+l.ref_id" class="text-indigo-600 hover:underline">#{{ l.ref_id }}</Link>
                <span v-else>—</span>
              </td>
              <td class="px-3 py-2">
                <span v-if="l.ip" class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs">{{ l.ip }}</span>
                <span v-else>—</span>
              </td>
              <td class="px-3 py-2 text-xs">
                <details>
                  <summary class="cursor-pointer text-indigo-600">Ver meta</summary>
                  <pre class="mt-1 whitespace-pre-wrap text-gray-700">{{ JSON.stringify(l.meta, null, 2) }}</pre>
                </details>
              </td>
            </tr>

            <tr v-if="props.logs.data.length === 0">
              <td colspan="7" class="px-3 py-8 text-center text-gray-500">
                Sin registros de acciones.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="flex justify-end gap-2">
        <Link v-for="(lnk,i) in props.logs.links" :key="i"
              :href="lnk.url || '#'" v-html="lnk.label"
              :class="[
                'px-3 py-1.5 rounded-xl border text-sm',
                lnk.active ? 'bg-indigo-600 text-white' : 'bg-white hover:bg-indigo-50',
                !lnk.url && 'opacity-40 pointer-events-none'
              ]" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
