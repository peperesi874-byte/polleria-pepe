<script setup>
import { Head } from '@inertiajs/vue3'
import ClienteHeader from '@/Components/ClienteHeader.vue'

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <!-- 🩶 Fondo gris clarito igual que el carrito -->
  <div class="bg-slate-50/70">
    <Head title="Notificaciones" />

    <div
      class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8"
    >
      <!-- 🔹 Encabezado global del cliente (igual que carrito) -->
      <ClienteHeader />

      <!-- 🔸 Tarjeta principal del módulo (misma tarjeta que el carrito) -->
      <section
        class="bg-white/90 border border-slate-100 shadow-sm rounded-3xl
               px-4 sm:px-6 lg:px-8 py-6 sm:py-7 space-y-6 sm:space-y-7"
      >
        <!-- Encabezado de la sección Notificaciones -->
        <header class="flex flex-col gap-1">
          <div class="flex items-center gap-2">
            <span class="text-2xl">🔔</span>
            <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
              Notificaciones
            </h1>
          </div>
          <p class="mt-1 text-sm text-slate-500 max-w-xl">
            Revisa los avisos de tus pedidos y cambios importantes relacionados con tu cuenta.
          </p>
        </header>

        <!-- CONTENIDO -->

        <!-- Cuando aún no hay notificaciones -->
        <div
          v-if="!props.items.length"
          class="flex flex-col items-center justify-center gap-3
                 rounded-2xl border border-dashed border-slate-200
                 bg-slate-50/80 px-6 py-10 text-center"
        >
          <p class="text-base sm:text-lg font-medium text-slate-700">
            Aún no tienes notificaciones.
          </p>
          <p class="text-sm text-slate-500 max-w-md">
            Cuando tu pedido cambie de estado, verás los avisos correspondientes en este apartado.
          </p>
        </div>

        <!-- Lista de notificaciones -->
        <div v-else class="space-y-3 sm:space-y-4">
          <article
            v-for="n in props.items"
            :key="n.id"
            class="rounded-2xl border px-4 py-3 flex gap-3
                   shadow-sm/50 hover:shadow-md/60 transition-shadow"
            :class="n.leida
              ? 'bg-slate-50 border-slate-100'
              : 'bg-amber-50/60 border-amber-200'"
          >
            <div class="pt-1">
              <span
                v-if="!n.leida"
                class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"
              ></span>
            </div>

            <div class="flex-1">
              <header class="flex items-center justify-between gap-2 mb-1">
                <h2 class="font-semibold text-sm text-slate-900">
                  {{ n.titulo }}
                </h2>
                <span class="text-xs text-slate-400">
                  {{ n.created_at ? new Date(n.created_at).toLocaleString() : '' }}
                </span>
              </header>

              <p class="text-sm text-slate-700 whitespace-pre-line">
                {{ n.mensaje }}
              </p>

              <p
                v-if="n.tipo"
                class="mt-1 text-[11px] uppercase tracking-wide text-slate-400"
              >
                {{ n.tipo }}
              </p>

              <!-- Botón: Marcar como leído (solo si está pendiente) -->
<div v-if="!n.leida" class="mt-3 text-right">
  <button
    type="button"
    class="inline-flex items-center rounded-full border border-amber-300 bg-white px-3 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-50 active:scale-95 transition"
    @click="$inertia.post(`/cliente/notificaciones/${n.id}/leer`)"
  >
    Marcar como leído
  </button>
</div>

            </div>
          </article>
        </div>
      </section>
    </div>
  </div>
</template>
