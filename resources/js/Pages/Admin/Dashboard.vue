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
    pendiente:  'bg-yellow-100 text-yellow-800 ring-yellow-200',
    preparando: 'bg-amber-100 text-amber-800 ring-amber-200',
    listo:      'bg-sky-100 text-sky-800 ring-sky-200',
    en_camino:  'bg-indigo-100 text-indigo-800 ring-indigo-200',
    entregado:  'bg-emerald-100 text-emerald-700 ring-emerald-200',
    cancelado:  'bg-rose-100 text-rose-700 ring-rose-200',
  }
  return `px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 ${map[e] ?? 'bg-gray-100 text-gray-700 ring-gray-200'}`
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

/** 📜 Bitácora en dashboard: props -> fallback a page.props.bitacoraTop */
const bitacoraList = computed(() => (props.bitacora?.length ?? 0) > 0
  ? props.bitacora
  : (page.props?.bitacoraTop ?? []))

/** 🔔 Notificaciones en dashboard: props -> fallback a page.props.notifTop (AppServiceProvider) */
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
    <template #header>
      <!-- HEADER más colorido -->
      <div
        class="relative rounded-2xl bg-gradient-to-r from-indigo-50 via-purple-50 to-amber-50 ring-1 ring-indigo-100/80 px-4 py-5 shadow-sm"
      >
        <!-- decoraciones suaves -->
        <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-indigo-200/30 blur-2xl" />
        <div class="pointer-events-none absolute -left-10 bottom-0 h-32 w-32 rounded-full bg-amber-200/40 blur-2xl" />

        <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <!-- Título / subtítulo + saludo -->
          <div class="flex items-start gap-3">
            <span
              class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-200"
            >
              🛠️
            </span>
            <div>
              <!-- 👋 Saludo personalizado -->
              <p class="text-sm text-gray-700">
                {{ saludo }}, <span class="font-semibold text-gray-900">{{ userName }}</span>
              </p>
              <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                Panel de administración
              </h2>
              <p class="mt-0.5 text-sm text-slate-700/80">
                Resumen rápido del sistema y accesos directos.
              </p>
            </div>
          </div>

          <!-- Acciones -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- 🔔 Notificaciones -->
            <button
              type="button"
              @click.stop="toggleNotif"
              class="inline-flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-amber-900 shadow-sm shadow-amber-100 transition hover:bg-amber-100 hover:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300/70"
              aria-haspopup="true"
              :aria-expanded="showNotif ? 'true' : 'false'"
            >
              <span>🔔 Notificaciones</span>
              <span
                v-if="(notifsList?.length ?? 0) > 0"
                class="ml-1 rounded-full bg-amber-600 px-2 py-0.5 text-[10px] font-semibold text-white"
              >
                {{ notifsList.length }}
              </span>
            </button>

            <!-- 📜 Bitácora -->
            <button
              type="button"
              @click.stop="toggleBitacora"
              class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-emerald-900 shadow-sm shadow-emerald-100 transition hover:bg-emerald-100 hover:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-300/70"
              aria-haspopup="true"
              :aria-expanded="showBitacora ? 'true' : 'false'"
            >
              <span>📜 Bitácora</span>
              <span
                v-if="(bitacoraList?.length ?? 0) > 0"
                class="ml-1 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-semibold text-white"
              >
                {{ bitacoraList.length }}
              </span>
            </button>
          </div>
        </div>

        <!-- Dropdown Notificaciones -->
        <div
          v-if="showNotif"
          class="absolute right-0 z-20 mt-3 w-full max-w-md rounded-2xl border border-indigo-100 bg-white/95 p-4 shadow-xl shadow-indigo-100 backdrop-blur"
        >
          <div class="mb-2 flex items-center justify-between">
            <h4 class="font-semibold text-gray-900">Notificaciones</h4>
            <button class="text-sm text-gray-500 hover:text-gray-700" @click="closeMenus">Cerrar</button>
          </div>

          <div class="max-h-[300px] overflow-y-auto pr-1">
            <ul class="divide-y divide-slate-100">
              <li
                v-for="(n, i) in notifsList" :key="n.id ?? i"
                class="py-3"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-medium text-slate-900">
                      {{ n.titulo ?? n.tipo ?? 'Notificación' }}
                    </p>
                    <p class="text-xs text-slate-500">
                      {{ n.mensaje ?? n.detalle ?? '' }}
                    </p>
                  </div>
                  <span class="whitespace-nowrap text-[11px] text-slate-400">
                    {{ fmtDate(n.fecha ?? n.when ?? n.created_at) }}
                  </span>
                </div>
              </li>
            </ul>
          </div>

          <div class="mt-3 text-right">
            <Link :href="route('admin.notificaciones.index')" class="text-sm text-indigo-600 hover:underline">
              Ver todas →
            </Link>
          </div>
        </div>

        <!-- Dropdown Bitácora -->
        <div
          v-if="showBitacora"
          class="absolute right-4 z-20 mt-3 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl shadow-slate-100 backdrop-blur"
        >
          <div class="mb-2 flex items-center justify-between">
            <h4 class="font-semibold text-gray-900">Bitácora reciente</h4>
            <button class="text-sm text-gray-500 hover:text-gray-700" @click="closeMenus">Cerrar</button>
          </div>

          <div class="max-h-[60vh] overflow-auto">
            <table class="w-full text-sm">
              <thead class="sticky top-0 bg-slate-50 text-xs uppercase text-slate-600">
                <tr>
                  <th class="px-3 py-2 text-left">Fecha</th>
                  <th class="px-3 py-2 text-left">Actor</th>
                  <th class="px-3 py-2 text-left">Acción</th>
                  <th class="px-3 py-2 text-left">Entidad</th>
                  <th class="px-3 py-2 text-left">Detalle</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(b, i) in bitacoraList.slice(0,20)"
                  :key="b.id ?? i"
                  class="border-b last:border-0 hover:bg-slate-50/80"
                >
                  <td class="whitespace-nowrap px-3 py-2 text-gray-500">{{ b.fecha ?? fmtDate(b.when ?? b.created_at) }}</td>
                  <td class="px-3 py-2 text-gray-700">{{ b.actor ?? b.usuario ?? '—' }}</td>
                  <td class="px-3 py-2 font-medium text-gray-800">{{ b.accion ?? b.action ?? '—' }}</td>
                  <td class="px-3 py-2 text-gray-700">{{ b.entidad ?? b.entity ?? '—' }}</td>
                  <td class="px-3 py-2 text-gray-600">
                    <span v-if="typeof (b.detalle ?? '') === 'string'">{{ b.detalle ?? b.meta ?? '—' }}</span>
                    <span v-else>{{ JSON.stringify(b.detalle ?? b.meta ?? {}, null, 0) }}</span>
                  </td>
                </tr>
                <tr v-if="(bitacoraList?.length ?? 0) === 0">
                  <td colspan="5" class="px-3 py-4 text-center text-gray-500">Sin registros de bitácora.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-3 text-right">
            <Link :href="route('admin.bitacora.index')" class="text-sm text-indigo-600 hover:underline">
              Ir a Bitácora
            </Link>
          </div>
        </div>
      </div>
    </template>

    <!-- Contenido principal (cards, tablas, etc.) -->
    <div class="mx-auto max-w-7xl p-6 space-y-8 bg-gradient-to-b from-slate-50 via-white to-amber-50/40 rounded-3xl">
      <!-- Cards -->
      <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <Link
          :href="route('productos.index')"
          class="group rounded-2xl border border-indigo-100 bg-white/95 p-5 shadow-sm hover:shadow-lg hover:border-indigo-300 hover:bg-indigo-50/70 transition"
        >
          <div class="flex items-center justify-between">
            <p class="text-sm text-slate-600">Productos</p>
            <span class="rounded-lg bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 group-hover:bg-indigo-600 group-hover:text-white transition">
              ver
            </span>
          </div>
          <div class="mt-3 flex items-end justify-between">
            <div class="text-4xl font-extrabold text-indigo-700">{{ cards.productos }}</div>
            <span class="rounded-full bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-600 border border-indigo-100">
              Catálogo
            </span>
          </div>
          <p class="mt-2 text-xs text-slate-400">Activos en catálogo</p>
        </Link>

        <Link
          :href="route('admin.pedidos.index')"
          class="group rounded-2xl border border-purple-100 bg-white/95 p-5 shadow-sm hover:shadow-lg hover:border-purple-300 hover:bg-purple-50/70 transition"
        >
          <div class="flex items-center justify-between">
            <p class="text-sm text-slate-600">Pedidos</p>
            <span class="rounded-lg bg-purple-100 px-2 py-1 text-xs font-medium text-purple-700 group-hover:bg-purple-600 group-hover:text-white transition">
              ver
            </span>
          </div>
          <div class="mt-3 flex items-end justify-between">
            <div class="text-4xl font-extrabold text-purple-700">{{ cards.pedidos }}</div>
            <span class="rounded-full bg-purple-50 px-2 py-1 text-[11px] font-semibold text-purple-600 border border-purple-100">
              Flujo
            </span>
          </div>
          <p class="mt-2 text-xs text-slate-400">Totales en el sistema</p>
        </Link>

        <Link
          :href="route('admin.usuarios.index')"
          class="group rounded-2xl border border-amber-100 bg-white/95 p-5 shadow-sm hover:shadow-lg hover:border-amber-300 hover:bg-amber-50/70 transition"
        >
          <div class="flex items-center justify-between">
            <p class="text-sm text-slate-600">Usuarios</p>
            <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 group-hover:bg-amber-500 group-hover:text-white transition">
              ver
            </span>
          </div>
          <div class="mt-3 flex items-end justify-between">
            <div class="text-4xl font-extrabold text-amber-700">{{ cards.usuarios }}</div>
            <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700 border border-amber-100">
              Equipo
            </span>
          </div>
          <p class="mt-2 text-xs text-slate-400">Administradores y personal</p>
        </Link>

        <Link
          :href="route('admin.inventario.index')"
          class="group rounded-2xl border bg-white/95 p-5 shadow-sm hover:shadow-lg transition"
          :class="cards.inventario_bajo > 0
            ? 'border-rose-100 hover:border-rose-300 hover:bg-rose-50/70'
            : 'border-emerald-100 hover:border-emerald-300 hover:bg-emerald-50/70'"
        >
          <div class="flex items-center justify-between">
            <p class="text-sm text-slate-600">Inventario bajo mínimo</p>
            <span
              :class="[
                'rounded-lg px-2 py-1 text-xs font-semibold',
                cards.inventario_bajo > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'
              ]"
            >
              {{ cards.inventario_bajo > 0 ? 'atender' : 'ok' }}
            </span>
          </div>
          <div
            class="mt-3 flex items-end justify-between"
          >
            <div
              class="text-4xl font-extrabold"
              :class="cards.inventario_bajo > 0 ? 'text-rose-700' : 'text-emerald-700'"
            >
              {{ cards.inventario_bajo }}
            </div>
            <span
              class="rounded-full px-2 py-1 text-[11px] font-semibold border"
              :class="cards.inventario_bajo > 0 ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100'"
            >
              Stock
            </span>
          </div>
          <p class="mt-2 text-xs text-slate-400">Productos por debajo del umbral</p>
        </Link>
      </section>

      <!-- Últimos pedidos + Lateral -->
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-900">Últimos pedidos</h3>
            <Link :href="route('admin.pedidos.index')" class="text-indigo-600 hover:underline text-sm">Ver todos</Link>
          </div>

          <div class="overflow-x-auto rounded-2xl border border-slate-100">
            <table class="w-full text-sm">
              <thead class="bg-gradient-to-r from-indigo-50 via-purple-50 to-amber-50 text-slate-700 uppercase text-xs">
                <tr>
                  <th class="px-4 py-2 text-left w-16">#</th>
                  <th class="px-4 py-2 text-left">Folio</th>
                  <th class="px-4 py-2 text-left">Estado</th>
                  <th class="px-4 py-2 text-left">Tipo</th>
                  <th class="px-4 py-2 text-left">Items</th>
                  <th class="px-4 py-2 text-left">Total</th>
                  <th class="px-4 py-2 text-left">Creado</th>
                  <th class="px-4 py-2 text-right">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(p, idx) in ultimos"
                  :key="p.id"
                  :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/60'"
                  class="hover:bg-indigo-50/50 transition"
                >
                  <td class="px-4 py-2 font-medium text-slate-500">#{{ p.id }}</td>
                  <td class="px-4 py-2 font-semibold text-slate-800">{{ p.folio ?? '—' }}</td>
                  <td class="px-4 py-2">
                    <span :class="estadoPill(p.estado)">{{ p.estado }}</span>
                  </td>
                  <td class="px-4 py-2 text-slate-600 capitalize">{{ p.tipo }}</td>
                  <td class="px-4 py-2 text-slate-700">{{ p.items }}</td>
                  <td class="px-4 py-2 font-semibold text-slate-900">{{ money(p.total) }}</td>
                  <td class="px-4 py-2 text-slate-500">{{ fmtDate(p.created_at) }}</td>
                  <td class="px-4 py-2 text-right">
                    <Link
                      :href="route('admin.pedidos.show', p.id)"
                      class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-600 hover:text-white transition"
                    >
                      Ver
                    </Link>
                  </td>
                </tr>
                <tr v-if="ultimos.length === 0">
                  <td colspan="8" class="px-4 py-6 text-center text-slate-500">Aún no hay pedidos registrados.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Lateral -->
        <div class="space-y-6">
          <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-semibold text-amber-900">Alertas de inventario</h3>
              <Link :href="route('admin.inventario.index')" class="text-amber-900 hover:underline text-sm">
                Ver inventario
              </Link>
            </div>
            <ul v-if="tieneAlertas" class="divide-y divide-amber-100">
              <li v-for="a in alertas" :key="a.id" class="py-3 flex items-center justify-between">
                <div>
                  <p class="font-medium text-amber-900">{{ a.nombre }}</p>
                  <p class="text-xs text-amber-900/80">
                    Stock: <span class="font-semibold">{{ a.stock_actual }}</span> /
                    Mín: <span class="font-semibold">{{ a.stock_minimo }}</span>
                  </p>
                </div>
                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">
                  Bajo
                </span>
              </li>
            </ul>
            <p v-else class="text-sm text-amber-900/80">Sin alertas por ahora ✅</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900 mb-3">Accesos rápidos</h3>
            <div class="grid grid-cols-1 gap-2">
              <Link
                :href="route('productos.create')"
                class="inline-flex items-center justify-between rounded-xl border border-indigo-100 bg-indigo-50/70 px-3 py-2 hover:bg-indigo-100 text-sm text-indigo-900"
              >
                <span>➕ Crear producto</span><span class="text-indigo-400">→</span>
              </Link>
              <Link
                :href="route('admin.reportes.index')"
                class="inline-flex items-center justify-between rounded-xl border border-purple-100 bg-purple-50/70 px-3 py-2 hover:bg-purple-100 text-sm text-purple-900"
              >
                <span>📑 Centro de reportes</span><span class="text-purple-400">→</span>
              </Link>
              <Link
                :href="route('catalogo.index')"
                class="inline-flex items-center justify-between rounded-xl border border-amber-100 bg-amber-50/70 px-3 py-2 hover:bg-amber-100 text-sm text-amber-900"
              >
                <span>🗂️ Ver catálogo</span><span class="text-amber-400">→</span>
              </Link>
              <Link
                :href="route('admin.usuarios.index')"
                class="inline-flex items-center justify-between rounded-xl border border-emerald-100 bg-emerald-50/70 px-3 py-2 hover:bg-emerald-100 text-sm text-emerald-900"
              >
                <span>👥 Usuarios internos</span><span class="text-emerald-400">→</span>
              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Fondo para cerrar dropdowns -->
    <div
      v-if="showBitacora || showNotif"
      class="fixed inset-0 z-10"
      @click.self="closeMenus"
    />
  </AuthenticatedLayout>
</template>
