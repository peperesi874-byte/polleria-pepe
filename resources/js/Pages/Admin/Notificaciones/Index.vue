<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const props = defineProps({
  // puede venir como paginator { data, links } o incluso como array plano
  notificaciones: {
    type: [Object, Array],
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

/* --------- Helpers de botón --------- */
const btn = (variant = 'outline') =>
  [
    'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-300',
    variant === 'solid'
      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
      : 'border border-gray-300 text-gray-700 hover:bg-gray-50',
  ].join(' ')

/* --------- Buscador (debounce) --------- */
const q = ref(props.filters?.q ?? '')
let t = null
watch(q, () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('admin.notificaciones.index'),
      { q: q.value },
      { preserveState: true, replace: true },
    )
  }, 350)
})

/* --------- Lista / Paginación --------- */
const items = computed(() => {
  const n = props.notificaciones

  // Si te llega como array plano
  if (Array.isArray(n)) {
    return n
  }

  // Si llega como paginator { data, links, ... }
  if (n && Array.isArray(n.data)) {
    return n.data
  }

  return []
})

const links = computed(() => {
  const n = props.notificaciones
  return n && Array.isArray(n.links) ? n.links : []
})

/* --------- Acciones: leer --------- */
function marcarLeida(id) {
  router.patch(route('admin.notificaciones.read', id), {}, { preserveScroll: true })
}

function marcarTodas() {
  router.patch(route('admin.notificaciones.read_all'), {}, { preserveScroll: true })
}

/* --------- Acciones: borrar --------- */
function eliminarLeidas() {
  if (!confirm('¿Eliminar todas las notificaciones que ya están marcadas como leídas?')) return
  router.delete(route('admin.notificaciones.delete_read'), {
    preserveScroll: true,
  })
}

function eliminarTodas() {
  if (!confirm('¿Eliminar TODAS las notificaciones? Esta acción no se puede deshacer.')) return
  router.delete(route('admin.notificaciones.delete_all'), {
    preserveScroll: true,
  })
}

/* Badge por tipo */
function tipoPill(tipo) {
  const t = (tipo || '').toString().toLowerCase()
  if (t.includes('inventario')) return 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'
  if (t.includes('pedido')) return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
  if (t.includes('error')) return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}
</script>

<template>
  <Head title="Notificaciones" />

  <AuthenticatedLayout>
    <!-- Header -->
    <template #header>
      <div
        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <span class="inline-grid h-9 w-9 place-items-center rounded-xl bg-indigo-100 text-indigo-700">
            🔔
          </span>
          <div>
            <h2 class="text-2xl font-semibold text-gray-800">Notificaciones</h2>
            <p class="mt-0.5 text-sm text-gray-500">Historial de avisos del sistema.</p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button type="button" :class="btn('outline')" @click="marcarTodas">
            Marcar todas como leídas
          </button>

          <button type="button" :class="btn('outline')" @click="eliminarLeidas">
            Eliminar leídas
          </button>
          <button type="button" :class="btn('solid')" @click="eliminarTodas">
            Eliminar todas
          </button>

          <Link :href="route('admin.dashboard')" class="text-sm text-indigo-600 hover:underline">
            ← Volver al panel
          </Link>
        </div>
      </div>
    </template>

    <!-- Contenido -->
    <div class="mx-auto max-w-7xl p-6 space-y-4">
      <!-- Buscador -->
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative w-full md:w-[28rem]">
          <input
            v-model="q"
            type="search"
            placeholder="Buscar por título o mensaje…"
            class="w-full rounded-xl border border-gray-300/80 bg-white px-4 py-2.5 pr-10 text-sm shadow-sm outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
            🔎
          </span>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="max-h-[70vh] overflow-auto">
          <table class="w-full border-collapse text-sm">
            <thead class="sticky top-0 z-10 bg-gray-50 text-gray-600">
              <tr class="uppercase text-xs">
                <th class="px-4 py-3 text-left w-[110px]">Fecha</th>
                <th class="px-4 py-3 text-left w-[180px]">Título</th>
                <th class="px-4 py-3 text-left">Mensaje</th>
                <th class="px-4 py-3 text-left w-[120px]">Tipo</th>
                <th class="px-4 py-3 text-right w-[160px]">Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="n in items"
                :key="n.id"
                class="border-t border-gray-100 odd:bg-white even:bg-gray-50/30 hover:bg-gray-50/80"
              >
                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                  {{ n.fecha }}
                </td>

                <td
                  class="px-4 py-3 font-medium text-gray-900 truncate max-w-[22ch]"
                  :title="n.titulo"
                >
                  {{ n.titulo }}
                </td>

                <td class="px-4 py-3 text-gray-700">
                  <div class="line-clamp-2 max-w-[70ch]" :title="n.mensaje">
                    {{ n.mensaje }}
                  </div>
                </td>

                <td class="px-4 py-3">
                  <span
                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="tipoPill(n.tipo)"
                  >
                    {{ n.tipo ?? '—' }}
                  </span>
                </td>

                <td class="px-4 py-3 text-right">
                  <span
                    v-if="n.leida"
                    class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200"
                  >
                    Leída
                  </span>

                  <button
                    v-else
                    :class="btn('outline')"
                    class="!py-1.5"
                    @click="marcarLeida(n.id)"
                  >
                    Marcar leído
                  </button>
                </td>
              </tr>

              <tr v-if="items.length === 0">
                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                  No hay notificaciones.
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
            'min-w-9 select-none rounded-lg border px-3 py-1.5 text-center text-sm transition',
            l.active
              ? 'border-indigo-600 bg-indigo-600 text-white'
              : 'border-gray-300 bg-white text-gray-700 hover:bg-indigo-50',
            !l.url && 'pointer-events-none opacity-40',
          ]"
          preserve-state
          preserve-scroll
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
