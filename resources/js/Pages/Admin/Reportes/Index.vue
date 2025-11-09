<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

// helpers (se quedan igual)
const btn = (variant = 'solid') =>
  [
    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-300',
    variant === 'solid'
      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
      : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
  ].join(' ')

const card =
  'group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition'
</script>

<template>
  <Head title="Reportes" />
  <AuthenticatedLayout>
    <!-- HEADER UNIFICADO (como Configuración/Usuarios/Productos) -->
    <template #header>
      <div
        class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-2xl bg-indigo-100 text-indigo-700">
            <!-- ícono -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 5h18v2H3zM3 11h12v2H3zM3 17h18v2H3z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">Reportes</h2>
            <p class="text-sm text-gray-500">Centro de reportes del sistema</p>
          </div>
        </div>

        <Link
          :href="route('admin.dashboard')"
          class="text-sm text-indigo-600 hover:underline"
        >
          ← Volver al panel
        </Link>
      </div>
    </template>

    <div class="mx-auto max-w-7xl p-6 space-y-6">
      <!-- Sección: Inventario -->
      <section>
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800">Inventario</h3>
          <Link :href="route('admin.inventario.index')" class="text-sm text-indigo-600 hover:underline">
            Ir al inventario →
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Inventario completo -->
          <div :class="card">
            <div class="flex items-start gap-4">
              <div class="rounded-xl bg-indigo-50 p-3 text-indigo-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M21 7.5V18a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V7.5l9-4.5 9 4.5zM12 5.19 6.75 7.8v2.7L12 7.88l5.25 2.62V7.8L12 5.19z"/>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">Inventario completo</h4>
                <p class="mt-1 text-sm text-gray-500">
                  Exporta el inventario con stock actual y mínimo.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                  <a :href="route('admin.inventario.export.csv')" class="!mr-2" :class="btn('outline')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm1 7H9V7h6zm0 4H9v-2h6zm-3 4H9v-2h3z"/>
                    </svg>
                    CSV
                  </a>

                  <a :href="route('admin.inventario.export.pdf')" target="_blank" :class="btn('solid')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm-1 13h-1v3h-2v-8h3a2 2 0 0 1 0 4zm0-3h-1v1h1a.5.5 0 0 0 0-1z"/>
                    </svg>
                    PDF
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Historial de movimientos -->
          <div :class="card">
            <div class="flex items-start gap-4">
              <div class="rounded-xl bg-sky-50 p-3 text-sky-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M3 5h18v2H3zM3 11h12v2H3zM3 17h18v2H3z"/>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">Historial de movimientos</h4>
                <p class="mt-1 text-sm text-gray-500">
                  Exporta el historial por producto (respetando filtros).
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                  <a :href="route('admin.inventario.historial.csv')" :class="btn('outline')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm1 7H9V7h6z"/>
                    </svg>
                    CSV
                  </a>
                  <Link :href="route('admin.inventario.index')" class="text-sm text-indigo-600 hover:underline">
                    Ir a inventario →
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Sección: Catálogo -->
      <section>
        <div class="mb-3">
          <h3 class="text-lg font-semibold text-gray-800">Catálogo de productos</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div :class="card">
            <div class="flex items-start gap-4">
              <div class="rounded-xl bg-emerald-50 p-3 text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M21 11l-8-8H5a2 2 0 0 0-2 2v8l8 8 10-10zM7 7a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">Listado de productos</h4>
                <p class="mt-1 text-sm text-gray-500">
                  Exporta la lista de productos (nombre y precio).
                </p>

                <div class="mt-4">
                  <a :href="route('admin.reportes.productos.csv')" :class="btn('outline')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    </svg>
                    CSV
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-dashed border-gray-300 p-5">
            <div class="flex h-full items-center justify-center text-gray-400 text-sm">
              Próximo reporte…
            </div>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
