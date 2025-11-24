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

const counts = computed(() => page.props?.counts ?? {
  pedidos_hoy: 0,
  pendientes: 0,
  en_camino: 0,
  entregados: 0,
})

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

    <!-- 🌈 Header con color igual al Admin -->
    <template #header>
      <div class="rounded-2xl bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 px-6 py-5 ring-1 ring-indigo-100 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="h-12 w-12 rounded-xl bg-indigo-100 text-indigo-700 grid place-items-center text-xl shadow-inner">
            🛒
          </div>

          <div>
            <p class="text-sm text-neutral-500">Panel del vendedor</p>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
              {{ saludo }}, {{ userName }} 👋
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Resumen rápido de tus pedidos y accesos operativos.
            </p>
          </div>
        </div>
      </div>
    </template>

    <!-- 🌈 Contenido -->
    <div class="mx-auto max-w-7xl p-6 space-y-10">

      <!-- 📊 KPIs con colores suaves -->
      <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">

        <article
          class="rounded-2xl border bg-white p-6 cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all"
          @click="goPedidosEstado(null)"
        >
          <p class="text-sm text-gray-500">Pedidos de hoy</p>
          <p class="mt-2 text-4xl font-extrabold text-indigo-700">
            {{ counts.pedidos_hoy }}
          </p>
          <div class="mt-3 h-2 w-full rounded-full bg-indigo-100">
            <div class="h-2 rounded-full bg-indigo-500" :style="{ width: '100%' }"></div>
          </div>
        </article>

        <article
          class="rounded-2xl border bg-white p-6 cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all"
          @click="goPedidosEstado('pendiente')"
        >
          <p class="text-sm text-gray-500">Pendientes</p>
          <p class="mt-2 text-4xl font-extrabold text-amber-600">
            {{ counts.pendientes }}
          </p>
          <div class="mt-3 h-2 w-full rounded-full bg-amber-100">
            <div class="h-2 rounded-full bg-amber-500" :style="{ width: '100%' }"></div>
          </div>
        </article>

        <article
          class="rounded-2xl border bg-white p-6 cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all"
          @click="goPedidosEstado('en_camino')"
        >
          <p class="text-sm text-gray-500">En camino</p>
          <p class="mt-2 text-4xl font-extrabold text-indigo-600">
            {{ counts.en_camino }}
          </p>
          <div class="mt-3 h-2 w-full rounded-full bg-indigo-100">
            <div class="h-2 rounded-full bg-indigo-500" :style="{ width: '100%' }"></div>
          </div>
        </article>

        <article
          class="rounded-2xl border bg-white p-6 cursor-pointer hover:shadow-lg hover:-translate-y-1 transition-all"
          @click="goPedidosEstado('entregado')"
        >
          <p class="text-sm text-gray-500">Entregados</p>
          <p class="mt-2 text-4xl font-extrabold text-emerald-600">
            {{ counts.entregados }}
          </p>
          <div class="mt-3 h-2 w-full rounded-full bg-emerald-100">
            <div class="h-2 rounded-full bg-emerald-500" :style="{ width: '100%' }"></div>
          </div>
        </article>

      </section>

      <!-- 🚀 Accesos rápidos con color y diseño profesional -->
      <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">

        <Link
          :href="route('vendedor.pedidos.index')"
          class="group block rounded-2xl border bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Pedidos</h3>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100">
              📦
            </span>
          </div>
          <p class="mt-2 text-sm text-gray-600">
            Ver y atender pedidos activos.
          </p>
          <span class="mt-4 inline-block text-sm font-medium text-indigo-700 group-hover:underline">
            Ir a pedidos →
          </span>
        </Link>

        <Link
          :href="route('vendedor.reportes.operativos')"
          class="group block rounded-2xl border bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Reportes operativos</h3>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-700 group-hover:bg-purple-100">
              📊
            </span>
          </div>
          <p class="mt-2 text-sm text-gray-600">
            Estadísticas rápidas del día.
          </p>
          <span class="mt-4 inline-block text-sm font-medium text-purple-700 group-hover:underline">
            Abrir reportes →
          </span>
        </Link>

        <Link
          :href="route('catalogo.index')"
          class="group block rounded-2xl border bg-white p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Catálogo</h3>
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-pink-50 text-pink-700 group-hover:bg-pink-100">
              🛍️
            </span>
          </div>
          <p class="mt-2 text-sm text-gray-600">
            Consulta productos y precios rápidamente.
          </p>
          <span class="mt-4 inline-block text-sm font-medium text-pink-700 group-hover:underline">
            Ver catálogo →
          </span>
        </Link>

      </section>

      <!-- 💡 Consejos -->
      <section class="rounded-2xl border bg-white p-6 shadow-sm">
        <h4 class="text-base font-semibold text-gray-900">Consejos rápidos</h4>
        <ul class="mt-3 list-disc pl-5 text-sm text-gray-600 space-y-1">
          <li>Desde <strong>Pedidos</strong> puedes cambiar estado.</li>
          <li>La <strong>cancelación</strong> repone el stock si aplica.</li>
          <li>Revisa <strong>Pedidos</strong> continuamente para evitar retrasos.</li>
        </ul>
      </section>

    </div>

  </AuthenticatedLayout>
</template>
