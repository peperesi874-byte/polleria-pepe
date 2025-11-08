<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()

const user = computed(() => page.props?.auth?.user ?? null)
const bitacora = computed(() => Array.isArray(page.props?.bitacoraTop) ? page.props.bitacoraTop : [])

const sidebarOpen = ref(false)
const showBitacora = ref(false)
const showUserMenu = ref(false)

const onKey = (e) => {
  if (e.key === 'Escape') {
    sidebarOpen.value = false
    showBitacora.value = false
    showUserMenu.value = false
  }
}
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

const is = (...names) => names.some(n => route().current(n))
const fmtDate = (iso) => {
  if (!iso) return '—'
  try {
    return new Intl.DateTimeFormat('es-MX', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(iso))
  } catch { return iso }
}
const chipFor = (accion = '') => {
  const a = String(accion).toLowerCase()
  if (a.includes('crear') || a.includes('alta') || a.includes('insert')) return 'bg-emerald-50 text-emerald-700 ring-emerald-200'
  if (a.includes('editar') || a.includes('update') || a.includes('modificar')) return 'bg-amber-50 text-amber-700 ring-amber-200'
  if (a.includes('eliminar') || a.includes('borrar') || a.includes('delete')) return 'bg-rose-50 text-rose-700 ring-rose-200'
  if (a.includes('estado') || a.includes('confirmar') || a.includes('cancelar')) return 'bg-indigo-50 text-indigo-700 ring-indigo-200'
  return 'bg-neutral-100 text-neutral-700 ring-neutral-200'
}
</script>

<template>
  <div class="min-h-screen bg-neutral-50 flex">
    <!-- Sidebar -->
    <aside
      class="w-64 shrink-0 bg-white border-r"
      :class="[
        'fixed inset-y-0 left-0 z-30 transform transition md:translate-x-0 md:static',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
      ]"
    >
      <div class="h-16 flex items-center justify-between px-4 border-b">
        <Link :href="route('catalogo.index')" class="font-semibold tracking-tight">Pollería Pepe</Link>
        <button class="md:hidden p-2 text-neutral-500" @click="sidebarOpen = false" aria-label="Cerrar menú">✕</button>
      </div>

      <nav class="p-3 space-y-1 text-[15px]">
        <Link
          :href="route('admin.dashboard')"
          class="block px-3 py-2 rounded-xl hover:bg-neutral-100"
          :class="{ 'bg-neutral-100 font-semibold': is('admin.dashboard') }"
        >
          🧭 Panel
        </Link>
        <Link
          :href="route('admin.productos.index')"
          class="block px-3 py-2 rounded-xl hover:bg-neutral-100"
          :class="{ 'bg-neutral-100 font-semibold': is('admin.productos.*','productos.*') }"
        >
          🛒 Productos
        </Link>
        <Link
          :href="route('admin.usuarios.index')"
          class="block px-3 py-2 rounded-xl hover:bg-neutral-100"
          :class="{ 'bg-neutral-100 font-semibold': is('admin.usuarios.*') }"
        >
          👥 Usuarios
        </Link>
        <Link
          :href="route('admin.reportes.index')"
          class="block px-3 py-2 rounded-xl hover:bg-neutral-100"
          :class="{ 'bg-neutral-100 font-semibold': is('admin.reportes.*') }"
        >
          📑 Reportes
        </Link>
      </nav>
    </aside>

    <!-- Content -->
    <div class="flex-1 min-w-0 flex flex-col md:ml-64">
      <!-- Topbar -->
      <header class="h-16 bg-white/90 backdrop-blur border-b flex items-center justify-between px-4 sticky top-0 z-20">
        <div class="flex items-center gap-3">
          <button class="md:hidden p-2 -ml-1" @click="sidebarOpen = true" aria-label="Abrir menú">☰</button>
          <h1 class="text-lg md:text-xl font-semibold tracking-tight">
            <slot name="title">Administración</slot>
          </h1>
        </div>

        <div class="flex items-center gap-2">
          <!-- Bitácora -->
          <div class="relative">
            <button
              type="button"
              @click="(showBitacora=!showBitacora, showUserMenu=false)"
              :aria-expanded="showBitacora ? 'true' : 'false'"
              aria-haspopup="true"
              class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 bg-white hover:bg-neutral-50 text-sm shadow-sm"
            >
              📜 Bitácora
              <span
                v-if="(bitacora?.length ?? 0) > 0"
                class="ml-1 rounded-full bg-indigo-600 px-2 py-0.5 text-[10px] font-semibold text-white"
              >{{ bitacora.length }}</span>
            </button>

            <div
              v-if="showBitacora"
              class="absolute right-0 mt-2 w-[44rem] max-w-[95vw] rounded-2xl border bg-white p-4 shadow-2xl"
            >
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-baseline gap-2">
                  <h4 class="font-semibold text-gray-900">Bitácora reciente</h4>
                  <span class="text-xs text-neutral-500">últimos registros</span>
                </div>
                <Link :href="route('admin.bitacora.index')" class="text-indigo-600 hover:underline text-sm">Ver completa →</Link>
              </div>

              <div class="max-h-[60vh] overflow-auto rounded-xl border">
                <table class="w-full text-sm">
                  <thead class="bg-neutral-50 text-neutral-600 uppercase text-xs sticky top-0">
                    <tr>
                      <th class="px-3 py-2 text-left">Fecha</th>
                      <th class="px-3 py-2 text-left">Actor</th>
                      <th class="px-3 py-2 text-left">Acción</th>
                      <th class="px-3 py-2 text-left">Entidad</th>
                      <th class="px-3 py-2 text-left">Detalle</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y">
                    <tr v-for="(b,i) in bitacora.slice(0,25)" :key="b.id ?? i" class="hover:bg-neutral-50">
                      <td class="px-3 py-2 text-neutral-500 whitespace-nowrap">{{ fmtDate(b.when ?? b.created_at) }}</td>
                      <td class="px-3 py-2 text-neutral-800">{{ b.actor ?? b.usuario ?? '—' }}</td>
                      <td class="px-3 py-2">
                        <span class="px-2 py-0.5 text-[11px] font-medium rounded-full ring-1" :class="chipFor(b.accion ?? b.action)">
                          {{ b.accion ?? b.action ?? 'acción' }}
                        </span>
                      </td>
                      <td class="px-3 py-2 text-neutral-800">{{ b.entidad ?? b.entity ?? '—' }}</td>
                      <td class="px-3 py-2 text-neutral-700"><span class="line-clamp-2">{{ b.detalle ?? b.meta ?? '—' }}</span></td>
                    </tr>
                    <tr v-if="(bitacora?.length ?? 0) === 0">
                      <td colspan="5" class="px-3 py-6 text-center text-neutral-500">Sin registros de bitácora.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Usuario -->
          <div class="relative">
            <button
              type="button"
              @click="(showUserMenu=!showUserMenu, showBitacora=false)"
              :aria-expanded="showUserMenu ? 'true' : 'false'"
              aria-haspopup="true"
              class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 bg-white hover:bg-neutral-50 text-sm shadow-sm"
            >
              <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600/10 text-indigo-700 font-semibold">
                {{ (user?.name ?? 'A')[0] }}
              </span>
              <span class="hidden sm:block text-neutral-700">{{ user?.name }}</span>
              <span class="text-neutral-400">▾</span>
            </button>

            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-56 rounded-2xl border bg-white p-2 shadow-xl"
            >
              <div class="px-3 py-2 text-xs text-neutral-500">Sesión</div>
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full text-left px-3 py-2 rounded-lg hover:bg-neutral-50 text-sm"
              >
                Cerrar sesión
              </Link>
            </div>
          </div>
        </div>
      </header>

      <!-- Page -->
      <main class="p-6">
        <slot />
      </main>
    </div>

    <!-- Overlay móvil -->
    <div
      v-if="showBitacora || showUserMenu || sidebarOpen"
      class="fixed inset-0 z-10 md:hidden"
      @click="showBitacora=false; showUserMenu=false; sidebarOpen=false"
    />
  </div>
</template>
