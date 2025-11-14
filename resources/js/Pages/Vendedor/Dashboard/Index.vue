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

/**
 * Atajo: ir al listado de pedidos filtrando por estado.
 * Si estado es null/undefined, muestra todos.
 */
function goPedidosEstado (estado) {
  if (!estado) {
    window.location.href = route('vendedor.pedidos.index')
    return
  }
  window.location.href = route('vendedor.pedidos.index', { estado })
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- Header -->
    <template #header>
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          {{ saludo }}, {{ userName }} 👋
        </h1>
        <p class="mt-1 text-sm text-gray-500">Panel del vendedor</p>
      </div>
    </template>

    <div class="mx-auto max-w-7xl p-6 space-y-8">
      <!-- KPIs (ahora clickeables) -->
      <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Pedidos de hoy: todos -->
        <article
          class="rounded-2xl border bg-white p-5 cursor-pointer hover:shadow-md transition"
          @click="goPedidosEstado(null)"
        >
          <p class="text-sm text-gray-500">Pedidos de hoy</p>
          <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ counts.pedidos_hoy }}
          </p>
        </article>

        <!-- Pendientes -->
        <article
          class="rounded-2xl border bg-white p-5 cursor-pointer hover:shadow-md transition"
          @click="goPedidosEstado('pendiente')"
        >
          <p class="text-sm text-gray-500">Pendientes</p>
          <p class="mt-2 text-3xl font-bold text-amber-600">
            {{ counts.pendientes }}
          </p>
        </article>

        <!-- En camino -->
        <article
          class="rounded-2xl border bg-white p-5 cursor-pointer hover:shadow-md transition"
          @click="goPedidosEstado('en_camino')"
        >
          <p class="text-sm text-gray-500">En camino</p>
          <p class="mt-2 text-3xl font-bold text-indigo-600">
            {{ counts.en_camino }}
          </p>
        </article>

        <!-- Entregados -->
        <article
          class="rounded-2xl border bg-white p-5 cursor-pointer hover:shadow-md transition"
          @click="goPedidosEstado('entregado')"
        >
          <p class="text-sm text-gray-500">Entregados</p>
          <p class="mt-2 text-3xl font-bold text-emerald-600">
            {{ counts.entregados }}
          </p>
        </article>
      </section>

      <!-- Accesos rápidos -->
      <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <Link
          :href="route('vendedor.pedidos.index')"
          class="group block rounded-2xl border bg-white p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Pedidos</h3>
            <span
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100"
            >
              📦
            </span>
          </div>
          <p class="mt-2 text-sm text-gray-600">
            Ver y atender pedidos (cambiar estado, asignar, cancelar).
          </p>
          <span class="mt-4 inline-block text-sm font-medium text-indigo-700">
            Ir a pedidos →
          </span>
        </Link>

        <Link
          :href="route('vendedor.reportes.operativos')"
          class="group block rounded-2xl border bg-white p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Reportes operativos</h3>
            <span
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100"
            >
              📈
            </span>
          </div>
          <p class="mt-2 text-sm text-gray-600">
            Consultas rápidas del día para seguimiento operativo.
          </p>
          <span class="mt-4 inline-block text-sm font-medium text-indigo-700">
            Abrir reportes →
          </span>
        </Link>

        <Link
          :href="route('catalogo.index')"
          class="group block rounded-2xl border bg-white p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Catálogo</h3>
            <span
              class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100"
            >
              🛒
            </span>
          </div>
          <p class="mt-2 text-sm text-gray-600">
            Consulta rápida del catálogo para confirmar precios y existencias.
          </p>
          <span class="mt-4 inline-block text-sm font-medium text-indigo-700">
            Ir al catálogo →
          </span>
        </Link>
      </section>

      <!-- Consejos -->
      <section class="rounded-2xl border bg-white p-6">
        <h4 class="text-base font-semibold text-gray-900">Consejos rápidos</h4>
        <ul class="mt-3 list-disc pl-5 text-sm text-gray-600 space-y-1">
          <li>
            Desde <strong>Pedidos</strong> puedes cambiar estado.
          </li>
          <li>
            La <strong>cancelación</strong> repone el stock si aplica.
          </li>
          <li>
            Usa <strong>Volver a la lista</strong> para navegar sin errores.
          </li>
        </ul>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
