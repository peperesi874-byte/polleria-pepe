<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

/* Usuario (de props compartidas de Inertia) */
const page = usePage()
const user = computed(() => page.props?.auth?.user ?? null)
const userName = computed(() => user.value?.name ?? 'Usuario')
const userInitial = computed(() => (userName.value || 'U').slice(0, 1).toUpperCase())

/* Menú de usuario (dropdown) */
const open = ref(false)
const toggle = () => { open.value = !open.value }
const onDocClick = (e) => {
  const t = e?.target
  if (!t || !(t.closest && t.closest('#user-menu'))) open.value = false
}
const onKeydown = (e) => {
  if (e.key === 'Escape') open.value = false
}
onMounted(() => {
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onKeydown)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onKeydown)
})

/* Helper de link activo (Ziggy) */
const isCurrent = (name) => {
  try { return !!route().current(name) } catch { return false }
}
const linkC = (active) =>
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
            <!-- Logo: lleva al dashboard del vendedor -->
            <Link :href="route('vendedor.dashboard')" class="flex items-center gap-2">
              <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold"
                aria-label="Inicio"
              >PP</span>
              <span class="sr-only">Residencia Pollería Pepe</span>
            </Link>

            <!-- Menú principal -->
            <div class="hidden md:flex items-center gap-1">
              <Link :href="route('vendedor.dashboard')"
                    :class="linkC(isCurrent('vendedor.dashboard'))">
                Panel
              </Link>

              <Link :href="route('vendedor.pedidos.index')"
                    :class="linkC(isCurrent('vendedor.pedidos.index'))">
                Pedidos
              </Link>

              <Link :href="route('catalogo.index')"
                    :class="linkC(isCurrent('catalogo.index'))">
                Catálogo
              </Link>

              <Link :href="route('vendedor.reportes.operativos')"
                    :class="linkC(isCurrent('vendedor.reportes.operativos'))">
                Reportes
              </Link>
            </div>
          </div>

          <!-- Derecha: usuario -->
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
                :title="userName"
              >
                {{ userInitial }}
              </span>
              <span class="hidden sm:inline text-gray-800">{{ userName }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.21l3.71-3.98a.75.75 0 111.08 1.04l-4.24 4.54a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
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
                role="menuitem"
              >
                Perfil
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="block w-full text-left px-4 py-2 text-sm text-rose-700 hover:bg-rose-50"
                @click="open=false"
                role="menuitem"
              >
                Cerrar sesión
              </Link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Encabezado de página (opcional) -->
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
