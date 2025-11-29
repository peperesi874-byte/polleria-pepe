<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

/* ===================== DATA BASE ===================== */

const page = usePage()

// Todos los pedidos que vienen desde el controlador
const pedidos = computed(() => page.props.pedidos ?? [])

// Nombre del repartidor autenticado
const repartidorName = computed(
  () => page.props?.auth?.user?.name ?? 'Repartidor'
)

/* ===================== FORMATOS ===================== */

const fmtMoney = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
    .format(Number(n ?? 0))

const fmtDateTime = value => {
  if (!value) return ''
  const d = new Date(value)
  return d.toLocaleString('es-MX')
}

// Chip de color según estado
const estadoBadgeClass = estado => {
  switch (estado) {
    case 'pendiente':
    case 'confirmado':
    case 'en_preparacion':
    case 'listo':
      return 'bg-amber-100 text-amber-800'
    case 'en_camino':
    case 'en_reparto':
      return 'bg-sky-100 text-sky-800'
    case 'entregado':
      return 'bg-emerald-100 text-emerald-800'
    case 'cancelado':
      return 'bg-rose-100 text-rose-700'
    default:
      return 'bg-neutral-100 text-neutral-700'
  }
}

// Etiqueta amigable para el estado
const estadoLabel = estado => {
  switch (estado) {
    case 'pendiente':      return 'Pendiente'
    case 'confirmado':     return 'Confirmado'
    case 'en_preparacion': return 'En preparación'
    case 'listo':          return 'Listo para entregar'
    case 'en_reparto':
    case 'en_camino':      return 'En camino'
    case 'entregado':      return 'Entregado'
    case 'cancelado':      return 'Cancelado'
    default:               return estado
  }
}

/* ===================== AGRUPACIONES ===================== */

const porSalir = computed(() =>
  pedidos.value.filter(p =>
    ['pendiente', 'confirmado', 'en_preparacion', 'listo'].includes(p.estado)
  )
)

const enCamino = computed(() =>
  pedidos.value.filter(p =>
    ['en_camino', 'en_reparto'].includes(p.estado)
  )
)

const historial = computed(() =>
  pedidos.value.filter(p =>
    ['entregado', 'cancelado'].includes(p.estado)
  )
)

/* ===================== ACCIONES ===================== */

const cambiarEstado = (pedido, estado) => {
  const label = estado === 'en_camino'
    ? 'iniciar el reparto'
    : 'marcar como entregado'

  if (!confirm(`¿Seguro que quieres ${label} del pedido #${pedido.folio ?? pedido.id}?`)) {
    return
  }

  router.post(
    route('repartidor.pedidos.cambiar-estado', pedido.id),
    { estado },
    { preserveScroll: true }
  )
}

/* ===================== SCROLL A SECCIONES ===================== */

const scrollToSection = id => {
  const el = document.getElementById(id)
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- ================= HEADER ================= -->
    <template #header>
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-sky-50 via-indigo-50 to-emerald-50 px-6 py-5 ring-1 ring-sky-100 shadow-sm"
      >
        <!-- blobs decorativos -->
        <div class="pointer-events-none absolute -right-16 -top-10 h-32 w-32 rounded-full bg-sky-200/30 blur-2xl" />
        <div class="pointer-events-none absolute -left-10 bottom-0 h-28 w-28 rounded-full bg-emerald-200/40 blur-2xl" />

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div class="flex items-start gap-3">
            <div
              class="grid h-11 w-11 place-items-center rounded-2xl bg-sky-600 text-white shadow-md shadow-sky-200 text-xl"
            >
              📋
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700/80">
                Pedidos asignados
              </p>
              <h2 class="mt-1 text-2xl md:text-3xl font-bold text-neutral-900 leading-tight">
                Entregas para {{ repartidorName }}
              </h2>
              <p class="mt-1 text-sm text-neutral-600 max-w-xl">
                Revisa tus pedidos por salir, en camino y el historial reciente de entregas.
              </p>
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <div class="rounded-xl bg-white/80 px-3 py-2 text-right shadow-sm border border-sky-50">
              <p class="text-[11px] uppercase tracking-[0.16em] text-neutral-400">
                Hoy
              </p>
              <p class="text-sm font-medium text-neutral-800">
                {{
                  new Date().toLocaleDateString('es-MX', {
                    weekday: 'long',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                  })
                }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- =============== CONTENIDO PRINCIPAL =============== -->
    <div
      class="max-w-6xl mx-auto p-6 space-y-8 bg-gradient-to-b from-slate-50 via-white to-sky-50/40 rounded-3xl"
    >
      <!-- ================== KPIs ================== -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Por salir -->
        <button
          type="button"
          @click="scrollToSection('sec-por-salir')"
          class="group relative overflow-hidden rounded-2xl border border-amber-100 bg-white/95 shadow-sm p-4 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-xl transition-all cursor-pointer text-left"
        >
          <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400" />
          <div class="flex items-center justify-between mt-1">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                ⏱️
              </span>
              <p class="text-[11px] font-semibold text-amber-700 uppercase tracking-[0.18em]">
                Por salir
              </p>
            </div>
            <span class="text-[11px] text-amber-500/80 group-hover:text-amber-600">
              Ver sección →
            </span>
          </div>
          <p class="text-3xl font-extrabold text-amber-700">
            {{ porSalir.length }}
          </p>
          <p class="text-xs text-neutral-500">
            Pedidos pendientes o listos que aún no han salido a reparto.
          </p>
        </button>

        <!-- En camino -->
        <button
          type="button"
          @click="scrollToSection('sec-en-camino')"
          class="group relative overflow-hidden rounded-2xl border border-sky-100 bg-white/95 shadow-sm p-4 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-xl transition-all cursor-pointer text-left"
        >
          <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-400 via-sky-500 to-indigo-500" />
          <div class="flex items-center justify-between mt-1">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                🛵
              </span>
              <p class="text-[11px] font-semibold text-sky-700 uppercase tracking-[0.18em]">
                En camino
              </p>
            </div>
            <span class="text-[11px] text-sky-500/80 group-hover:text-sky-600">
              Ver sección →
            </span>
          </div>
          <p class="text-3xl font-extrabold text-sky-700">
            {{ enCamino.length }}
          </p>
          <p class="text-xs text-neutral-500">
            Entregas actualmente en ruta.
          </p>
        </button>

        <!-- Historial (entregados/cancelados) -->
        <button
          type="button"
          @click="scrollToSection('sec-historial')"
          class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-white/95 shadow-sm p-4 flex flex-col gap-2 hover:-translate-y-1 hover:shadow-xl transition-all cursor-pointer text-left"
        >
          <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-500" />
          <div class="flex items-center justify-between mt-1">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                ✅
              </span>
              <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-[0.18em]">
                Historial reciente
              </p>
            </div>
            <span class="text-[11px] text-emerald-500/80 group-hover:text-emerald-600">
              Ver sección →
            </span>
          </div>
          <p class="text-3xl font-extrabold text-emerald-700">
            {{ historial.filter(p => p.estado === 'entregado').length }}
          </p>
          <p class="text-xs text-neutral-500">
            Entregas marcadas como entregadas en tus últimas rutas.
          </p>
        </button>
      </section>

      <!-- Si no tiene pedidos -->
      <section
        v-if="!pedidos.length"
        class="relative overflow-hidden border border-dashed border-sky-200 rounded-3xl p-10 text-center bg-gradient-to-r from-sky-50 via-indigo-50/60 to-emerald-50/70 shadow-sm"
      >
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/80 shadow-inner text-2xl">
          🕊️
        </div>
        <p class="text-base font-semibold text-neutral-800">
          No tienes entregas asignadas por ahora.
        </p>
        <p class="mt-1 text-sm text-neutral-600 max-w-md mx-auto">
          Cuando el administrador o vendedor te asignen pedidos, aparecerán aquí para que organices tu ruta.
        </p>
        <p class="mt-3 text-[11px] text-sky-700/80 flex items-center justify-center gap-1">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
          Consejo: agrupa pedidos por colonia y horario para optimizar tus recorridos.
        </p>
      </section>

      <!-- Si hay pedidos -->
      <section
        v-else
        class="space-y-8"
      >
        <!-- ============== POR SALIR ============== -->
        <div id="sec-por-salir">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                📍
              </span>
              <div>
                <h2 class="text-xs font-semibold text-neutral-800 uppercase tracking-[0.22em]">
                  Pedidos por salir
                </h2>
                <p class="text-[11px] text-neutral-400">
                  Organiza primero los más lejanos o los de mayor total.
                </p>
              </div>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700"
              >
                {{ porSalir.length }} pedidos
              </span>
            </div>
          </div>

          <div
            v-if="!porSalir.length"
            class="rounded-2xl border border-dashed border-amber-200 bg-amber-50/40 p-4 text-xs text-amber-800/80 flex items-center gap-2"
          >
            <span class="text-lg">🗺️</span>
            <span>No hay pedidos pendientes ni listos para reparto.</span>
          </div>

          <div
            v-else
            class="space-y-4"
          >
            <div
              v-for="pedido in porSalir"
              :key="'ps-' + pedido.id"
              class="border border-slate-100 rounded-2xl p-4 bg-white/95 shadow-sm flex flex-col gap-3 hover:shadow-md hover:-translate-y-[2px] transition-all"
            >
              <!-- Encabezado -->
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                  <p class="text-xs text-neutral-500 uppercase tracking-wide">
                    Pedido
                  </p>
                  <p class="text-base font-semibold text-neutral-900">
                    #{{ pedido.folio ?? pedido.id }}
                  </p>
                  <p class="text-xs text-neutral-400">
                    Creado: {{ fmtDateTime(pedido.created_at) }}
                  </p>
                </div>

                <div class="flex items-center gap-4 justify-between md:justify-end">
                  <div class="flex flex-col items-start md:items-end gap-1">
                    <span class="text-xs text-neutral-500">Estado</span>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                      :class="estadoBadgeClass(pedido.estado)"
                    >
                      {{ estadoLabel(pedido.estado) }}
                    </span>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-neutral-400 uppercase tracking-wide">
                      Total
                    </p>
                    <p class="text-lg font-semibold text-neutral-900">
                      {{ fmtMoney(pedido.total) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Cliente / dirección -->
              <div class="grid md:grid-cols-2 gap-3 text-sm border-t pt-3">
                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Cliente / contacto
                  </p>
                  <p class="font-medium text-neutral-900">
                    {{ pedido.entrega_nombre || 'Nombre no especificado' }}
                  </p>
                  <p class="text-neutral-600">
                    Tel: {{ pedido.entrega_telefono || 'Sin teléfono' }}
                  </p>
                </div>

                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Dirección de entrega
                  </p>
                  <p class="text-neutral-700">
                    {{
                      [
                        pedido.entrega_calle,
                        pedido.entrega_numero,
                        pedido.entrega_colonia,
                        pedido.entrega_municipio,
                      ]
                        .filter(Boolean)
                        .join(', ') || 'Sin dirección registrada'
                    }}
                  </p>
                  <p
                    v-if="pedido.entrega_referencias"
                    class="text-neutral-500 text-xs"
                  >
                    Referencias: {{ pedido.entrega_referencias }}
                  </p>
                </div>
              </div>

              <!-- Acciones -->
              <div class="border-t pt-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <p class="text-xs text-neutral-500 max-w-sm">
                  Cuando salgas a ruta, inicia el reparto para que quede registrado en el sistema.
                </p>
                <div class="flex flex-wrap gap-2 justify-end">
                  <button
                    type="button"
                    class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full border border-sky-500 text-sky-600 hover:bg-sky-50 transition"
                    @click="cambiarEstado(pedido, 'en_camino')"
                  >
                    🚚 Iniciar reparto
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============== EN CAMINO ============== -->
        <div id="sec-en-camino">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                🛣️
              </span>
              <div>
                <h2 class="text-xs font-semibold text-neutral-800 uppercase tracking-[0.22em]">
                  Pedidos en camino
                </h2>
                <p class="text-[11px] text-neutral-400">
                  Marca como entregado tan pronto como cierres la entrega.
                </p>
              </div>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700"
              >
                {{ enCamino.length }} en ruta
              </span>
            </div>
          </div>

          <div
            v-if="!enCamino.length"
            class="rounded-2xl border border-dashed border-sky-200 bg-sky-50/40 p-4 text-xs text-sky-800/80 flex items-center gap-2"
          >
            <span class="text-lg">📭</span>
            <span>No tienes entregas en camino en este momento.</span>
          </div>

          <div
            v-else
            class="space-y-4"
          >
            <div
              v-for="pedido in enCamino"
              :key="'ec-' + pedido.id"
              class="border border-slate-100 rounded-2xl p-4 bg-white/95 shadow-sm flex flex-col gap-3 hover:shadow-md hover:-translate-y-[2px] transition-all"
            >
              <!-- Encabezado -->
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                  <p class="text-xs text-neutral-500 uppercase tracking-wide">
                    Pedido en ruta
                  </p>
                  <p class="text-base font-semibold text-neutral-900">
                    #{{ pedido.folio ?? pedido.id }}
                  </p>
                  <p class="text-xs text-neutral-400">
                    Iniciado: {{ fmtDateTime(pedido.created_at) }}
                  </p>
                </div>

                <div class="flex items-center gap-4 justify-between md:justify-end">
                  <div class="flex flex-col items-start md:items-end gap-1">
                    <span class="text-xs text-neutral-500">Estado</span>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                      :class="estadoBadgeClass(pedido.estado)"
                    >
                      {{ estadoLabel(pedido.estado) }}
                    </span>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-neutral-400 uppercase tracking-wide">
                      Total
                    </p>
                    <p class="text-lg font-semibold text-neutral-900">
                      {{ fmtMoney(pedido.total) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Cliente / dirección -->
              <div class="grid md:grid-cols-2 gap-3 text-sm border-t pt-3">
                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Cliente / contacto
                  </p>
                  <p class="font-medium text-neutral-900">
                    {{ pedido.entrega_nombre || 'Nombre no especificado' }}
                  </p>
                  <p class="text-neutral-600">
                    Tel: {{ pedido.entrega_telefono || 'Sin teléfono' }}
                  </p>
                </div>

                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Dirección de entrega
                  </p>
                  <p class="text-neutral-700">
                    {{
                      [
                        pedido.entrega_calle,
                        pedido.entrega_numero,
                        pedido.entrega_colonia,
                        pedido.entrega_municipio,
                      ]
                        .filter(Boolean)
                        .join(', ') || 'Sin dirección registrada'
                    }}
                  </p>
                  <p
                    v-if="pedido.entrega_referencias"
                    class="text-neutral-500 text-xs"
                  >
                    Referencias: {{ pedido.entrega_referencias }}
                  </p>
                </div>
              </div>

              <!-- Acciones -->
              <div class="border-t pt-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <p class="text-xs text-neutral-500 max-w-sm">
                  Al entregar el pedido al cliente, márcalo como entregado para cerrar la ruta.
                </p>
                <div class="flex flex-wrap gap-2 justify-end">
                  <button
                    type="button"
                    class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full border border-emerald-500 text-emerald-600 hover:bg-emerald-50 transition"
                    @click="cambiarEstado(pedido, 'entregado')"
                  >
                    ✅ Marcar como entregado
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============== HISTORIAL ============== -->
        <div id="sec-historial">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-7 w-7 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                📜
              </span>
              <div>
                <h2 class="text-xs font-semibold text-neutral-800 uppercase tracking-[0.22em]">
                  Historial reciente
                </h2>
                <p class="text-[11px] text-neutral-400">
                  Entregas y cancelaciones registradas en tus rutas recientes.
                </p>
              </div>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-50 text-neutral-700"
              >
                {{ historial.length }} registros
              </span>
            </div>
          </div>

          <div
            v-if="!historial.length"
            class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-4 text-xs text-neutral-500 flex items-center gap-2"
          >
            <span class="text-lg">🕒</span>
            <span>Aún no hay historial de entregas o cancelaciones recientes.</span>
          </div>

          <div
            v-else
            class="rounded-3xl border border-slate-100 bg-white/95 shadow-sm divide-y"
          >
            <div
              v-for="pedido in historial"
              :key="'hist-' + pedido.id"
              class="px-4 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/70 transition"
            >
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-neutral-900 truncate">
                  Pedido #{{ pedido.folio ?? pedido.id }}
                </p>
                <p class="text-xs text-neutral-500">
                  {{ pedido.entrega_nombre || 'Cliente sin nombre' }} ·
                  {{ fmtMoney(pedido.total) }}
                </p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="estadoBadgeClass(pedido.estado)"
                >
                  {{ estadoLabel(pedido.estado) }}
                </span>
                <span class="text-[11px] text-neutral-400">
                  {{ fmtDateTime(pedido.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
