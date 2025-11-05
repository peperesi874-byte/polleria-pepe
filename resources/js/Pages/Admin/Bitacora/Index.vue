<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  logs: Object,
  filters: Object,
})

const modulo  = ref(props.filters?.modulo ?? '')
const q       = ref(props.filters?.q ?? '')
const user_id = ref(props.filters?.user_id ?? '')

// 🔹 Filtro reactivo con retraso leve
let t = null
watch([modulo, q, user_id], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('admin.bitacora.index'),
      { modulo: modulo.value, q: q.value, user_id: user_id.value || undefined },
      { preserveState: true, replace: true }
    )
  }, 300)
})

// 🔹 Botón Limpiar
function clearAll() {
  modulo.value = ''
  q.value = ''
  user_id.value = ''
  router.get(route('admin.bitacora.index'), {}, { replace: true })
}

// 🔹 Badges de color por tipo de acción
const actionBadge = (a) => {
  const map = {
    crear:        'bg-emerald-100 text-emerald-800',
    actualizar:   'bg-indigo-100 text-indigo-800',
    eliminar:     'bg-rose-100 text-rose-800',
    movimiento:   'bg-amber-100 text-amber-800',
    exportar_pdf: 'bg-sky-100 text-sky-800',
    exportar_csv: 'bg-sky-100 text-sky-800',
    estado_cambiado: 'bg-violet-100 text-violet-800',
    asignado:     'bg-cyan-100 text-cyan-800',
    desasignado:  'bg-gray-100 text-gray-700',
  }
  return (map[a] ?? 'bg-gray-100 text-gray-700') + ' px-2 py-0.5 rounded-full text-xs font-semibold'
}

async function copyMeta(meta) {
  try {
    await navigator.clipboard.writeText(JSON.stringify(meta ?? {}, null, 2))
  } catch (e) {}
}

// 🔹 Enlaces a registros
function refLinkFor(row) {
  const id = row.ref_id
  if (!id) return null

  switch (row.modulo) {
    case 'pedidos':
      return route().has('admin.pedidos.show') ? route('admin.pedidos.show', id) : null
    case 'inventario':
      return route().has('admin.inventario.movimientos') ? route('admin.inventario.movimientos', id) : null
    case 'usuarios':
      return route().has('admin.usuarios.edit') ? route('admin.usuarios.edit', id) : null
    case 'productos':
      return route().has('productos.edit') ? route('productos.edit', id) : null
    default:
      return null
  }
}
</script>

<template>
  <Head title="Bitácora" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">🧾</span>
          Bitácora de acciones
        </h2>

        <!-- 🔹 Solo botón limpiar -->
        <button
          @click="clearAll"
          class="rounded-lg border px-3 py-2 text-sm text-rose-700 hover:bg-rose-50"
          title="Limpiar filtros"
        >
          🧹 Limpiar
        </button>
      </div>
    </template>

    <div class="mx-auto max-w-7xl px-4 py-6 space-y-5">
      <!-- 🔹 Filtros -->
      <div class="rounded-2xl border bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Módulo</label>
            <input
              v-model="modulo"
              placeholder="usuarios, pedidos, inventario…"
              class="rounded border px-3 py-2 w-full"
            />
          </div>

          <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-500 mb-1">Buscar palabra clave</label>
            <input
              v-model="q"
              placeholder="Ej. movimiento, cancelado, precio…"
              class="rounded border px-3 py-2 w-full"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">ID Usuario</label>
            <input
              v-model="user_id"
              type="number"
              min="0"
              placeholder="0 = todos"
              class="rounded border px-3 py-2 w-full"
            />
          </div>
        </div>
      </div>

      <!-- 🔹 Tabla -->
      <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="relative overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-indigo-50 text-indigo-900 uppercase text-xs">
              <tr>
                <th class="px-4 py-3 text-left">Fecha</th>
                <th class="px-4 py-3 text-left">Usuario</th>
                <th class="px-4 py-3 text-left">Módulo</th>
                <th class="px-4 py-3 text-left">Acción</th>
                <th class="px-4 py-3 text-left">ID Ref</th>
                <th class="px-4 py-3 text-left">IP</th>
                <th class="px-4 py-3 text-left">Meta</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in props.logs.data"
                :key="row.id"
                class="border-t last:border-0 hover:bg-gray-50"
              >
                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ row.fecha }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                      {{ (row.user ?? 'S').slice(0,1).toUpperCase() }}
                    </span>
                    <span class="text-gray-800">{{ row.user }}</span>
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700">
                    {{ row.modulo }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span :class="actionBadge(row.accion)">{{ row.accion }}</span>
                </td>
                <td class="px-4 py-3">
                  <template v-if="refLinkFor(row)">
                    <Link
                      :href="refLinkFor(row)"
                      class="text-indigo-600 hover:underline font-medium"
                    >#{{ row.ref_id }}</Link>
                  </template>
                  <template v-else>
                    <span class="text-gray-500">—</span>
                  </template>
                </td>
                <td class="px-4 py-3">
                  <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700">
                    {{ row.ip ?? '—' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <details class="group">
                    <summary class="cursor-pointer text-indigo-600 hover:underline select-none">
                      Ver meta
                    </summary>
                    <pre class="mt-1 rounded-lg bg-gray-50 p-2 text-xs text-gray-800 whitespace-pre-wrap">
{{ JSON.stringify(row.meta ?? {}, null, 2) }}
                    </pre>
                    <button
                      class="mt-1 rounded-md border px-2 py-1 text-xs hover:bg-gray-50"
                      @click="copyMeta(row.meta)"
                    >Copiar</button>
                  </details>
                </td>
              </tr>

              <tr v-if="props.logs.data.length === 0">
                <td colspan="7" class="px-4 py-12">
                  <div class="flex flex-col items-center justify-center text-center text-gray-500">
                    <div class="mb-2 text-5xl">🗒</div>
                    <div class="text-lg font-semibold">Sin registros de acciones</div>
                    <div class="text-sm">
                      Realiza alguna acción (crear usuario, registrar movimiento, exportar reporte) y aparecerá aquí.
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 🔹 Paginación -->
        <div class="flex justify-end gap-2 p-3">
          <Link
            v-for="(lnk, i) in props.logs.links"
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
    </div>
  </AuthenticatedLayout>
</template>