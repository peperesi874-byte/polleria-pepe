<script setup>
import { usePage, router } from '@inertiajs/vue3'
import NavLink from '@/Components/NavLink.vue'

const page = usePage()
const user = page.props.auth?.user

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
        <NavLink :href="route('catalogo.index')" :active="route().current('catalogo.index')">
          Catálogo
        </NavLink>

        <!-- Si hay sesión -->
        <template v-if="user">
          <NavLink :href="route('admin.dashboard')" :active="route().current('admin.*')">
            Panel
          </NavLink>

          <button
            @click="logout"
            class="ml-1 px-3 py-2 rounded-lg text-sm font-medium text-white bg-neutral-900 hover:bg-neutral-800 transition">
            Cerrar sesión
          </button>
        </template>

        <!-- Si NO hay sesión -->
        <template v-else>
          <a :href="route('login')"
             class="px-3 py-2 rounded-lg text-sm font-medium text-neutral-700 hover:bg-neutral-100">
            Iniciar sesión
          </a>
          <a :href="route('register')"
             class="px-3 py-2 rounded-lg text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600">
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
