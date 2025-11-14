<script setup>
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const isCurrent = name => route().current(name)
</script>

<template>
  <div class="min-h-screen bg-neutral-50/60">

    <!-- NAV PRINCIPAL DEL REPARTIDOR -->
    <nav class="bg-white border-b">
      <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-center gap-4">

          <!-- PANEL -->
          <Link
            :href="route('repartidor.dashboard')"
            class="px-4 py-3 text-sm font-semibold border-b-2 -mb-px"
            :class="
              isCurrent('repartidor.dashboard')
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-neutral-500 hover:text-neutral-900 hover:border-neutral-200'
            "
          >
            Panel
          </Link>

          <!-- PEDIDOS -->
          <Link
            :href="route('repartidor.pedidos.index')"
            class="px-4 py-3 text-sm font-semibold border-b-2 -mb-px"
            :class="
              isCurrent('repartidor.pedidos.index')
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-neutral-500 hover:text-neutral-900 hover:border-neutral-200'
            "
          >
            Pedidos
          </Link>

          <!-- CATÁLOGO (solo vista, sin acciones) -->
          <Link
            :href="route('catalogo.index')"
            class="px-4 py-3 text-sm font-semibold border-b-2 -mb-px border-transparent
                   text-neutral-500 hover:text-neutral-900 hover:border-neutral-200"
          >
            Catálogo
          </Link>

          <!-- REPORTES desactivado (no existe para repartidor según docs) -->
          <button
            disabled
            class="px-4 py-3 text-sm font-semibold border-b-2 -mb-px border-transparent
                   text-neutral-300 cursor-default"
          >
            Reportes
          </button>

          <!-- BITÁCORA desactivado -->
          <button
            disabled
            class="px-4 py-3 text-sm font-semibold border-b-2 -mb-px border-transparent
                   text-neutral-300 cursor-default"
          >
            Bitácora
          </button>

        </div>
      </div>
    </nav>

    <!-- CONTENIDO DINÁMICO -->
    <main>
      <slot />
    </main>

  </div>
</template>
