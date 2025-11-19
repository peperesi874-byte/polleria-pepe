<script setup>
import { usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import NavLink from '@/Components/NavLink.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

const roleId = computed(() => Number(user.value?.role_id ?? 0))

// 👉 A dónde debe mandar el botón "Panel" según el rol
const panelHref = computed(() => {
  if (!user.value) return route('login')

  switch (roleId.value) {
    case 1:  return route('admin.dashboard')
    case 2:  return route('vendedor.dashboard')
    case 3:  return route('repartidor.dashboard')
    case 4:  return route('cliente.inicio')
    default: return route('catalogo.index')
  }
})

// 👉 Cuándo marcar "Panel" como activo (en cualquier panel interno)
const isPanelActive = computed(() =>
  route().current('admin.*') ||
  route().current('vendedor.*') ||
  route().current('repartidor.*') ||
  route().current('cliente.*')
)

function logout() {
  router.post(route('logout'))
}
</script>

<template>
  <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-neutral-200">
    <nav class="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 h-14">
      <!-- Logo / marca -->
      <a href="/" class="flex items-center gap-2">
        <img src="/storage/logo.png" class="h-8 w-auto" alt="Pollería Pepe" />
        <span class="font-semibold">Pollería Pepe</span>
      </a>

      <!-- Links derecha -->
      <div class="flex items-center gap-2">
        <NavLink
          :href="route('catalogo.index')"
          :active="route().current('catalogo.index')"
        >
          Catálogo
        </NavLink>

        <!-- Si hay sesión -->
        <template v-if="user">
          <NavLink
            :href="panelHref"
            :active="isPanelActive"
          >
            Panel
          </NavLink>

          <button
            @click="logout"
            class="ml-1 px-3 py-2 rounded-lg text-sm font-medium text-white bg-neutral-900 hover:bg-neutral-800 transition"
          >
            Cerrar sesión
          </button>
        </template>

        <!-- Si NO hay sesión -->
        <template v-else>
          <a
            :href="route('login')"
            class="px-3 py-2 rounded-lg text-sm font-medium text-neutral-700 hover:bg-neutral-100"
          >
            Iniciar sesión
          </a>
          <a
            :href="route('register')"
            class="px-3 py-2 rounded-lg text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600"
          >
            Registrarse
          </a>
        </template>
      </div>
    </nav>
  </header>

  <!-- Contenido -->
  <main>
    <slot />
  </main>
</template>
