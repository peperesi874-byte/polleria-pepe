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

/** Helpers */
const money = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

const estadoPill = (e) => {
  const map = {
    pendiente:  'bg-yellow-100 text-yellow-800 ring-yellow-200',
    preparando: 'bg-amber-100 text-amber-800 ring-amber-200',
    listo:      'bg-blue-100 text-blue-800 ring-blue-200',
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
      <div class="relative">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Panel de administración</h2>
            <p class="mt-0.5 text-sm text-gray-500">Resumen rápido del sistema y accesos directos.</p>
          </div>

          <!-- Acciones / Top-bar (solo Notificaciones y Bitácora) -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- 🔔 Notificaciones -->
            <button
              type="button"
              @click="toggleNotif"
              class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 bg-white hover:bg-gray-50"
              aria-haspopup="true"
              :aria-expanded="showNotif ? 'true' : 'false'"
            >
              🔔 Notificaciones
              <span
                v-if="(notifsList?.length ?? 0) > 0"
                class="ml-1 rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold text-white"
              >
                {{ notifsList.length }}
              </span>
            </button>

            <!-- 📜 Bitácora -->
            <button
              type="button"
              @click="toggleBitacora"
              class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 bg-white hover:bg-gray-50"
              aria-haspopup="true"
              :aria-expanded="showBitacora ? 'true' : 'false'"
            >
              📜 Bitácora
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
        <div v-if="showNotif" class="absolute right-0 z-20 mt-2 w/full max-w-md rounded-2xl border bg-white p-4 shadow-lg">
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-semibold text-gray-900">Notificaciones</h4>
            <button class="text-sm text-gray-500 hover:text-gray-700" @click="closeMenus">Cerrar</button>
          </div>

          <ul v-if="(notifsList?.length ?? 0) > 0" class="divide-y">
            <li v-for="(n, i) in notifsList.slice(0,10)" :key="n.id ?? i" class="py-3">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ n.titulo ?? n.tipo ?? 'Notificación' }}</p>
                  <p class="text-xs text-gray-500">{{ n.mensaje ?? n.detalle ?? '' }}</p>
                </div>
                <span class="text-[11px] text-gray-400 whitespace-nowrap">
                  {{ fmtDate(n.fecha ?? n.when ?? n.created_at) }}
                </span>
              </div>
            </li>
          </ul>
          <p v-else class="text-sm text-gray-500">No hay notificaciones por ahora.</p>    

        </div>

        <!-- Dropdown Bitácora -->
        <div v-if="showBitacora" class="absolute right-0 z-20 mt-2 w/full max-w-2xl rounded-2xl border bg-white p-4 shadow-lg">
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-semibold text-gray-900">Bitácora reciente</h4>
            <button class="text-sm text-gray-500 hover:text-gray-700" @click="closeMenus">Cerrar</button>
          </div>

          <div class="max-h-[60vh] overflow-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-600 uppercase text-xs sticky top-0">
                <tr>
                  <th class="px-3 py-2 text-left">Fecha</th>
                  <th class="px-3 py-2 text-left">Actor</th>
                  <th class="px-3 py-2 text-left">Acción</th>
                  <th class="px-3 py-2 text-left">Entidad</th>
                  <th class="px-3 py-2 text-left">Detalle</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(b, i) in bitacoraList.slice(0,20)" :key="b.id ?? i" class="border-b last:border-0 hover:bg-gray-50">
                  <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ b.fecha ?? fmtDate(b.when ?? b.created_at) }}</td>
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
            <Link :href="route('admin.bitacora.index')" class="text-indigo-600 hover:underline text-sm">Ir a Bitácora</Link>
          </div>
        </div>
      </div>
    </template>

    <!-- Contenido principal (cards, tablas, etc.) -->
    <div class="mx-auto max-w-7xl p-6 space-y-8">
      <!-- Cards -->
      <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <Link :href="route('productos.index')" class="group rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Productos</p>
            <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">ver</span>
          </div>
          <div class="mt-2 text-4xl font-extrabold text-indigo-700">{{ cards.productos }}</div>
          <p class="mt-1 text-xs text-gray-400">Activos en catálogo</p>
        </Link>

        <Link :href="route('admin.pedidos.index')" class="group rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Pedidos</p>
            <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">ver</span>
          </div>
          <div class="mt-2 text-4xl font-extrabold text-indigo-700">{{ cards.pedidos }}</div>
          <p class="mt-1 text-xs text-gray-400">Totales en el sistema</p>
        </Link>

        <Link :href="route('admin.usuarios.index')" class="group rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Usuarios</p>
            <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">ver</span>
          </div>
          <div class="mt-2 text-4xl font-extrabold text-indigo-700">{{ cards.usuarios }}</div>
          <p class="mt-1 text-xs text-gray-400">Administradores y personal</p>
        </Link>

        <Link :href="route('admin.inventario.index')" class="group rounded-2xl border bg-white p-5 shadow-sm hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Inventario bajo mínimo</p>
            <span :class="['rounded-lg px-2 py-1 text-xs font-medium', cards.inventario_bajo > 0 ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700']">
              {{ cards.inventario_bajo > 0 ? 'atender' : 'ok' }}
            </span>
          </div>
          <div class="mt-2 text-4xl font-extrabold" :class="cards.inventario_bajo > 0 ? 'text-rose-700' : 'text-emerald-700'">
            {{ cards.inventario_bajo }}
          </div>
          <p class="mt-1 text-xs text-gray-400">Productos por debajo del umbral</p>
        </Link>
      </section>

      <!-- Últimos pedidos + Lateral -->
      <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Últimos pedidos</h3>
            <Link :href="route('admin.pedidos.index')" class="text-indigo-600 hover:underline text-sm">Ver todos</Link>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
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
              <tbody>
                <tr v-for="p in ultimos" :key="p.id" class="border-b last:border-0 hover:bg-gray-50">
                  <td class="px-4 py-2 font-medium text-gray-500">#{{ p.id }}</td>
                  <td class="px-4 py-2 font-semibold text-gray-800">{{ p.folio ?? '—' }}</td>
                  <td class="px-4 py-2"><span :class="estadoPill(p.estado)">{{ p.estado }}</span></td>
                  <td class="px-4 py-2 text-gray-600">{{ p.tipo }}</td>
                  <td class="px-4 py-2 text-gray-700">{{ p.items }}</td>
                  <td class="px-4 py-2 font-semibold">{{ money(p.total) }}</td>
                  <td class="px-4 py-2 text-gray-500">{{ fmtDate(p.created_at) }}</td>
                  <td class="px-4 py-2 text-right">
                    <Link :href="route('admin.pedidos.show', p.id)" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver</Link>
                  </td>
                </tr>
                <tr v-if="ultimos.length === 0">
                  <td colspan="8" class="px-4 py-6 text-center text-gray-500">Aún no hay pedidos registrados.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Lateral -->
        <div class="space-y-6">
          <div class="rounded-2xl border bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-semibold text-gray-900">Alertas de inventario</h3>
              <Link :href="route('admin.inventario.index')" class="text-indigo-600 hover:underline text-sm">Ver inventario</Link>
            </div>
            <ul v-if="tieneAlertas" class="divide-y">
              <li v-for="a in alertas" :key="a.id" class="py-3 flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-800">{{ a.nombre }}</p>
                  <p class="text-xs text-gray-500">
                    Stock: <span class="font-semibold">{{ a.stock_actual }}</span> /
                    Mín: <span class="font-semibold">{{ a.stock_minimo }}</span>
                  </p>
                </div>
                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-rose-200">Bajo</span>
              </li>
            </ul>
            <p v-else class="text-sm text-gray-500">Sin alertas por ahora ✅</p>
          </div>

          <div class="rounded-2xl border bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Accesos rápidos</h3>
            <div class="grid grid-cols-1 gap-2">
              <Link :href="route('productos.create')" class="inline-flex items-center justify-between rounded-xl border px-3 py-2 hover:bg-gray-50 text-sm">
                <span>➕ Crear producto</span><span class="text-gray-400">→</span>
              </Link>
              <Link :href="route('admin.reportes.index')" class="inline-flex items-center justify-between rounded-xl border px-3 py-2 hover:bg-gray-50 text-sm">
                <span>📑 Centro de reportes</span><span class="text-gray-400">→</span>
              </Link>
              <Link :href="route('catalogo.index')" class="inline-flex items-center justify-between rounded-xl border px-3 py-2 hover:bg-gray-50 text-sm">
                <span>🗂️ Ver catálogo</span><span class="text-gray-400">→</span>
              </Link>
              <Link :href="route('admin.usuarios.index')" class="inline-flex items-center justify-between rounded-xl border px-3 py-2 hover:bg-gray-50 text-sm">
                <span>👥 Usuarios internos</span><span class="text-gray-400">→</span>
              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Fondo para cerrar dropdowns -->
    <div v-if="showBitacora || showNotif" class="fixed inset-0 z-10" @click="closeMenus" />
  </AuthenticatedLayout>
</template>
