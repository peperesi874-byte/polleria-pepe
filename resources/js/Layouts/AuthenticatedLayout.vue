<!-- resources/js/Layouts/AuthenticatedLayout.vue -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()

/* Usuario */
const userName = computed(() => page.props?.auth?.user?.name ?? 'Usuario')
const roleId   = computed(() => Number(page.props?.auth?.user?.role_id ?? 0))

/* Dropdown usuario */
const open = ref(false)
const toggle = () => { open.value = !open.value }
const close = (e) => {
  const t = e?.target
  if (!t || !(t.closest && t.closest('#user-menu'))) open.value = false
}
onMounted(() => document.addEventListener('click', close))
onBeforeUnmount(() => document.removeEventListener('click', close))

/* ✅ Helper mejorado: acepta string o array de patrones */
const isCurrent = (patterns) => {
  try {
    if (Array.isArray(patterns)) {
      return patterns.some(p => route().current(p))
    }
    return !!route().current(patterns)
  } catch {
    return false
  }
}

/* Clases para activo/inactivo */
const navLinkClasses = (active) => [
  'px-3 py-2 rounded-lg text-sm font-medium transition',
  active
    ? 'bg-indigo-100 text-indigo-800'
    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
].join(' ')

/* Ruta del logo */
const homeRoute = computed(() => {
  if (roleId.value === 1) return route('admin.dashboard')
  if (roleId.value === 2) return route('vendedor.dashboard')
  return route('catalogo.index')
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- TOPBAR -->
    <nav class="bg-white border-b border-gray-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">

          <!-- Izquierda -->
          <div class="flex items-center gap-6">

            <!-- LOGO -->
            <Link :href="homeRoute" class="flex items-center gap-2">
              <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold"
              >PP</span>
              <span class="sr-only">Pollería Pepe</span>
            </Link>

            <!-- MENÚ -->
            <div class="hidden md:flex items-center gap-1">

              <!-- ADMIN -->
              <template v-if="roleId === 1">
                <Link :href="route('admin.dashboard')" 
                      :class="navLinkClasses(isCurrent('admin.dashboard'))">
                  Panel
                </Link>

                <Link :href="route('admin.pedidos.index')" 
                      :class="navLinkClasses(isCurrent('admin.pedidos.index'))">
                  Pedidos
                </Link>

                <!-- ✅ Productos marcado con patrón productos.* -->
                <Link :href="route('productos.index')" 
                      :class="navLinkClasses(isCurrent(['productos.index','productos.*']))">
                  Productos
                </Link>

                <Link :href="route('admin.inventario.index')" 
                      :class="navLinkClasses(isCurrent('admin.inventario.index'))">
                  Inventario
                </Link>

                <Link :href="route('admin.reportes.index')" 
                      :class="navLinkClasses(isCurrent('admin.reportes.index'))">
                  Reportes
                </Link>

                <Link :href="route('admin.usuarios.index')" 
                      :class="navLinkClasses(isCurrent('admin.usuarios.index'))">
                  Usuarios
                </Link>

                <Link :href="route('admin.config.edit')" 
                      :class="navLinkClasses(isCurrent('admin.config.edit'))">
                  Configuración
                </Link>
              </template>

              <!-- VENDEDOR -->
              <template v-else-if="roleId === 2">
                <Link :href="route('vendedor.dashboard')" 
                      :class="navLinkClasses(isCurrent('vendedor.dashboard'))">
                  Panel
                </Link>

                <Link :href="route('vendedor.pedidos.index')" 
                      :class="navLinkClasses(isCurrent('vendedor.pedidos.index'))">
                  Pedidos
                </Link>

                <!-- ✅ Productos también para vendedor -->
                <Link :href="route('productos.index')" 
                      :class="navLinkClasses(isCurrent(['productos.index','productos.*']))">
                  Productos
                </Link>

                <Link :href="route('vendedor.reportes.operativos')" 
                      :class="navLinkClasses(isCurrent('vendedor.reportes.operativos'))">
                  Reportes
                </Link>
              </template>

              <!-- PÚBLICO -->
              <template v-else>
                <Link :href="route('catalogo.index')" 
                      :class="navLinkClasses(isCurrent('catalogo.index'))">
                  Catálogo
                </Link>

                <!-- ✅ También aplica pattern -->
                <Link :href="route('productos.index')" 
                      :class="navLinkClasses(isCurrent(['productos.index','productos.*']))">
                  Productos
                </Link>
              </template>

            </div>
          </div>

          <!-- DERECHA: Usuario -->
          <div id="user-menu" class="relative">
            <button
              type="button"
              @click="toggle"
              class="flex items-center gap-3 rounded-xl border px-3 py-2 text-sm font-medium hover:bg-gray-50"
            >
              <span
                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-semibold"
              >
                {{ userName.slice(0,1).toUpperCase() }}
              </span>
              <span class="hidden sm:inline text-gray-800">{{ userName }}</span>

              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.21l3.71-3.98a.75.75 0 111.08 1.04l-4.24 4.54a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"/>
              </svg>
            </button>

            <div
              v-show="open"
              class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border bg-white shadow-lg z-50"
              role="menu"
            >
              <Link
                :href="route('profile.edit')"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                @click="open=false"
              >
                Perfil
              </Link>

              <Link
                :href="route('logout')"
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
    </nav>

    <!-- HEADER INTERNO -->
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
