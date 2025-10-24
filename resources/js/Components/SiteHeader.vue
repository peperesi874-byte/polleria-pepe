<!-- resources/js/Components/SiteHeader.vue -->
<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

function safeRoute(name, params = {}, absolute = true) {
  try {
    if (typeof route !== 'undefined' && route().has(name)) {
      return route(name, params, absolute)
    }
  } catch (e) {}
  return '#'
}

function initials(str) {
  const s = (str || '').trim()
  if (!s) return 'U'
  const parts = s.split(/\s+/).slice(0, 2)
  return parts.map(p => p[0]?.toUpperCase() ?? '').join('') || 'U'
}
</script>

<template>
  <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-neutral-200">
    <nav class="max-w-7xl mx-auto h-14 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
      <!-- Marca -->
      <a :href="safeRoute('catalogo.index')" class="inline-flex items-center gap-2">
        <img src="/logo.jpg" alt="Pollería Pepe" class="h-8 w-8 rounded" />
        <span class="font-semibold tracking-tight text-neutral-900">Pollería Pepe</span>
      </a>

      <!-- Acciones -->
      <div class="flex items-center gap-3">
        <!-- Si hay sesión: muestra nombre -->
        <template v-if="user">
          <div class="hidden sm:flex items-center gap-2 bg-neutral-100 rounded-full px-3 py-1">
            <div class="h-6 w-6 rounded-full bg-amber-600 text-white grid place-items-center text-xs font-semibold">
              {{ initials(user?.name || user?.email) }}
            </div>
            <div class="text-sm font-medium text-neutral-800">
              {{ user?.name || user?.email }}
            </div>
          </div>

          <!-- Botón Panel -->
          <a
            :href="safeRoute('admin.dashboard')"
            class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm font-medium text-neutral-700 hover:border-amber-500 hover:text-amber-600 transition"
          >
            Panel
          </a>

          <!-- Botón Cerrar sesión -->
          <form
            v-if="typeof route !== 'undefined' && route().has('logout')"
            @submit.prevent="$inertia.post(route('logout'))"
          >
            <button
              type="submit"
              class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm font-medium text-neutral-700 hover:border-amber-500 hover:text-amber-600 transition"
            >
              Cerrar sesión
            </button>
          </form>
        </template>

        <!-- Invitado -->
        <template v-else>
          <a
            v-if="typeof route !== 'undefined' && route().has('login')"
            :href="route('login')"
            class="text-sm font-medium text-neutral-700 hover:text-amber-600"
          >
            Iniciar sesión
          </a>
          <a
            v-if="typeof route !== 'undefined' && route().has('register')"
            :href="route('register')"
            class="hidden sm:inline-block rounded-lg bg-amber-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-600 transition"
          >
            Registrarse
          </a>
        </template>
      </div>
    </nav>
  </header>
</template>
