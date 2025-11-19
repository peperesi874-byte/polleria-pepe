<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()
const user = computed(() => page.props?.auth?.user)

const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})
</script>

<template>
  <header class="flex flex-col gap-6">
    <!-- Parte de arriba -->
    <div
      class="flex items-center justify-between gap-4 rounded-2xl bg-white/80 backdrop-blur-md shadow-md px-6 py-4 border border-neutral-200"
    >
      <div>
        <p class="text-xs font-semibold tracking-[0.2em] text-amber-600 uppercase">
          Panel del cliente
        </p>

        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-neutral-900 flex items-center gap-2">
          <span>{{ saludo }},</span>
          <span class="text-amber-600">{{ user?.name || 'Cliente' }}</span>
        </h1>

        <p class="mt-2 text-sm text-neutral-600">
          Gestiona tu compra, pedidos y datos desde aquí.
        </p>
      </div>

      <!-- 🔥 SOLO dejamos Cerrar Sesión -->
      <div class="flex items-center gap-3">
        <form method="post" action="/logout">
          <input type="hidden" name="_token" :value="$page.props.csrf_token" />
          <button
            type="button"
            class="rounded-xl border border-rose-500 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 active:scale-95 transition"
            @click.prevent="$inertia.post(route('logout'))"
          >
            Cerrar sesión
          </button>
        </form>
      </div>
    </div>

    <!-- Tabs -->
    <nav class="flex flex-wrap items-center gap-3 text-sm">
      <div class="flex flex-wrap gap-2">
       <!-- Inicio -->
<Link
  :href="route('cliente.inicio')"
  class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-300 bg-white text-sm font-medium text-slate-700 shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
  :class="{ 'bg-amber-500 border-amber-500 text-white shadow-md': route().current('cliente.inicio') }"
>
  🏠 Inicio
</Link>


        <Link
          :href="route('catalogo.index')"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>🛍️</span>
          <span>Catálogo</span>
        </Link>

        <Link
          :href="route('cliente.pedidos.index')"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>📦</span>
          <span>Mis pedidos</span>
        </Link>

        <Link
          :href="route('cliente.perfil.edit')"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>👤</span>
          <span>Mis datos</span>
        </Link>

        <Link
          :href="route('cliente.carrito.index')"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>🧺</span>
          <span>Carrito</span>
        </Link>

        <!-- Pendientes -->
        <button
          type="button"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-200 bg-neutral-50 text-neutral-400 font-medium cursor-default whitespace-nowrap"
        >
          <span>📍</span>
          <span>Direcciones</span>
        </button>

        <button
          type="button"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-200 bg-neutral-50 text-neutral-400 font-medium cursor-default whitespace-nowrap"
        >
          <span>🔔</span>
          <span>Notificaciones</span>
        </button>
      </div>
    </nav>
  </header>
</template>
