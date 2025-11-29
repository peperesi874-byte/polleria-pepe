<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  role:    { type: String, default: 'admin' }, // 'admin' | 'vendedor'
  pedidos: { type: Object, required: true },   // paginator
  q:       { type: String, default: '' },
  estado:  { type: String, default: '' },
  asignado:{ type: String, default: '' },      // '', 'any', 'none'
  desde:   { type: String, default: '' },      // YYYY-MM-DD
  hasta:   { type: String, default: '' },      // YYYY-MM-DD
  estados: { type: Array,  default: () => [] },
})

/* --------- Prefijo/base según rol --------- */
const base = computed(() => (props.role === 'vendedor' ? 'vendedor' : 'admin'))

/* --------- Filtros con debounce --------- */
const q        = ref(props.q ?? '')
const estado   = ref(props.estado ?? '')
const asignado = ref(props.asignado ?? '')
const desde    = ref(props.desde ?? '')
const hasta    = ref(props.hasta ?? '')

let t = null
watch([q, estado, asignado, desde, hasta], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route(base.value + '.pedidos.index'),
      {
        q: q.value,
        estado: estado.value,
        asignado: asignado.value,
        desde: desde.value || '',
        hasta: hasta.value || '',
      },
      { preserveState: true, replace: true }
    )
  }, 300)
})

/* ---------------- Helpers UI ---------------- */
const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

const estadoPill = e => {
  if (e === 'cancelado')  return 'bg-rose-100 text-rose-700 ring-1 ring-rose-200'
  if (e === 'entregado')  return 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200'
  if (e === 'en_camino')  return 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200'
  if (e === 'preparando') return 'bg-orange-100 text-orange-700 ring-1 ring-orange-200'
  if (e === 'listo')      return 'bg-sky-100 text-sky-700 ring-1 ring-sky-200'
  return 'bg-amber-100 text-amber-700 ring-1 ring-amber-200'
}

const asignadoPill = has =>
  has
    ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200'
    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'

const human = s => (s || '').replace(/_/g, ' ')

const fmtDate = val => {
  if (!val) return '—'
  if (typeof val === 'string' && /\d{2}\/\d{2}\/\d{4}/.test(val)) return val
  try {
    const d = new Date(val)
    return new Intl.DateTimeFormat('es-MX', {
      dateStyle: 'short',
      timeStyle: 'short',
    }).format(d)
  } catch {
    return String(val)
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- ===== HEADER GRADIENTE ===== -->
    <template #header>
      <div class="pt-4 pb-6">
        <div
          class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
        >
          <!-- blobs decorativos -->
          <div class="pointer-events-none absolute -left-20 -top-24 h-40 w-40 rounded-full bg-white/20 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-44 rounded-full bg-black/20 blur-3xl" />

          <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <!-- IZQUIERDA -->
            <div class="space-y-3">
              <!-- Chip superior -->
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
              >
                <span class="text-lg">📦</span>
                <span>
                  {{
                    base === 'vendedor'
                      ? 'Pedidos en mostrador — Pollería Pepe'
                      : 'Gestión de pedidos — Pollería Pepe'
                  }}
                </span>
              </div>

              <!-- Título -->
              <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">
                  {{ base === 'vendedor' ? 'Pedidos presenciales y a domicilio' : 'Control total de pedidos' }}
                </h2>

                <p class="mt-1 text-sm text-amber-50/90">
                  {{
                    base === 'vendedor'
                      ? 'Visualiza rápido todos los tickets generados y su estado actual.'
                      : 'Administra estados, repartidores, importes y tiempos de entrega en un solo lugar.'
                  }}
                </p>
              </div>

              <!-- Chips resumen -->
              <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-amber-50/95">
                <span
                  class="inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 font-semibold text-amber-900 shadow-sm"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                  Control actualizado en tiempo real
                </span>

                <span class="inline-flex items-center gap-1 rounded-full bg-black/15 px-2.5 py-1">
                  Estado · repartidor · tipo · fecha · total
                </span>
              </div>
            </div>

            <!-- DERECHA -->
            <div class="flex flex-col items-end gap-3">
              <!-- Botón nuevo pedido (solo vendedor) -->
              <Link
                v-if="base === 'vendedor'"
                :href="route('vendedor.pedidos.create')"
                class="inline-flex items-center gap-2 rounded-2xl bg-white/95 px-4 py-2 text-[11px] font-semibold text-amber-800 shadow-sm hover:bg-white"
              >
                <span class="grid h-6 w-6 place-items-center rounded-full bg-amber-200">＋</span>
                Nuevo pedido
              </Link>

              <!-- Volver -->
              <Link
                :href="route(base + '.dashboard')"
                class="inline-flex items-center gap-1 rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-amber-50 hover:bg-black/30"
              >
                ← Volver al panel
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
      <!-- FILTROS -->
      <div class="rounded-3xl bg-white/90 backdrop-blur-sm px-4 py-4 shadow-sm ring-1 ring-amber-100/80">
        <div class="mb-3 flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
              Filtros de búsqueda
            </p>
            <p class="text-[11px] text-slate-500">
              Combina texto, estado, asignación y fechas para encontrar un pedido específico.
            </p>
          </div>
          <div
            class="hidden sm:inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200"
          >
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
            Filtros en tiempo real
          </div>
        </div>

        <div class="flex flex-wrap items-end gap-3">
          <!-- Texto -->
          <div class="relative w-full md:w-80">
            <input
              v-model="q"
              type="text"
              placeholder="Buscar por folio u observaciones…"
              class="w-full rounded-2xl border border-amber-100 bg-amber-50/40 px-4 py-2.5 pr-10 text-sm text-slate-900 shadow-sm outline-none ring-1 ring-amber-100 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/80"
            />
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-amber-400">
              🔍
            </span>
          </div>

          <!-- Estado -->
          <select
            v-model="estado"
            class="rounded-2xl border border-amber-100 bg-white px-3 py-2.5 text-sm shadow-sm outline-none ring-1 ring-amber-100 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/80"
          >
            <option value="">Todos los estados</option>
            <option v-for="e in estados" :key="e" :value="e">
              {{ human(e) }}
            </option>
          </select>

          <!-- Asignado -->
          <select
            v-model="asignado"
            class="rounded-2xl border border-amber-100 bg-white px-3 py-2.5 text-sm shadow-sm outline-none ring-1 ring-amber-100 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/80"
          >
            <option value="">Asignados: todos</option>
            <option value="any">Solo asignados</option>
            <option value="none">Solo sin asignar</option>
          </select>

          <!-- Fecha desde -->
          <div class="w-40">
            <label class="mb-1 block text-xs font-semibold text-amber-800">Desde</label>
            <input
              type="date"
              v-model="desde"
              class="w-full rounded-2xl border border-amber-100 bg-white px-3 py-2.5 text-sm shadow-sm outline-none ring-1 ring-amber-100 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/80"
            />
          </div>

          <!-- Fecha hasta -->
          <div class="w-40">
            <label class="mb-1 block text-xs font-semibold text-amber-800">Hasta</label>
            <input
              type="date"
              v-model="hasta"
              class="w-full rounded-2xl border border-amber-100 bg-white px-3 py-2.5 text-sm shadow-sm outline-none ring-1 ring-amber-100 focus:border-amber-300 focus:ring-2 focus:ring-amber-300/80"
            />
          </div>
        </div>
      </div>

      <!-- ===== LISTA DE PEDIDOS ===== -->
      <div class="space-y-3">
        <!-- Encabezado tipo tabla (desktop) -->
        <div
          class="hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-100 md:grid md:grid-cols-[auto,1.3fr,0.9fr,0.9fr,0.7fr,0.9fr,0.9fr,auto]"
        >
          <span>#</span>
          <span>Folio / resumen</span>
          <span class="text-center">Estado</span>
          <span class="text-center">Tipo</span>
          <span class="text-center">Items</span>
          <span class="text-center">Asignado a</span>
          <span class="text-right">Total</span>
          <span class="text-right">Acciones</span>
        </div>

        <!-- Fila / tarjeta -->
        <div
          v-for="(p, i) in pedidos.data"
          :key="p.id"
          class="group relative rounded-3xl border bg-white/95 px-4 py-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
          :class="[
            p.estado === 'entregado'
              ? 'border-emerald-200/80 hover:border-emerald-300 bg-emerald-50/60'
              : '',
            p.estado === 'cancelado'
              ? 'border-rose-200/80 hover:border-rose-300 bg-rose-50/60'
              : '',
            p.estado !== 'entregado' && p.estado !== 'cancelado'
              ? 'border-amber-100/80 hover:border-amber-200 bg-amber-50/40'
              : '',
          ]"
        >
          <!-- tira de color izquierda -->
          <div
            class="pointer-events-none absolute inset-y-3 left-1 w-1 rounded-full"
            :class="{
              'bg-emerald-400': p.estado === 'entregado',
              'bg-rose-400': p.estado === 'cancelado',
              'bg-amber-400': p.estado !== 'entregado' && p.estado !== 'cancelado',
            }"
          />

          <div
            class="grid grid-cols-1 gap-y-3 text-sm md:grid-cols-[auto,1.3fr,0.9fr,0.9fr,0.7fr,0.9fr,0.9fr,auto] md:items-center md:gap-y-2 md:gap-x-3"
          >
            <!-- # -->
            <div class="flex items-center gap-2 pl-2 md:pl-3">
              <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                #{{ (pedidos.from ?? 1) + i }}
              </span>
            </div>

            <!-- Folio + info -->
            <div>
              <p class="text-[13px] font-semibold text-slate-900">
                {{ p.folio ?? 'SIN FOLIO' }}
              </p>
              <p class="mt-0.5 text-[11px] text-slate-500">
                {{ human(p.tipo) }} · {{ p.items }} items
                <span class="mx-1">•</span>
                {{ fmtDate(p.created_at) }}
              </p>
            </div>

            <!-- Estado -->
            <div class="md:text-center">
              <span
                class="inline-flex items-center justify-center rounded-full px-2.5 py-1 text-[11px] font-semibold capitalize"
                :class="estadoPill(p.estado)"
              >
                {{ human(p.estado) }}
              </span>
            </div>

            <!-- Tipo -->
            <div class="md:text-center">
              <span
                class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-medium text-indigo-700"
              >
                <span>{{ p.tipo === 'domicilio' ? '🏍️' : '🧾' }}</span>
                {{ human(p.tipo) }}
              </span>
            </div>

            <!-- Items -->
            <div class="md:text-center">
              <span class="text-sm font-semibold text-slate-900">
                {{ p.items }}
              </span>
            </div>

            <!-- Asignado -->
            <div class="md:text-center">
              <span
                class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold"
                :class="asignadoPill(!!p.repartidor)"
              >
                <span v-if="p.repartidor" class="mr-1 text-[10px]">👤</span>
                {{ p.repartidor ?? 'Sin asignar' }}
              </span>
            </div>

            <!-- Total -->
            <div class="flex items-center md:justify-end md:text-right">
              <span
                class="text-sm font-extrabold"
                :class="p.estado === 'cancelado'
                  ? 'text-rose-700 line-through decoration-rose-400/70'
                  : 'text-emerald-700'"
              >
                {{ money(p.total) }}
              </span>
            </div>

            <!-- Acciones -->
            <div class="flex items-center justify-end md:text-right">
              <Link
                :href="route(base + '.pedidos.show', p.id)"
                class="inline-flex items-center gap-1 rounded-full bg-amber-500/90 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm shadow-amber-300/80 transition group-hover:bg-amber-600"
              >
                <span>Ver detalle</span>
                <span>→</span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Sin resultados -->
        <div
          v-if="(pedidos.data?.length || 0) === 0"
          class="rounded-3xl border border-dashed border-amber-200 bg-amber-50/60 px-4 py-10 text-center text-sm text-slate-500"
        >
          No hay pedidos con esos filtros.
        </div>
      </div>

      <!-- PAGINACIÓN -->
      <div class="mt-6 flex justify-end gap-2">
        <Link
          v-for="(lnk, i) in pedidos.links"
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
  </AuthenticatedLayout>
</template>
