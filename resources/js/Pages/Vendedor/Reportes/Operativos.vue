<script setup>
import VendedorLayout from '@/Layouts/VendedorLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()
const props = defineProps({
  fecha: { type: String, required: true },
  totales: { type: Object, required: true },
  sin_asignar: { type: Array, default: () => [] },
})

const money = n => new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(n ?? 0)

const fecha = ref(props.fecha)
function goFecha () {
  // Cambio de fecha (GET ?fecha=YYYY-MM-DD)
  window.location.href = route('vendedor.reportes.operativos', { fecha: fecha.value })
}
</script>

<template>
  <VendedorLayout>
    <!-- Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Reportes operativos</h1>
        <div class="flex items-center gap-2">
          <input
            v-model="fecha"
            type="date"
            class="border rounded-lg px-3 py-2 text-sm"
          />
          <button
            @click="goFecha"
            class="bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm hover:bg-indigo-700"
          >
            Consultar
          </button>
        </div>
      </div>
      <p class="mt-1 text-sm text-gray-500">Resumen del día seleccionado.</p>
    </template>

    <div class="mx-auto max-w-7xl p-6 space-y-8">
      <!-- Tarjetas -->
      <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">Pedidos de hoy</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">{{ totales.total_hoy }}</p>
          <p class="text-xs text-gray-500 mt-1">Monto: <strong>{{ money(totales.monto_hoy) }}</strong></p>
        </article>

        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">Pendientes</p>
          <p class="mt-2 text-2xl font-semibold text-amber-700">{{ totales.pendiente }}</p>
        </article>

        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">Preparando</p>
          <p class="mt-2 text-2xl font-semibold text-amber-700">{{ totales.preparando }}</p>
        </article>

        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">Listo</p>
          <p class="mt-2 text-2xl font-semibold text-indigo-700">{{ totales.listo }}</p>
        </article>

        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">En camino</p>
          <p class="mt-2 text-2xl font-semibold text-indigo-700">{{ totales.en_camino }}</p>
        </article>

        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">Entregados</p>
          <p class="mt-2 text-2xl font-semibold text-green-700">{{ totales.entregado }}</p>
        </article>

        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">Cancelados</p>
          <p class="mt-2 text-2xl font-semibold text-rose-700">{{ totales.cancelado }}</p>
        </article>

        <article class="rounded-2xl border bg-white p-5">
          <p class="text-sm text-gray-500">Sin asignar</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">{{ totales.sin_asignar }}</p>
          <p class="text-xs text-gray-500 mt-1">Pedidos del día sin repartidor</p>
        </article>
      </section>

      <!-- Tabla de "sin asignar" (sin límite de filas) -->
      <section class="rounded-2xl border bg-white">
        <div class="p-5 border-b">
          <h2 class="text-lg font-semibold text-gray-900">Pedidos sin asignar</h2>
          <p class="text-sm text-gray-500">Todos los pedidos del día sin repartidor asignado.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
              <tr>
                <th class="px-4 py-3 text-left">Folio</th>
                <th class="px-4 py-3 text-left">Estado</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-left">Creado</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in sin_asignar" :key="p.id" class="hover:bg-gray-50 border-t">
                <td class="px-4 py-3 font-medium">{{ p.folio ?? ('#' + p.id) }}</td>
                <td class="px-4 py-3 capitalize">{{ p.estado?.replace('_',' ') }}</td>
                <td class="px-4 py-3 text-right font-medium">{{ money(p.total) }}</td>
                <td class="px-4 py-3">{{ p.created_at }}</td>
                <td class="px-4 py-3 text-right">
                  <Link
                    :href="route('vendedor.pedidos.show', p.id)"
                    class="text-indigo-600 hover:text-indigo-700 font-medium"
                  >
                    Ver detalle →
                  </Link>
                </td>
              </tr>

              <tr v-if="!sin_asignar.length">
                <td colspan="5" class="px-4 py-8 text-center text-neutral-500">
                  No hay pedidos sin asignar en esta fecha.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </VendedorLayout>
</template>
