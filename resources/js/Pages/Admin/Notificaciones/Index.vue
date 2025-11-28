<!-- resources/js/Pages/Admin/Notificaciones/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const props = defineProps({
  notificaciones: {
    type: [Object, Array],
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

/* --------- Helper de botón neutro (para contenido) --------- */
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

  if (Array.isArray(n)) return n
  if (n && Array.isArray(n.data)) return n.data

  return []
})

const links = computed(() => {
  const n = props.notificaciones
  return n && Array.isArray(n.links) ? n.links : []
})

/* Métricas para el header */
const totalCount = computed(() => items.value.length)
const unreadCount = computed(() => items.value.filter(n => !n.leida).length)

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
  if (t.includes('pedido'))     return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
  if (t.includes('error'))      return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}
</script>

<template>
  <Head title="Notificaciones" />

  <AuthenticatedLayout>
    <!-- ===== HEADER UNIFICADO ===== -->
    <template #header>
      <div class="pt-4 pb-6">
        <div
          class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
        >
          <!-- blobs -->
          <div class="pointer-events-none absolute -left-20 -top-24 h-40 w-40 rounded-full bg-white/20 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-44 rounded-full bg-black/20 blur-3xl" />

          <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Izquierda -->
            <div class="space-y-3">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
              >
                <span class="text-lg">🔔</span>
                <span>Centro de alertas — Pollería Pepe</span>
              </div>

              <h2 class="text-3xl font-extrabold tracking-tight text-white">
                Notificaciones y avisos del sistema
              </h2>
              <p class="text-sm text-amber-50/90">
                Revisa alertas de inventario, pedidos y mensajes importantes en un solo lugar.
              </p>

              <!-- Chips resumen -->
              <div class="flex flex-wrap gap-2 text-[11px] text-amber-50/95">
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-white/95 px-3 py-1 font-semibold text-amber-900 shadow-sm ring-1 ring-white/70"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                  {{ totalCount }} notificaciones registradas
                </span>
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-amber-200" />
                  {{ unreadCount }} sin leer
                </span>
              </div>
            </div>

            <!-- Derecha: acciones rápidas -->
            <div class="flex flex-col items-end gap-2">
              <div class="flex flex-wrap justify-end gap-2">
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-2xl bg-white/95 px-3 py-2 text-xs font-semibold text-amber-900 shadow-sm hover:bg-amber-50"
                  @click="marcarTodas"
                >
                  ✔ Marcar todas como leídas
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-2xl bg-black/20 px-3 py-2 text-xs font-semibold text-amber-50 hover:bg-black/30"
                  @click="eliminarLeidas"
                >
                  🧹 Eliminar leídas
                </button>
                <button
                  type="button"
                  class="inline-flex items-center gap-2 rounded-2xl bg-rose-500/95 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-600"
                  @click="eliminarTodas"
                >
                  ⛔ Eliminar todas
                </button>
              </div>

              <Link
                :href="route('admin.dashboard')"
                class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1.5 text-[11px] font-medium text-amber-50 hover:bg-black/30"
              >
                ← Volver al panel
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/20 to-slate-50">
      <div class="mx-auto max-w-7xl px-4 py-6 space-y-5 lg:px-6">
        <!-- Buscador -->
        <div
          class="rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-amber-200 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
        >
          <div>
            <p class="text-sm font-semibold text-slate-900 flex items-center gap-2">
              <span class="text-lg">🔎</span>
              Buscar notificaciones
            </p>
            <p class="text-xs text-slate-500">
              Filtra por título o mensaje para encontrar rápidamente un aviso.
            </p>
          </div>

          <div class="w-full md:w-[26rem]">
            <div class="relative">
              <input
                v-model="q"
                type="search"
                placeholder="Buscar por título o mensaje…"
                class="w-full rounded-2xl border border-amber-300 bg-amber-50/60 px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
              <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-amber-400">
                🔍
              </span>
            </div>
          </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
          <div class="max-h-[70vh] overflow-auto">
            <table class="w-full border-collapse text-sm">
              <thead class="sticky top-0 z-10 bg-slate-900 text-amber-50 text-xs uppercase tracking-wide">
                <tr>
                  <th class="px-4 py-3 text-left w-[110px]">Fecha</th>
                  <th class="px-4 py-3 text-left w-[190px]">Título</th>
                  <th class="px-4 py-3 text-left">Mensaje</th>
                  <th class="px-4 py-3 text-left w-[140px]">Tipo</th>
                  <th class="px-4 py-3 text-right w-[170px]">Acciones</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="n in items"
                  :key="n.id"
                  class="border-t border-slate-100 text-slate-800 transition-colors odd:bg-white even:bg-amber-50/20 hover:bg-amber-50/60"
                >
                  <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                    {{ n.fecha }}
                  </td>

                  <td
                    class="px-4 py-3 font-medium text-slate-900 truncate max-w-[22ch]"
                    :title="n.titulo"
                  >
                    {{ n.titulo }}
                  </td>

                  <td class="px-4 py-3 text-slate-700">
                    <div class="line-clamp-2 max-w-[70ch]" :title="n.mensaje">
                      {{ n.mensaje }}
                    </div>
                  </td>

                  <td class="px-4 py-3">
                    <span
                      class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold"
                      :class="tipoPill(n.tipo)"
                    >
                      {{ n.tipo ?? '—' }}
                    </span>
                  </td>

                  <td class="px-4 py-3 text-right">
                    <span
                      v-if="n.leida"
                      class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200"
                    >
                      ✔ Leída
                    </span>

                    <button
                      v-else
                      :class="btn('outline')"
                      class="!py-1.5 !text-xs"
                      @click="marcarLeida(n.id)"
                    >
                      Marcar como leída
                    </button>
                  </td>
                </tr>

                <tr v-if="items.length === 0">
                  <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                    No hay notificaciones para mostrar.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginación -->
        <div v-if="links.length" class="flex justify-end gap-2">
          <Link
            v-for="(l, i) in links"
            :key="i"
            :href="l.url || '#'"
            v-html="l.label"
            :class="[
              'min-w-9 select-none rounded-full border px-3 py-1.5 text-center text-xs font-semibold transition',
              l.active
                ? 'border-amber-600 bg-amber-600 text-white shadow-sm shadow-amber-300/80'
                : 'border-amber-100 bg-white text-slate-700 hover:bg-amber-50',
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

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
