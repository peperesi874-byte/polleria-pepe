<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  registros: Object, // paginator que viene del controlador
})

const items = computed(() => props.registros?.data ?? [])
const links = computed(() => props.registros?.links ?? [])
const total = computed(() => props.registros?.total ?? items.value.length)
</script>

<template>
  <AuthenticatedLayout role="vendedor">
    <Head title="Bitácora" />

    <!-- HEADER -->
    <template #header>
      <div
        class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-amber-50 via-white to-white px-4 py-4 ring-1 ring-amber-100/70"
      >
        <div class="flex items-center gap-3">
          <span
            class="inline-grid h-10 w-10 place-items-center rounded-xl bg-amber-100 text-amber-700 text-xl"
          >
            📑
          </span>
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">
              Bitácora del vendedor
            </h1>
            <p class="mt-0.5 text-sm text-gray-500">
              Historial de movimientos que realizaste sobre pedidos.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- Resumen pequeño -->
          <div class="hidden sm:flex flex-col items-end text-xs text-gray-500">
            <span class="font-medium text-gray-700">
              Registros: {{ total }}
            </span>
            <span v-if="items.length" class="text-[11px]">
              Mostrando los movimientos más recientes primero
            </span>
          </div>

          <Link
            :href="route('vendedor.dashboard')"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition"
          >
            ← Volver al panel
          </Link>
        </div>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="mx-auto max-w-7xl p-6 space-y-4">
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <!-- Encabezado de sección -->
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <span class="inline-flex h-2 w-2 rounded-full bg-amber-500"></span>
            <span>Últimos movimientos</span>
          </div>
          <span class="text-xs text-gray-400">
            Se registran sólo acciones relacionadas con pedidos
          </span>
        </div>

        <!-- Tabla -->
        <div class="max-h-[70vh] overflow-auto">
          <table class="w-full border-collapse text-sm">
            <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur text-gray-500">
              <tr class="uppercase text-xs tracking-wide">
                <th class="px-4 py-3 text-left w-[140px]">Fecha</th>
                <th class="px-4 py-3 text-left w-[150px]">Módulo</th>
                <th class="px-4 py-3 text-left">Descripción</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="b in items"
                :key="b.id"
                class="border-t border-gray-100 hover:bg-amber-50/40 transition"
              >
                <!-- FECHA -->
                <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs sm:text-sm">
                  {{ b.fecha }}
                </td>

                <!-- MÓDULO -->
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800 capitalize"
                  >
                    {{ b.modulo || '—' }}
                  </span>
                </td>

                <!-- DESCRIPCIÓN -->
                <td class="px-4 py-3 text-gray-800 text-sm">
                  {{ b.descripcion || '—' }}
                </td>
              </tr>

              <tr v-if="items.length === 0">
                <td colspan="3" class="px-4 py-10 text-center text-gray-500 text-sm">
                  Aún no hay movimientos registrados en tu bitácora.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="links.length" class="mt-4 flex justify-end gap-2">
        <Link
          v-for="(l,i) in links"
          :key="i"
          :href="l.url || '#'"
          v-html="l.label"
          :class="[
            'min-w-9 select-none rounded-lg border px-3 py-1.5 text-center text-sm transition',
            l.active
              ? 'border-amber-600 bg-amber-600 text-white shadow-sm'
              : 'border-gray-300 bg-white text-gray-700 hover:bg-amber-50',
            !l.url && 'pointer-events-none opacity-40',
          ]"
          preserve-state
          preserve-scroll
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
