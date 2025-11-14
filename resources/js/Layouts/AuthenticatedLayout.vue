<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()

/* Usuario */
const userName = computed(() => page.props?.auth?.user?.name ?? 'Usuario')
const roleId   = computed(() => Number(page.props?.auth?.user?.role_id ?? 0))

/* Helper seguro para rutas (NO TRUENA si Ziggy no la reconoce) */
const safeRoute = (name, params = {}) => {
  try {
    return route(name, params)
  } catch {
    // Fallback manual SOLO para bitácora vendedor
    if (name === 'vendedor.bitacora.index') return '/vendedor/bitacora'

    // Fallback para notificaciones vendedor
    if (name === 'vendedor.notificaciones.index') return '/vendedor/notificaciones'

    return '#'
  }
}

/* 🔔 Contador de notificaciones */
const unreadNotifications = computed(() =>
  Number(page.props?.notificaciones?.unread_count ?? 0)
)

/* Ruta de notificaciones según rol */
const notifRoute = computed(() => {
  if (roleId.value === 1) return safeRoute('admin.notificaciones.index')
  if (roleId.value === 2) return safeRoute('vendedor.notificaciones.index')
  if (roleId.value === 3) return safeRoute('repartidor.pedidos.index') // por ahora
  // Cliente / otros
  return safeRoute('catalogo.index')
})

/* Dropdown usuario */
const open = ref(false)
const toggle = () => { open.value = !open.value }
const close = (e) => {
  const t = e?.target
  if (!t || !(t.closest && t.closest('#user-menu'))) open.value = false
}
onMounted(() => document.addEventListener('click', close))
onBeforeUnmount(() => document.removeEventListener('click', close))

/* Helpers navegación activa */
const isCurrent = (name) => {
  try { return !!route().current(name) } catch { return false }
}
const navLinkClasses = (active) => [
  'px-3 py-2 rounded-lg text-sm font-medium transition',
  active ? 'bg-indigo-100 text-indigo-800'
         : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
].join(' ')

/* Inicio según rol (para el LOGO) */
const homeRoute = computed(() => {
  try {
    if (roleId.value === 1) return route('admin.dashboard')        // Admin
    if (roleId.value === 2) return route('vendedor.dashboard')     // Vendedor
    if (roleId.value === 3) return route('repartidor.dashboard')   // Repartidor
    if (roleId.value === 4) return route('cliente.inicio')         // Cliente
    return route('catalogo.index')                                 // Público / otros
  } catch {
    return route('catalogo.index')
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 🔷 TOPBAR STICKY -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <!-- IZQUIERDA: LOGO + MENÚ -->
          <div class="flex items-center gap-6">
            <!-- LOGO -->
            <Link :href="homeRoute" class="flex items-center gap-2">
              <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold"
              >
                PP
              </span>
            </Link>

            <!-- MENÚ POR ROL -->
            <div class="hidden md:flex items-center gap-1">
              <!-- ADMIN -->
              <template v-if="roleId === 1">
                <Link :href="safeRoute('admin.dashboard')"         :class="navLinkClasses(isCurrent('admin.dashboard'))">Panel</Link>
                <Link :href="safeRoute('catalogo.index')"          :class="navLinkClasses(isCurrent('catalogo.index'))">Catálogo</Link>
                <Link :href="safeRoute('productos.index')"         :class="navLinkClasses(isCurrent('productos.index'))">Productos</Link>
                <Link :href="safeRoute('admin.pedidos.index')"     :class="navLinkClasses(isCurrent('admin.pedidos.index'))">Pedidos</Link>
                <Link :href="safeRoute('admin.inventario.index')"  :class="navLinkClasses(isCurrent('admin.inventario.index'))">Inventario</Link>
                <Link :href="safeRoute('admin.reportes.index')"    :class="navLinkClasses(isCurrent('admin.reportes.index'))">Reportes</Link>
                <Link :href="safeRoute('admin.usuarios.index')"    :class="navLinkClasses(isCurrent('admin.usuarios.index'))">Usuarios</Link>
                <Link :href="safeRoute('admin.config.edit')"       :class="navLinkClasses(isCurrent('admin.config.edit'))">Configuración</Link>
              </template>

              <!-- VENDEDOR -->
              <template v-else-if="roleId === 2">
                <Link :href="safeRoute('vendedor.dashboard')"           :class="navLinkClasses(isCurrent('vendedor.dashboard'))">Panel</Link>
                <Link :href="safeRoute('vendedor.pedidos.index')"       :class="navLinkClasses(isCurrent('vendedor.pedidos.index'))">Pedidos</Link>
                <Link :href="safeRoute('catalogo.index')"               :class="navLinkClasses(isCurrent('catalogo.index'))">Catálogo</Link>
                <Link :href="safeRoute('vendedor.reportes.operativos')" :class="navLinkClasses(isCurrent('vendedor.reportes.operativos'))">Reportes</Link>
                <Link :href="safeRoute('vendedor.bitacora.index')"      :class="navLinkClasses(isCurrent('vendedor.bitacora.index'))">Bitácora</Link>
              </template>

              <!-- REPARTIDOR -->
              <template v-else-if="roleId === 3">
                <Link :href="safeRoute('repartidor.dashboard')"      :class="navLinkClasses(isCurrent('repartidor.dashboard'))">Panel</Link>
                <Link :href="safeRoute('repartidor.pedidos.index')"  :class="navLinkClasses(isCurrent('repartidor.pedidos.index'))">Pedidos</Link>
                <!-- Nada de Productos ni Inventario para repartidor -->
              </template>

              <!-- CLIENTE -->
              <template v-else-if="roleId === 4">
                <Link :href="safeRoute('cliente.inicio')"           :class="navLinkClasses(isCurrent('cliente.inicio'))">Panel</Link>
                <Link :href="safeRoute('cliente.catalogo.index')"   :class="navLinkClasses(isCurrent('cliente.catalogo.index'))">Catálogo</Link>
                <Link :href="safeRoute('cliente.pedidos.index')"    :class="navLinkClasses(isCurrent('cliente.pedidos.index'))">Mis pedidos</Link>
                <Link :href="safeRoute('cliente.perfil.edit')"      :class="navLinkClasses(isCurrent('cliente.perfil.edit'))">Perfil</Link>
              </template>

              <!-- PÚBLICO / OTROS -->
              <template v-else>
                <Link :href="safeRoute('catalogo.index')" :class="navLinkClasses(isCurrent('catalogo.index'))">Catálogo</Link>
                <Link :href="safeRoute('productos.index')" :class="navLinkClasses(isCurrent('productos.index'))">Productos</Link>
              </template>
            </div>
          </div>

          <!-- DERECHA: NOTIFICACIONES + USUARIO -->
          <div class="flex items-center gap-4">
            <!-- 🔔 CAMPANITA -->
            <Link
              :href="notifRoute"
              class="relative inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition"
            >
              <span aria-hidden="true">🔔</span>
              <span
                class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold"
                :class="unreadNotifications > 0
                  ? 'bg-red-500 text-white'
                  : 'bg-gray-300 text-gray-700'"
              >
                {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
              </span>
            </Link>

            <!-- MENÚ USUARIO -->
            <div id="user-menu" class="relative">
              <button
                type="button"
                @click="toggle"
                class="flex items-center gap-3 rounded-xl border px-3 py-2 text-sm font-medium hover:bg-gray-50"
                :aria-expanded="open ? 'true' : 'false'"
                aria-haspopup="menu"
              >
                <span
                  class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-semibold"
                >
                  {{ userName.slice(0,1).toUpperCase() }}
                </span>
                <span class="hidden sm:inline text-gray-800">{{ userName }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.21l3.71-3.98a.75.75 0 111.08 1.04l-4.24 4.54a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
              </button>

              <div
                v-show="open"
                class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border bg-white shadow-lg z-50"
                role="menu"
              >
                <Link
                  :href="safeRoute('profile.edit')"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                  @click="open=false"
                >
                  Perfil
                </Link>

                <Link
                  :href="safeRoute('logout')"
                  method="post"
                  as="button"
                  class="block w-full text-left px-4 py-2 text-sm text-rose-700 hover:bg-rose-50"
                  @click="open=false"
                >
                  Cerrar sesión
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- ENCABEZADO -->
    <header v-if="$slots.header" class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- CONTENIDO -->
    <main>
      <slot />
    </main>
  </div>
</template>
