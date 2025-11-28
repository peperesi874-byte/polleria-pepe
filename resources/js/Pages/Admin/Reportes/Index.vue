<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

/* ------ Botones y helpers ------ */
const btn = (variant = 'solid') =>
  [
    'inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-300',
    variant === 'solid'
      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
      : 'border border-slate-200 text-slate-700 hover:bg-slate-50'
  ].join(' ')

/* ------ URLs directas ------ */
const INVENTARIO_EXPORT_CSV_URL      = '/admin/inventario/export/csv'
const INVENTARIO_EXPORT_PDF_URL      = '/admin/inventario/export/pdf'
const INVENTARIO_HISTORIAL_CSV_URL   = '/admin/inventario/movimientos/export/csv'

const REPORTES_PRODUCTOS_CSV_URL     = '/admin/reportes/productos/export/csv'
const REPORTES_PRODUCTOS_PDF_URL     = '/admin/reportes/productos/export/pdf'

const REPORTES_INGRESOS_URL          = '/admin/reportes/ingresos'
const REPORTES_INGRESOS_CSV_URL      = '/admin/reportes/ingresos/export/csv'
const REPORTES_INGRESOS_PDF_URL      = '/admin/reportes/ingresos/export/pdf'
</script>

<template>
  <Head title="Reportes" />

  <AuthenticatedLayout>
    <!-- ===== HERO NARANJA (SIN FONDO DETRÁS) ===== -->
<template #header>
  <div class="pt-4 pb-6">
    <div
      class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
    >
      <!-- decor -->
      <div class="pointer-events-none absolute -left-16 -top-20 h-40 w-40 rounded-full bg-white/15 blur-3xl" />
      <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-40 rounded-full bg-black/20 blur-3xl" />

      <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <!-- IZQUIERDA -->
        <div class="space-y-3">
          <!-- etiqueta -->
          <div
            class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
          >
            <span class="text-lg">📊</span>
            <span>Centro de reportes — Pollería Pepe</span>
          </div>

          <!-- título + descripción -->
          <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-white drop-shadow">
              Reportes y exportaciones
            </h2>
            <p class="mt-1 max-w-xl text-sm text-amber-50/90">
              Descarga información clave de inventario, catálogo e ingresos en formatos listos
              para Excel o PDF.
            </p>
          </div>

          <!-- chips -->
          <div class="mt-1 flex flex-wrap gap-2 text-xs">
            <span
              class="inline-flex items-center gap-2 rounded-full bg-white/95 px-3 py-1 font-semibold text-amber-900 shadow-sm ring-1 ring-white/60"
            >
              <span class="text-sm">📦</span>
              Inventario y movimientos
            </span>

            <span
              class="inline-flex items-center gap-2 rounded-full bg-emerald-50/95 px-3 py-1 font-semibold text-emerald-900 shadow-sm ring-1 ring-emerald-100/80"
            >
              <span class="text-sm">🧾</span>
              Catálogo de productos
            </span>

            <span
              class="inline-flex items-center gap-2 rounded-full bg-rose-50/95 px-3 py-1 font-semibold text-rose-900 shadow-sm ring-1 ring-rose-100/80"
            >
              <span class="text-sm">💰</span>
              Ingresos por ventas entregadas
            </span>
          </div>
        </div>

        <!-- DERECHA -->
        <div class="flex flex-col items-end gap-3">
          <!-- volver -->
          <Link
            :href="route('admin.dashboard')"
            class="inline-flex items-center gap-2 rounded-2xl bg-black/20 px-3 py-2 text-xs font-medium text-amber-50 hover:bg-black/30"
          >
            ← Volver al panel
          </Link>

          <!-- tip -->
          <div
            class="max-w-xs rounded-2xl bg-white/90 px-4 py-3 text-[11px] text-slate-800 shadow-md ring-1 ring-white/70"
          >
            <p class="font-semibold text-amber-600">Tip rápido</p>
            <p class="mt-0.5 text-slate-700">
              Usa CSV para análisis en Excel y PDF para informes rápidos o impresión.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>



    <!-- ========== CONTENIDO (tarjetas tipo Apple) ========== -->
    <div class="mx-auto max-w-7xl px-6 py-10 space-y-10">
      <!-- INVENTARIO -->
      <section>
        <div class="mb-3 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-amber-700">
              Reportes de inventario
            </h3>
            <p class="text-xs text-slate-500">
              Descarga existencias, mínimos y movimientos históricos.
            </p>
          </div>

          <Link
            :href="route('admin.inventario.index')"
            class="text-xs font-medium text-amber-700 hover:underline"
          >
            Ir al inventario →
          </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <!-- Inventario completo -->
          <div class="relative group card-float">
            <div class="card-glow card-glow-amber" />
            <div
              class="card-apple"
            >
              <!-- barra de color: YA NO SE SALE por overflow-hidden -->
              <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400" />

              <div class="mt-4 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-sm">
                  🧱
                </div>

                <div class="flex-1">
                  <h4 class="text-sm font-semibold text-slate-900">Inventario completo</h4>
                  <p class="mt-1 text-xs text-slate-600">
                    Exporta todos los productos con stock actual y mínimo configurado.
                  </p>

                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a :href="INVENTARIO_EXPORT_CSV_URL" :class="btn('outline')">CSV</a>
                    <a :href="INVENTARIO_EXPORT_PDF_URL" target="_blank" :class="btn('solid')">PDF</a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Historial de movimientos -->
          <div class="relative group card-float">
            <div class="card-glow card-glow-sky" />
            <div class="card-apple">
              <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-400 to-indigo-500" />

              <div class="mt-4 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 shadow-sm">
                  📑
                </div>

                <div class="flex-1">
                  <h4 class="text-sm font-semibold text-slate-900">Historial de movimientos</h4>
                  <p class="mt-1 text-xs text-slate-600">
                    Exporta entradas, salidas y ajustes por producto.
                  </p>

                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a :href="INVENTARIO_HISTORIAL_CSV_URL" :class="btn('outline')">CSV</a>
                    <Link
                      :href="route('admin.inventario.index')"
                      class="text-xs font-medium text-sky-700 hover:underline"
                    >
                      Ver movimientos →
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CATÁLOGO E INGRESOS -->
      <section>
        <div class="mb-3">
          <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-700">
            Catálogo e ingresos
          </h3>
          <p class="text-xs text-slate-500">
            Reportes de productos y resumen de ingresos por ventas entregadas.
          </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          <!-- Listado de productos -->
          <div class="relative group card-float">
            <div class="card-glow card-glow-emerald" />
            <div class="card-apple">
              <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-600" />

              <div class="mt-4 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 shadow-sm">
                  🛒
                </div>

                <div class="flex-1">
                  <h4 class="text-sm font-semibold text-slate-900">Listado de productos</h4>
                  <p class="mt-1 text-xs text-slate-600">
                    Exporta el catálogo con nombre y precio.
                  </p>

                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a :href="REPORTES_PRODUCTOS_CSV_URL" :class="btn('outline')">CSV</a>
                    <a :href="REPORTES_PRODUCTOS_PDF_URL" target="_blank" :class="btn('solid')">PDF</a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ingresos -->
          <div class="relative group card-float">
            <div class="card-glow card-glow-amber" />
            <div class="card-apple">
              <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400" />

              <div class="mt-4 flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-sm">
                  💰
                </div>

                <div class="flex-1">
                  <h4 class="text-sm font-semibold text-slate-900">Ingresos (ventas entregadas)</h4>
                  <p class="mt-1 text-xs text-slate-600">
                    Consulta ingresos diarios o por rango de fechas.
                  </p>

                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <a :href="REPORTES_INGRESOS_URL" :class="btn('solid')">Ver reporte</a>
                    <a :href="REPORTES_INGRESOS_CSV_URL" :class="btn('outline')">CSV</a>
                    <a :href="REPORTES_INGRESOS_PDF_URL" target="_blank" :class="btn('outline')">PDF</a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Espacio para balance visual en desktop -->
          <div class="hidden lg:block" />
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* ===== Tarjeta tipo Apple (glass + sombra suave) ===== */
.card-apple {
  position: relative;
  overflow: hidden; /* importante: para que la barra de color NO se salga */
  border-radius: 1.5rem; /* 24px */
  padding: 1.4rem 1.5rem 1.6rem;
  background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.9), #ffffff)
              border-box;
  border: 1px solid rgba(148, 163, 184, 0.25);
  box-shadow:
    0 18px 45px rgba(15, 23, 42, 0.08),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
  backdrop-filter: blur(10px);
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}

.group:hover .card-apple {
  transform: translateY(-4px);
  box-shadow:
    0 22px 55px rgba(15, 23, 42, 0.15),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
  border-color: rgba(129, 140, 248, 0.35);
}

/* ===== Animación flotante suave ===== */
@keyframes float-card {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
.card-float {
  animation: float-card 9s ease-in-out infinite;
}

/* ===== Glow MUY suave alrededor ===== */
.card-glow {
  position: absolute;
  inset: -10px;
  border-radius: 1.7rem;
  filter: blur(26px);
  opacity: 0;
  transition: opacity 0.25s ease-out;
  pointer-events: none;
}

.card-glow-amber {
  background:
    radial-gradient(circle at top left, rgba(251, 191, 36, 0.22), transparent 60%),
    radial-gradient(circle at bottom right, rgba(248, 113, 113, 0.16), transparent 60%);
}

.card-glow-sky {
  background:
    radial-gradient(circle at top left, rgba(56, 189, 248, 0.22), transparent 60%),
    radial-gradient(circle at bottom right, rgba(129, 140, 248, 0.16), transparent 60%);
}

.card-glow-emerald {
  background:
    radial-gradient(circle at top left, rgba(16, 185, 129, 0.24), transparent 60%),
    radial-gradient(circle at bottom right, rgba(34, 197, 94, 0.16), transparent 60%);
}

.group:hover .card-glow {
  opacity: 1;
}
</style>
