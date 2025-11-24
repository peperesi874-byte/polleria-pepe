<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

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

// ✅ URLs directas para evitar que Ziggy se queje
const INVENTARIO_EXPORT_CSV_URL      = '/admin/inventario/export/csv'
const INVENTARIO_EXPORT_PDF_URL      = '/admin/inventario/export/pdf'
const INVENTARIO_HISTORIAL_CSV_URL   = '/admin/inventario/movimientos/export/csv'
const REPORTES_PRODUCTOS_CSV_URL     = '/admin/reportes/productos/export/csv'
const REPORTES_PRODUCTOS_PDF_URL     = '/admin/reportes/productos/export/pdf'   // 🔴 NUEVO

// 👇 NUEVO: Reporte de ingresos
const REPORTES_INGRESOS_URL          = '/admin/reportes/ingresos'
const REPORTES_INGRESOS_CSV_URL      = '/admin/reportes/ingresos/export/csv'
const REPORTES_INGRESOS_PDF_URL      = '/admin/reportes/ingresos/export/pdf'    // 🔴 NUEVO
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
              <path d="M3 5h18v2H3zM3 11h12v2H3zM3 17h18v2H3z" />
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
                  <path
                    d="M21 7.5V18a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V7.5l9-4.5 9 4.5zM12 5.19 6.75 7.8v2.7L12 7.88l5.25 2.62V7.8L12 5.19z"
                  />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">Inventario completo</h4>
                <p class="mt-1 text-sm text-gray-500">
                  Exporta el inventario con stock actual y mínimo.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                  <!-- ⬇ CSV inventario (URL directa) -->
                  <a :href="INVENTARIO_EXPORT_CSV_URL" class="!mr-2" :class="btn('outline')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm1 7H9V7h6zm0 4H9v-2h6zm-3 4H9v-2h3z"
                      />
                    </svg>
                    CSV
                  </a>

                  <!-- ⬇ PDF inventario (URL directa) -->
                  <a :href="INVENTARIO_EXPORT_PDF_URL" target="_blank" :class="btn('solid')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm-1 13h-1v3h-2v-8h3a2 2 0 0 1 0 4zm0-3h-1v1h1a.5.5 0 0 0 0-1z"
                      />
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
                  <path d="M3 5h18v2H3zM3 11h12v2H3zM3 17h18v2H3z" />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">Historial de movimientos</h4>
                <p class="mt-1 text-sm text-gray-500">
                  Exporta el historial por producto (respetando filtros).
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                  <!-- ⬇ CSV movimientos (URL directa) -->
                  <a :href="INVENTARIO_HISTORIAL_CSV_URL" :class="btn('outline')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
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
                  <path d="M21 11l-8-8H5a2 2 0 0 0-2 2v8l8 8 10-10zM7 7a2 2 0 1 1 0 4 2 2 0 0 1 0-4z" />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">Listado de productos</h4>
                <p class="mt-1 text-sm text-gray-500">
                  Exporta la lista de productos (nombre y precio).
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                  <!-- ⬇ CSV productos (URL directa) -->
                  <a :href="REPORTES_PRODUCTOS_CSV_URL" :class="btn('outline')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    </svg>
                    CSV
                  </a>

                  <!-- 🔴 NUEVO: PDF productos -->
                  <a :href="REPORTES_PRODUCTOS_PDF_URL" target="_blank" :class="btn('solid')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zm-1 13h-1v3h-2v-8h3a2 2 0 0 1 0 4zm0-3h-1v1h1a.5.5 0 0 0 0-1z"
                      />
                    </svg>
                    PDF
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Ingresos (ventas entregadas) -->
          <div :class="card">
            <div class="flex items-start gap-4">
              <div class="rounded-xl bg-amber-50 p-3 text-amber-700">
                <!-- ícono de dinero -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 1.5c-5.8 0-10.5 4.7-10.5 10.5S6.2 22.5 12 22.5 22.5 17.8 22.5 12 17.8 1.5 12 1.5Zm.8 16.4v1.1h-1.6v-1.1c-1.7-.2-3-1.3-3.1-3.1h2c.1.9.7 1.6 2 1.6 1.2 0 1.9-.6 1.9-1.4 0-.7-.5-1.1-1.8-1.4l-1-.2c-2-.4-3.1-1.3-3.1-3 0-1.6 1.2-2.8 3-3.1V4.5h1.6v1.1c1.6.2 2.7 1.2 2.9 2.8h-2c-.1-.8-.7-1.4-1.7-1.4-1.1 0-1.8.5-1.8 1.3 0 .7.5 1.1 1.8 1.3l.9.2c2.1.4 3.1 1.3 3.1 3 0 1.7-1.2 3-3.1 3.2Z"/>
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">Ingresos (ventas entregadas)</h4>
                <p class="mt-1 text-sm text-gray-500">
                  Consulta y exporta ingresos diarios con filtro de fechas.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                  <!-- 👀 Ver reporte (URL directa a la vista) -->
                  <a :href="REPORTES_INGRESOS_URL" :class="btn('solid')">
                    Ver reporte
                  </a>
                  <!-- ⬇ Exportar CSV (URL directa) -->
                  <a :href="REPORTES_INGRESOS_CSV_URL" :class="btn('outline')">
                    CSV
                  </a>
                  <!-- 🔴 NUEVO: Exportar PDF -->
                  <a :href="REPORTES_INGRESOS_PDF_URL" target="_blank" :class="btn('outline')">
                    PDF
                  </a>
                </div>
              </div>
            </div>
          </div>
          <!-- /Ingresos -->
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
