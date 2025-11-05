<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const page = usePage()
const userName = computed(() => page.props?.auth?.user?.name ?? 'Usuario')

// Dropdown usuario
const open = ref(false)
function toggle() { open.value = !open.value }
function close(e) { if (!e.target.closest('#user-menu')) open.value = false }
onMounted(() => document.addEventListener('click', close))
onBeforeUnmount(() => document.removeEventListener('click', close))

// Helpers navegación activa (Ziggy)
const isCurrent = (name) => {
  try { return route().current(name) } catch { return false }
}
const navLinkClasses = (active) =>
  [
    'px-3 py-2 rounded-lg text-sm font-medium transition',
    active ? 'bg-indigo-100 text-indigo-800'
           : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
  ].join(' ')
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Topbar -->
    <nav class="bg-white border-b border-gray-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <!-- Izquierda: logo + navegación -->
          <div class="flex items-center gap-6">
            <Link :href="route('admin.dashboard')" class="flex items-center gap-2">
              <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold">PP</span>
              <span class="sr-only">Residencia Pollería Pepe</span>
            </Link>

            <!-- Links -->
            <div class="hidden md:flex items-center gap-1">
              <Link :href="route('admin.dashboard')"
                    :class="navLinkClasses(isCurrent('admin.dashboard'))">Panel</Link>

              <Link :href="route('productos.index')"
                    :class="navLinkClasses(isCurrent('productos.index'))">Productos</Link>

              <Link :href="route('catalogo.index')"
                    :class="navLinkClasses(isCurrent('catalogo.index'))">Catálogo</Link>

              <Link :href="route('admin.pedidos.index')"
                    :class="navLinkClasses(isCurrent('admin.pedidos.index'))">Pedidos</Link>

              <Link :href="route('admin.inventario.index')"
                    :class="navLinkClasses(isCurrent('admin.inventario.index'))">Inventario</Link>

              <!-- NUEVO: Reportes -->
              <Link :href="route('admin.reportes.index')"
                    :class="navLinkClasses(isCurrent('admin.reportes.index'))">Reportes</Link>

              <Link :href="route('admin.usuarios.index')"
                    :class="navLinkClasses(isCurrent('admin.usuarios.index'))">Usuarios</Link>
            </div>
          </div>

          <!-- Derecha: usuario -->
          <div id="user-menu" class="relative">
            <button @click="toggle"
                    class="flex items-center gap-3 rounded-xl border px-3 py-2 text-sm font-medium hover:bg-gray-50">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-semibold">
                {{ userName.slice(0,1).toUpperCase() }}
              </span>
              <span class="hidden sm:inline text-gray-800">{{ userName }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.21l3.71-3.98a.75.75 0 111.08 1.04l-4.24 4.54a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
              </svg>
            </button>

            <div v-show="open"
                 class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border bg-white shadow-lg z-50">
              <Link :href="route('profile.edit')"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    @click="open=false">Perfil</Link>

              <Link :href="route('logout')" method="post" as="button"
                    class="block w-full text-left px-4 py-2 text-sm text-rose-700 hover:bg-rose-50"
                    @click="open=false">Cerrar sesión</Link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Encabezado de página -->
    <header v-if="$slots.header" class="bg-white shadow-sm">
      <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Contenido -->
    <main>
      <slot />
    </main>
  </div>
</template>
