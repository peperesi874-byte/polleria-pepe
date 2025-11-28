<!-- resources/js/Pages/Admin/Dashboard.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const page = usePage()

const props = defineProps({
  cards: { type: Object, default: () => ({ productos: 0, pedidos: 0, usuarios: 0, inventario_bajo: 0 }) },
  ultimos: { type: Array, default: () => [] },      // [{id, folio, estado, tipo, items, total, created_at}]
  alertas: { type: Array, default: () => [] },      // [{id,nombre,stock_actual,stock_minimo}]
  bitacora: { type: Array, default: () => [] },     // si el controlador la manda
  notificaciones: { type: Array, default: () => [] } // si el controlador las manda
})

/** 👤 Nombre y saludo del administrador */
const userName = computed(() => page.props?.auth?.user?.name ?? 'Usuario')

const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

/** Helpers */
const money = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

const estadoPill = (e) => {
  const map = {
    pendiente:  'bg-amber-100 text-amber-800 ring-amber-200',
    preparando: 'bg-orange-100 text-orange-800 ring-orange-200',
    listo:      'bg-sky-100 text-sky-800 ring-sky-200',
    en_camino:  'bg-indigo-100 text-indigo-800 ring-indigo-200',
    entregado:  'bg-emerald-100 text-emerald-700 ring-emerald-200',
    cancelado:  'bg-rose-100 text-rose-700 ring-rose-200',
  }
  return `inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 ${map[e] ?? 'bg-slate-100 text-slate-700 ring-slate-200'}`
}

const fmtDate = (val) => {
  if (!val) return '—'
  if (typeof val === 'string' && /\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}/.test(val)) return val
  try {
    const d = new Date(val)
    return new Intl.DateTimeFormat('es-MX', { dateStyle: 'short', timeStyle: 'short' }).format(d)
  } catch { return String(val) }
}

const tieneAlertas = computed(() => (props.alertas?.length ?? 0) > 0)

/** 📊 cositas de resumen */
const pedidosHoy = computed(() => props.ultimos?.length ?? 0)
const alertasCount = computed(() => props.alertas?.length ?? 0)

const actividadNivel = computed(() => {
  const n = pedidosHoy.value
  if (n >= 8) return 'Día muy movido'
  if (n >= 4) return 'Buen ritmo de pedidos'
  if (n > 0) return 'Pocos pedidos hoy'
  return 'Sin pedidos registrados hoy'
})

/** 📜 Bitácora en dashboard: props -> fallback a page.props.bitacoraTop */
const bitacoraList = computed(() => (props.bitacora?.length ?? 0) > 0
  ? props.bitacora
  : (page.props?.bitacoraTop ?? []))

/** 🔔 Notificaciones en dashboard: props -> fallback a page.props.notifTop */
const notifsList = computed(() => (props.notificaciones?.length ?? 0) > 0
  ? props.notificaciones
  : (page.props?.notifTop ?? []))

/** UI: dropdowns */
const showBitacora = ref(false)
const showNotif = ref(false)
const toggleBitacora = () => { showBitacora.value = !showBitacora.value; if (showBitacora.value) showNotif.value = false }
const toggleNotif = () => { showNotif.value = !showNotif.value; if (showNotif.value) showBitacora.value = false }
const closeMenus = () => { showBitacora.value = false; showNotif.value = false }
</script>

<template>
  <Head title="Panel" />

  <AuthenticatedLayout>
    <!-- 🧡 HERO NARANJA: LO DEJAMOS TAL CUAL -->
     <template #header>
  <div class="bg-gradient-to-b from-[#FFF7E3] via-[#FFFDF8] to-[#FFE7E0] pt-4 pb-6">
    <div class="relative mx-auto max-w-7xl rounded-3xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 px-6 py-6 shadow-lg">
      <!-- decor blobs -->
          <div class="pointer-events-none absolute -left-16 -top-20 h-40 w-40 rounded-full bg-white/10 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-40 rounded-full bg-black/10 blur-3xl" />

          <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <!-- Saludo + contexto -->
            <div class="space-y-2">
              <div class="inline-flex items-center gap-2 rounded-full bg-black/15 px-3 py-1 text-xs font-medium text-amber-50 backdrop-blur">
                <span class="text-lg">🐔</span>
                <span>Panel general — Pollería Pepe</span>
              </div>

              <div>
                <p class="text-sm text-amber-50/90">
                  {{ saludo }},
                  <span class="font-semibold">{{ userName }}</span>
                </p>
                <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">
                  Controla tus pedidos e inventario en un solo lugar
                </h1>
                <p class="mt-2 max-w-xl text-sm text-amber-50/90">
                  Revisa el flujo de pedidos, el estado del inventario y la actividad reciente
                  del equipo. Todo lo que necesitas para no perder ninguna venta.
                </p>
              </div>

              <!-- mini resumen -->
              <div class="mt-3 flex flex-wrap gap-3 text-xs text-amber-100/90">
                <div class="flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-emerald-300" />
                  <span>{{ cards.pedidos }} pedidos totales</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-yellow-300" />
                  <span>{{ cards.productos }} productos activos</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-sky-300" />
                  <span>{{ cards.usuarios }} usuarios en el sistema</span>
                </div>
              </div>
            </div>

            <!-- Acciones rápidas del header (Notificaciones + Bitácora) -->
            <div class="flex flex-col items-stretch gap-3 lg:w-80">
              <button
                type="button"
                @click.stop="toggleNotif"
                class="flex items-center justify-between rounded-2xl bg-white/95 px-4 py-3 text-sm font-medium text-slate-900 shadow-md shadow-black/10 ring-1 ring-white/60 backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lg"
                :aria-expanded="showNotif ? 'true' : 'false'"
              >
                <div class="flex items-center gap-3">
                  <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 text-lg">
                    🔔
                  </span>
                  <div class="text-left">
                    <p>Notificaciones</p>
                    <p class="text-xs text-slate-500">
                      {{ (notifsList?.length ?? 0) > 0 ? 'Revisa lo más reciente' : 'Sin notificaciones importantes' }}
                    </p>
                  </div>
                </div>
                <span
                  v-if="(notifsList?.length ?? 0) > 0"
                  class="rounded-full bg-amber-500 px-2 py-0.5 text-[11px] font-semibold text-white"
                >
                  {{ notifsList.length }}
                </span>
              </button>

              <button
                type="button"
                @click.stop="toggleBitacora"
                class="flex items-center justify-between rounded-2xl bg-black/15 px-4 py-3 text-sm font-medium text-amber-50 shadow-inner shadow-black/20 ring-1 ring-white/20 backdrop-blur transition hover:bg-black/25"
                :aria-expanded="showBitacora ? 'true' : 'false'"
              >
                <div class="flex items-center gap-3">
                  <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-amber-200/20 text-lg">
                    📜
                  </span>
                  <div class="text-left">
                    <p>Bitácora reciente</p>
                    <p class="text-xs text-amber-100/90">
                      Cambios y movimientos del sistema
                    </p>
                  </div>
                </div>
                <span
                  v-if="(bitacoraList?.length ?? 0) > 0"
                  class="rounded-full bg-emerald-400/90 px-2 py-0.5 text-[11px] font-semibold text-emerald-950"
                >
                  {{ bitacoraList.length }}
                </span>
              </button>
            </div>
          </div>

          <!-- PANEL flotante de notificaciones -->
          <div
            v-if="showNotif"
            class="absolute right-6 top-full z-20 mt-4 w-full max-w-md rounded-2xl border border-amber-100 bg-white/95 p-4 shadow-2xl shadow-amber-200/60 backdrop-blur"
          >
            <div class="mb-2 flex items-center justify-between">
              <h4 class="font-semibold text-slate-900">Notificaciones</h4>
              <button class="text-xs text-slate-400 hover:text-slate-600" @click="closeMenus">Cerrar</button>
            </div>

            <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
              <div
                v-for="(n, i) in notifsList"
                :key="n.id ?? i"
                class="flex items-start gap-3 rounded-xl bg-slate-50/80 px-3 py-2 text-sm"
              >
                <div class="mt-0.5 h-6 w-6 flex items-center justify-center rounded-full bg-amber-100 text-amber-700 text-xs">
                  !
                </div>
                <div class="flex-1">
                  <p class="font-medium text-slate-900">
                    {{ n.titulo ?? n.tipo ?? 'Notificación' }}
                  </p>
                  <p class="text-xs text-slate-500">
                    {{ n.mensaje ?? n.detalle ?? '' }}
                  </p>
                </div>
                <span class="whitespace-nowrap text-[10px] text-slate-400">
                  {{ fmtDate(n.fecha ?? n.when ?? n.created_at) }}
                </span>
              </div>
              <p v-if="(notifsList?.length ?? 0) === 0" class="py-2 text-center text-xs text-slate-400">
                No hay notificaciones recientes.
              </p>
            </div>

            <div class="mt-3 text-right">
              <Link :href="route('admin.notificaciones.index')" class="text-xs font-medium text-amber-600 hover:underline">
                Ver todas →
              </Link>
            </div>
          </div>

          <!-- PANEL flotante de bitácora (MEJORADO) -->
          <div
            v-if="showBitacora"
            class="absolute right-6 top-full z-20 mt-4 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-2xl shadow-slate-200/80 backdrop-blur"
          >
            <div class="mb-2 flex items-center justify-between">
              <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-xs">
                  📜
                </span>
                Bitácora reciente
              </h4>
              <button class="text-xs text-slate-400 hover:text-slate-600" @click="closeMenus">Cerrar</button>
            </div>

            <div class="max-h-[60vh] overflow-auto text-xs rounded-2xl border border-slate-100/80 bg-slate-50/60">
              <table class="w-full border-separate border-spacing-0">
                <thead>
                  <tr class="sticky top-0 z-10 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-[10px] uppercase tracking-wide text-slate-100">
                    <th class="px-3 py-2 text-left font-semibold first:rounded-tl-2xl">Fecha</th>
                    <th class="px-3 py-2 text-left font-semibold">Actor</th>
                    <th class="px-3 py-2 text-left font-semibold">Acción</th>
                    <th class="px-3 py-2 text-left font-semibold">Entidad</th>
                    <th class="px-3 py-2 text-left font-semibold last:rounded-tr-2xl">Detalle</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(b, i) in bitacoraList.slice(0, 25)"
                    :key="b.id ?? i"
                    class="group border-b border-slate-100/80 last:border-0 transition-all"
                    :class="i % 2 === 0 ? 'bg-white' : 'bg-slate-50/70'"
                  >
                    <td class="whitespace-nowrap px-3 py-2 text-slate-500 group-hover:bg-amber-50/60 group-hover:text-slate-700 border-l-4 border-transparent group-hover:border-amber-400/80">
                      {{ b.fecha ?? fmtDate(b.when ?? b.created_at) }}
                    </td>
                    <td class="px-3 py-2 text-slate-700 group-hover:bg-amber-50/60">
                      <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                        {{ b.actor ?? b.usuario ?? '—' }}
                      </span>
                    </td>
                    <td class="px-3 py-2 group-hover:bg-amber-50/60">
                      <span
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1"
                        :class="[
                          (b.accion ?? b.action ?? '').toLowerCase().includes('crear') ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : '',
                          (b.accion ?? b.action ?? '').toLowerCase().includes('actualizar') ? 'bg-sky-50 text-sky-700 ring-sky-200' : '',
                          (b.accion ?? b.action ?? '').toLowerCase().includes('eliminar') ? 'bg-rose-50 text-rose-700 ring-rose-200' : '',
                        ].filter(Boolean).join(' ') || 'bg-slate-50 text-slate-700 ring-slate-200'"
                      >
                        ⚙️
                        <span>{{ b.accion ?? b.action ?? '—' }}</span>
                      </span>
                    </td>
                    <td class="px-3 py-2 text-slate-700 group-hover:bg-amber-50/60">
                      {{ b.entidad ?? b.entity ?? '—' }}
                    </td>
                    <td class="px-3 py-2 text-slate-600 group-hover:bg-amber-50/60">
                      <span v-if="typeof (b.detalle ?? '') === 'string'">
                        {{ b.detalle ?? b.meta ?? '—' }}
                      </span>
                      <span v-else>
                        {{ JSON.stringify(b.detalle ?? b.meta ?? {}, null, 0) }}
                      </span>
                    </td>
                  </tr>

                  <tr v-if="(bitacoraList?.length ?? 0) === 0">
                    <td colspan="5" class="px-3 py-4 text-center text-slate-400 bg-white">
                      Sin registros de bitácora.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="mt-3 text-right">
              <Link :href="route('admin.bitacora.index')" class="text-xs font-medium text-amber-600 hover:underline">
                Ir a Bitácora →
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🔽 CONTENIDO PRINCIPAL: FONDO LIGERO CON DEGRADADO -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/15 to-slate-50">
      <div class="mx-auto max-w-7xl px-4 py-6 space-y-6 lg:px-6">
        <!-- Resumen del día + panel oscuro -->
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
          <!-- Resumen del día -->
           <div class="lg:col-span-2 rounded-2xl bg-gradient-to-br from-amber-50 via-amber-100/80 to-white p-5 shadow-md ring-1 ring-amber-200/80">
  <div class="flex items-center justify-between gap-3">
    <div>
      <!-- Etiqueta Resumen con más presencia -->
      <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200/80 shadow-sm">
        <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
        <span>Resumen del día</span>
      </div>

      <p class="mt-2 text-sm text-amber-950">
        {{ actividadNivel }}. Hoy se han registrado
        <span class="font-semibold">{{ pedidosHoy }}</span> pedidos.
      </p>
    </div>

    <!-- Chip “Panel en tiempo real” más vivo -->
    <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-slate-900/95 px-3 py-1 text-[11px] font-semibold text-emerald-300 ring-1 ring-slate-800 shadow-sm">
      <span class="relative flex h-2 w-2">
        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400/70 opacity-75" />
        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-300" />
      </span>
      Panel en tiempo real
    </span>
  </div>

  <!-- Chips de resumen, con un poquito más de presencia -->
  <div class="mt-4 flex flex-wrap gap-3 text-xs">
    <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 ring-1 ring-amber-200 shadow-sm">
      <span class="text-amber-700 text-sm">📦</span>
      <span class="text-amber-900">
        {{ cards.pedidos }} pedidos totales
      </span>
    </div>
    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 ring-1 ring-emerald-200 shadow-sm">
      <span class="text-emerald-700 text-sm">🧺</span>
      <span class="text-emerald-900">
        {{ cards.productos }} productos activos
      </span>
    </div>
    <div class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 ring-1 ring-sky-200 shadow-sm">
      <span class="text-sky-700 text-sm">👥</span>
      <span class="text-sky-900">
        {{ cards.usuarios }} usuarios internos
      </span>
    </div>
    <div class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 ring-1 ring-rose-200 shadow-sm">
      <span class="text-rose-700 text-sm">⚠️</span>
      <span class="text-rose-900">
        {{ alertasCount }} alertas de inventario
      </span>
    </div>
  </div>
</div>


          <!-- Panel oscuro “Hoy en Pollería Pepe” -->
          <div class="rounded-2xl bg-slate-900 p-5 text-slate-50 shadow-lg ring-1 ring-slate-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-300">
                  Hoy en Pollería Pepe
                </p>
                <p class="mt-1 text-sm text-slate-100">
                  Vista rápida del movimiento de pedidos.
                </p>
              </div>
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold text-emerald-300 ring-1 ring-emerald-500/40">
                <span class="h-2 w-2 rounded-full bg-emerald-300" />
                Online
              </span>
            </div>

            <!-- sparkline -->
            <svg viewBox="0 0 100 30" class="mt-4 h-16 w-full text-emerald-300">
              <polyline
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                points="0,22 12,20 24,21 36,16 48,18 60,12 72,14 84,9 96,11"
              />
            </svg>

            <div class="mt-3 grid grid-cols-3 gap-2 text-[11px]">
              <div>
                <p class="text-slate-400">Pedidos hoy</p>
                <p class="text-sm font-semibold text-slate-50">{{ pedidosHoy }}</p>
              </div>
              <div>
                <p class="text-slate-400">Activos en sistema</p>
                <p class="text-sm font-semibold text-slate-50">{{ cards.pedidos }}</p>
              </div>
              <div>
                <p class="text-slate-400">Alertas</p>
                <p
                  class="text-sm font-semibold"
                  :class="alertasCount > 0 ? 'text-rose-300' : 'text-emerald-300'"
                >
                  {{ alertasCount > 0 ? alertasCount + ' abiertas' : '0 activas' }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- GRID PRINCIPAL: KPIs + tabla + lateral -->
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- KPIs + tabla -->
          <div class="space-y-4 lg:col-span-2">
            <!-- KPIs -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <!-- Productos -->
              <Link
                :href="route('productos.index')"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50 via-amber-100/80 to-white p-4 shadow-md ring-1 ring-amber-200/80 transition hover:-translate-y-0.5 hover:shadow-lg hover:ring-amber-300"
              >
                <div class="absolute right-3 top-3 h-10 w-10 rounded-2xl bg-amber-500/90 text-white flex items-center justify-center text-lg shadow-md shadow-amber-300/60">
                  🛒
                </div>
                <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Productos</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-900">
                  {{ cards.productos }}
                </p>
                <p class="mt-1 text-xs text-amber-900/80">Activos en catálogo</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 group-hover:underline">
                  Ver catálogo →
                </span>
              </Link>

              <!-- Pedidos -->
              <Link
                :href="route('admin.pedidos.index')"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-50 via-rose-100/80 to-white p-4 shadow-md ring-1 ring-rose-200/80 transition hover:-translate-y-0.5 hover:shadow-lg hover:ring-rose-300"
              >
                <div class="absolute right-3 top-3 h-10 w-10 rounded-2xl bg-rose-500/90 text-white flex items-center justify-center text-lg shadow-md shadow-rose-300/60">
                  📦
                </div>
                <p class="text-xs font-medium uppercase tracking-wide text-rose-700">Pedidos</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-900">
                  {{ cards.pedidos }}
                </p>
                <p class="mt-1 text-xs text-rose-900/80">Totales en el sistema</p>
                <div class="mt-3 flex gap-1">
                  <span class="h-1.5 flex-1 rounded-full bg-rose-500/80" />
                  <span class="h-1.5 flex-1 rounded-full bg-rose-400/70" />
                  <span class="h-1.5 flex-1 rounded-full bg-rose-300/60" />
                </div>
              </Link>

              <!-- Usuarios -->
              <Link
                :href="route('admin.usuarios.index')"
                class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-50 via-sky-100/80 to-white p-4 shadow-md ring-1 ring-sky-200/80 transition hover:-translate-y-0.5 hover:shadow-lg hover:ring-sky-300"
              >
                <div class="absolute right-3 top-3 h-10 w-10 rounded-2xl bg-sky-500/90 text-white flex items-center justify-center text-lg shadow-md shadow-sky-300/60">
                  👥
                </div>
                <p class="text-xs font-medium uppercase tracking-wide text-sky-700">Usuarios</p>
                <p class="mt-1 text-3xl font-extrabold text-slate-900">
                  {{ cards.usuarios }}
                </p>
                <p class="mt-1 text-xs text-sky-900/80">Administradores y personal</p>
                <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-sky-700 group-hover:underline">
                  Gestionar usuarios →
                </span>
              </Link>

              <!-- Inventario -->
              <Link
                :href="route('admin.inventario.index')"
                class="group relative overflow-hidden rounded-2xl p-4 shadow-md ring-1 transition hover:-translate-y-0.5 hover:shadow-lg"
                :class="cards.inventario_bajo > 0
                  ? 'bg-gradient-to-br from-rose-50 via-rose-100/80 to-white ring-rose-200/80 hover:ring-rose-300'
                  : 'bg-gradient-to-br from-emerald-50 via-emerald-100/80 to-white ring-emerald-200/80 hover:ring-emerald-300'"
              >
                <div
                  class="absolute right-3 top-3 h-10 w-10 rounded-2xl flex items-center justify-center text-lg shadow-md"
                  :class="cards.inventario_bajo > 0 ? 'bg-rose-500/90 text-white shadow-rose-300/60' : 'bg-emerald-500/90 text-white shadow-emerald-300/60'"
                >
                  📊
                </div>
                <p
                  class="text-xs font-medium uppercase tracking-wide"
                  :class="cards.inventario_bajo > 0 ? 'text-rose-700' : 'text-emerald-700'"
                >
                  Inventario bajo mínimo
                </p>
                <p
                  class="mt-1 text-3xl font-extrabold"
                  :class="cards.inventario_bajo > 0 ? 'text-rose-800' : 'text-emerald-800'"
                >
                  {{ cards.inventario_bajo }}
                </p>
                <p class="mt-1 text-xs" :class="cards.inventario_bajo > 0 ? 'text-rose-900/80' : 'text-emerald-900/80'">
                  Productos por debajo del umbral
                </p>
                <span
                  class="mt-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1"
                  :class="cards.inventario_bajo > 0 ? 'bg-rose-50 text-rose-700 ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200'"
                >
                  {{ cards.inventario_bajo > 0 ? 'Atender hoy' : 'Todo en orden' }}
                </span>
              </Link>
            </div>

            <!-- Últimos pedidos (MEJORADO) -->
            <div class="overflow-hidden rounded-3xl bg-gradient-to-b from-white via-amber-50/30 to-white shadow-sm ring-1 ring-amber-100/70">
              <div class="flex items-center justify-between border-b border-amber-100/70 px-5 py-4 bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-rose-500/10">
                <div class="flex items-center gap-3">
                  <span class="flex h-8 w-8 items-center justify-center rounded-2xl bg-amber-500 text-sm text-white shadow-sm">
                    ✅
                  </span>
                  <div>
                    <h3 class="text-sm font-semibold text-slate-900">Últimos pedidos</h3>
                    <p class="text-xs text-slate-600">
                      Seguimiento de los pedidos más recientes
                    </p>
                  </div>
                </div>
                <Link
                  :href="route('admin.pedidos.index')"
                  class="inline-flex items-center gap-1 rounded-full bg-white/70 px-3 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200 hover:bg-amber-600 hover:text-white hover:ring-amber-500 transition"
                >
                  Ver todos
                  <span>→</span>
                </Link>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-0 text-xs">
                  <thead>
                    <tr class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-[11px] uppercase tracking-wide text-slate-100">
                      <th class="px-4 py-2 text-left font-semibold first:rounded-tl-3xl w-14">#</th>
                      <th class="px-4 py-2 text-left font-semibold">Folio</th>
                      <th class="px-4 py-2 text-left font-semibold">Estado</th>
                      <th class="px-4 py-2 text-left font-semibold">Tipo</th>
                      <th class="px-4 py-2 text-right font-semibold">Items</th>
                      <th class="px-4 py-2 text-right font-semibold">Total</th>
                      <th class="px-4 py-2 text-left font-semibold">Creado</th>
                      <th class="px-4 py-2 text-right font-semibold last:rounded-tr-3xl">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(p, idx) in ultimos"
                      :key="p.id"
                      class="group border-b border-slate-100/80 last:border-0 transition-all"
                      :class="idx % 2 === 0 ? 'bg-white' : 'bg-amber-50/40'"
                    >
                      <td class="px-4 py-2 font-medium text-slate-400 group-hover:bg-amber-50/80 group-hover:text-slate-600 border-l-4 border-transparent group-hover:border-amber-400/80">
                        #{{ p.id }}
                      </td>
                      <td class="px-4 py-2 text-[13px] font-semibold text-slate-900 group-hover:bg-amber-50/80">
                        {{ p.folio ?? '—' }}
                      </td>
                      <td class="px-4 py-2 group-hover:bg-amber-50/80">
                        <span :class="estadoPill(p.estado)">
                          <span class="h-1.5 w-1.5 rounded-full bg-current/70" />
                          <span class="capitalize">{{ p.estado }}</span>
                        </span>
                      </td>
                      <td class="px-4 py-2 group-hover:bg-amber-50/80">
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-0.5 text-[11px] font-medium text-slate-700 ring-1 ring-slate-200 capitalize">
                          🍗
                          {{ p.tipo }}
                        </span>
                      </td>
                      <td class="px-4 py-2 text-right text-slate-700 group-hover:bg-amber-50/80">
                        {{ p.items }}
                      </td>
                      <td class="px-4 py-2 text-right font-semibold text-emerald-700 group-hover:bg-amber-50/80">
                        {{ money(p.total) }}
                      </td>
                      <td class="px-4 py-2 text-slate-500 group-hover:bg-amber-50/80 whitespace-nowrap">
                        {{ fmtDate(p.created_at) }}
                      </td>
                      <td class="px-4 py-2 text-right group-hover:bg-amber-50/80">
                        <Link
                          :href="route('admin.pedidos.show', p.id)"
                          class="inline-flex items-center gap-1 rounded-full bg-amber-500/90 px-3 py-1 text-[11px] font-semibold text-white shadow-sm shadow-amber-300/70 hover:bg-amber-600 transition"
                        >
                          Ver
                          <span>→</span>
                        </Link>
                      </td>
                    </tr>

                    <tr v-if="ultimos.length === 0">
                      <td colspan="8" class="px-4 py-6 text-center text-slate-400 bg-white">
                        Aún no hay pedidos registrados.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- LATERAL DERECHO -->
          <div class="space-y-4">
            <!-- Alertas de inventario -->
            <div class="rounded-3xl bg-amber-50/60 backdrop-blur-sm p-5 shadow-lg shadow-amber-200/40 ring-1 ring-amber-100/80">
              <div class="mb-3 flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-semibold text-amber-950">Alertas de inventario</h3>
                  <p class="text-xs text-amber-900/80">
                    Productos por debajo del mínimo establecido
                  </p>
                </div>
                <Link :href="route('admin.inventario.index')" class="text-xs font-medium text-amber-900 hover:underline">
                  Ver inventario →
                </Link>
              </div>

              <ul v-if="tieneAlertas" class="space-y-3 text-xs">
                <li
                  v-for="a in alertas"
                  :key="a.id"
                  class="flex items-start justify-between rounded-2xl bg-white/70 px-3 py-2 shadow-[0_1px_0_rgba(0,0,0,0.03)]"
                >
                  <div>
                    <p class="text-[13px] font-semibold text-amber-950">
                      {{ a.nombre }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-amber-900/80">
                      Stock:
                      <span class="font-semibold">{{ a.stock_actual }}</span>
                      — Mín:
                      <span class="font-semibold">{{ a.stock_minimo }}</span>
                    </p>
                  </div>
                  <span class="mt-0.5 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700 ring-1 ring-rose-200">
                    Bajo
                  </span>
                </li>
              </ul>

              <p v-else class="rounded-2xl bg-white/60 px-3 py-3 text-xs text-amber-900/80 shadow-[0_1px_0_rgba(0,0,0,0.03)]">
                Sin alertas por ahora
                <span class="ml-1">✅</span>
              </p>
            </div>

            <!-- Accesos rápidos -->
            <div class="rounded-3xl bg-white/60 backdrop-blur-sm p-5 shadow-lg shadow-slate-300/40 ring-1 ring-white/60">
              <h3 class="text-sm font-semibold text-slate-900">Accesos rápidos</h3>
              <p class="mb-3 mt-1 text-xs text-slate-500">
                Atajos para las tareas más comunes del día a día.
              </p>

              <div class="grid grid-cols-1 gap-2 text-xs">
                <Link
                  :href="route('productos.create')"
                  class="flex items-center justify-between rounded-2xl bg-amber-50 px-3 py-2 ring-1 ring-amber-100 hover:bg-amber-100 transition"
                >
                  <span class="flex items-center gap-2 text-amber-900">
                    <span class="text-base">➕</span>
                    Crear producto
                  </span>
                  <span class="text-amber-500">→</span>
                </Link>

                <Link
                  :href="route('admin.reportes.index')"
                  class="flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-2 ring-1 ring-slate-100 hover:bg-slate-100 transition"
                >
                  <span class="flex items-center gap-2 text-slate-900">
                    <span class="text-base">📑</span>
                    Centro de reportes
                  </span>
                  <span class="text-slate-400">→</span>
                </Link>

                <Link
                  :href="route('catalogo.index')"
                  class="flex items-center justify-between rounded-2xl bg-orange-50 px-3 py-2 ring-1 ring-orange-100 hover:bg-orange-100 transition"
                >
                  <span class="flex items-center gap-2 text-orange-900">
                    <span class="text-base">🗂️</span>
                    Ver catálogo
                  </span>
                  <span class="text-orange-400">→</span>
                </Link>

                <Link
                  :href="route('admin.usuarios.index')"
                  class="flex items-center justify-between rounded-2xl bg-emerald-50 px-3 py-2 ring-1 ring-emerald-100 hover:bg-emerald-100 transition"
                >
                  <span class="flex items-center gap-2 text-emerald-900">
                    <span class="text-base">👥</span>
                    Usuarios internos
                  </span>
                  <span class="text-emerald-400">→</span>
                </Link>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Fondo para cerrar dropdowns -->
    <div
      v-if="showBitacora || showNotif"
      class="fixed inset-0 z-10"
      @click.self="closeMenus"
    />
  </AuthenticatedLayout>
</template>
