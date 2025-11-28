<!-- resources/js/Pages/Vendedor/Dashboard.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()

const userName = computed(() => page.props?.auth?.user?.name ?? 'Vendedor')

const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

const counts = computed(
  () =>
    page.props?.counts ?? {
      pedidos_hoy: 0,
      pendientes: 0,
      en_camino: 0,
      entregados: 0,
    },
)

function goPedidosEstado(estado) {
  if (!estado) {
    window.location.href = route('vendedor.pedidos.index')
    return
  }
  window.location.href = route('vendedor.pedidos.index', { estado })
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- 🌈 HEADER ESTILO POLLERÍA (igual familia que el admin) -->
    <template #header>
      <div class="bg-gradient-to-b from-[#FFF7E3] via-[#FFFDF8] to-[#FFE7E0] pt-4 pb-6">
        <div
          class="relative mx-auto max-w-7xl overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 px-6 py-6 shadow-lg"
        >
          <!-- blobs decorativos -->
          <div
            class="pointer-events-none absolute -left-16 -top-20 h-40 w-40 rounded-full bg-white/15 blur-3xl"
          />
          <div
            class="pointer-events-none absolute -right-10 bottom-0 h-40 w-40 rounded-full bg-black/15 blur-3xl"
          />

          <div
            class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
          >
            <!-- Izquierda: saludo -->
            <div class="space-y-3">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/15 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
              >
                <span class="text-lg">🛒</span>
                <span>Panel del vendedor — Pollería Pepe</span>
              </div>

              <div>
                <p class="text-sm text-amber-50/90">
                  {{ saludo }},
                  <span class="font-semibold">{{ userName }}</span>
                </p>
                <h1
                  class="mt-1 text-3xl font-extrabold tracking-tight text-white drop-shadow-sm"
                >
                  Gestiona tus pedidos del día en un solo lugar
                </h1>
                <p class="mt-2 max-w-xl text-sm text-amber-50/90">
                  Revisa los pedidos pendientes, en camino y entregados para evitar
                  retrasos con los clientes.
                </p>
              </div>

              <!-- Chips mini resumen -->
              <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-amber-50/95">
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-white/95 px-3 py-1 font-semibold text-amber-900 shadow-sm ring-1 ring-white/70"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                  {{ counts.pedidos_hoy }} pedidos hoy
                </span>
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-amber-300" />
                  {{ counts.pendientes }} pendientes
                </span>
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-sky-300" />
                  {{ counts.en_camino }} en camino
                </span>
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-300" />
                  {{ counts.entregados }} entregados
                </span>
              </div>
            </div>

            <!-- Derecha: accesos rápidos top -->
            <div class="flex flex-col items-end gap-3">
              <Link
                :href="route('vendedor.pedidos.index')"
                class="inline-flex items-center gap-2 rounded-2xl bg-white/95 px-4 py-2 text-xs font-semibold text-amber-800 shadow-md ring-1 ring-white/70 hover:bg-amber-50"
              >
                <span class="grid h-7 w-7 place-items-center rounded-xl bg-amber-100 text-amber-700">
                  📦
                </span>
                Ver pedidos del día
              </Link>

              <Link
                :href="route('catalogo.index')"
                class="inline-flex items-center gap-2 rounded-2xl bg-black/20 px-3 py-1.5 text-xs font-medium text-amber-50 hover:bg-black/30"
              >
                Ver catálogo rápido
                <span>→</span>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🌈 CONTENIDO con fondo suave, mismo lenguaje visual -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/15 to-slate-50">
      <div class="mx-auto max-w-7xl p-6 space-y-10">
        <!-- 📊 KPIs -->
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <article
            class="cursor-pointer rounded-2xl bg-gradient-to-br from-amber-50 via-amber-100/70 to-white p-5 shadow-md ring-1 ring-amber-200/80 transition hover:-translate-y-1 hover:shadow-lg hover:ring-amber-300"
            @click="goPedidosEstado(null)"
          >
            <p class="text-xs font-medium uppercase tracking-wide text-amber-800">
              Pedidos de hoy
            </p>
            <p class="mt-2 text-4xl font-extrabold text-amber-900">
              {{ counts.pedidos_hoy }}
            </p>
            <div class="mt-3 h-2 w-full rounded-full bg-amber-100">
              <div class="h-2 w-full rounded-full bg-amber-500" />
            </div>
          </article>

          <article
            class="cursor-pointer rounded-2xl bg-gradient-to-br from-orange-50 via-orange-100/70 to-white p-5 shadow-md ring-1 ring-orange-200/80 transition hover:-translate-y-1 hover:shadow-lg hover:ring-orange-300"
            @click="goPedidosEstado('pendiente')"
          >
            <p class="text-xs font-medium uppercase tracking-wide text-orange-800">
              Pendientes
            </p>
            <p class="mt-2 text-4xl font-extrabold text-orange-700">
              {{ counts.pendientes }}
            </p>
            <div class="mt-3 h-2 w-full rounded-full bg-orange-100">
              <div class="h-2 rounded-full bg-orange-500" :style="{ width: '100%' }" />
            </div>
          </article>

          <article
            class="cursor-pointer rounded-2xl bg-gradient-to-br from-sky-50 via-sky-100/70 to-white p-5 shadow-md ring-1 ring-sky-200/80 transition hover:-translate-y-1 hover:shadow-lg hover:ring-sky-300"
            @click="goPedidosEstado('en_camino')"
          >
            <p class="text-xs font-medium uppercase tracking-wide text-sky-800">
              En camino
            </p>
            <p class="mt-2 text-4xl font-extrabold text-sky-700">
              {{ counts.en_camino }}
            </p>
            <div class="mt-3 h-2 w-full rounded-full bg-sky-100">
              <div class="h-2 rounded-full bg-sky-500" :style="{ width: '100%' }" />
            </div>
          </article>

          <article
            class="cursor-pointer rounded-2xl bg-gradient-to-br from-emerald-50 via-emerald-100/70 to-white p-5 shadow-md ring-1 ring-emerald-200/80 transition hover:-translate-y-1 hover:shadow-lg hover:ring-emerald-300"
            @click="goPedidosEstado('entregado')"
          >
            <p class="text-xs font-medium uppercase tracking-wide text-emerald-800">
              Entregados
            </p>
            <p class="mt-2 text-4xl font-extrabold text-emerald-700">
              {{ counts.entregados }}
            </p>
            <div class="mt-3 h-2 w-full rounded-full bg-emerald-100">
              <div class="h-2 rounded-full bg-emerald-500" :style="{ width: '100%' }" />
            </div>
          </article>
        </section>

        <!-- 🚀 Accesos rápidos -->
        <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <Link
            :href="route('vendedor.pedidos.index')"
            class="group block rounded-2xl bg-white p-6 shadow-md ring-1 ring-amber-100/80 transition hover:-translate-y-1 hover:shadow-lg hover:ring-amber-200"
          >
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-slate-900">Pedidos</h3>
              <span
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700 group-hover:bg-amber-100"
              >
                📦
              </span>
            </div>
            <p class="mt-2 text-sm text-slate-600">
              Ver y atender pedidos activos.
            </p>
            <span
              class="mt-4 inline-block text-sm font-medium text-amber-700 group-hover:underline"
            >
              Ir a pedidos →
            </span>
          </Link>

          <Link
            :href="route('vendedor.reportes.operativos')"
            class="group block rounded-2xl bg-white p-6 shadow-md ring-1 ring-purple-100/80 transition hover:-translate-y-1 hover:shadow-lg hover:ring-purple-200"
          >
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-slate-900">Reportes operativos</h3>
              <span
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-700 group-hover:bg-purple-100"
              >
                📊
              </span>
            </div>
            <p class="mt-2 text-sm text-slate-600">
              Estadísticas rápidas del día.
            </p>
            <span
              class="mt-4 inline-block text-sm font-medium text-purple-700 group-hover:underline"
            >
              Abrir reportes →
            </span>
          </Link>

          <Link
            :href="route('catalogo.index')"
            class="group block rounded-2xl bg-white p-6 shadow-md ring-1 ring-rose-100/80 transition hover:-translate-y-1 hover:shadow-lg hover:ring-rose-200"
          >
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-slate-900">Catálogo</h3>
              <span
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-700 group-hover:bg-rose-100"
              >
                🛍️
              </span>
            </div>
            <p class="mt-2 text-sm text-slate-600">
              Consulta productos y precios rápidamente.
            </p>
            <span
              class="mt-4 inline-block text-sm font-medium text-rose-700 group-hover:underline"
            >
              Ver catálogo →
            </span>
          </Link>
        </section>

        <!-- 💡 Consejos -->
        <section class="rounded-2xl bg-white p-6 shadow-md ring-1 ring-slate-100/80">
          <h4 class="text-base font-semibold text-slate-900">Consejos rápidos</h4>
          <ul class="mt-3 space-y-1 list-disc pl-5 text-sm text-slate-600">
            <li>
              Desde <strong>Pedidos</strong> puedes cambiar el estado de cada orden.
            </li>
            <li>
              La <strong>cancelación</strong> repone el stock si aplica, para no perder
              control de inventario.
            </li>
            <li>
              Revisa <strong>Pedidos</strong> continuamente para evitar retrasos en las
              entregas.
            </li>
          </ul>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
