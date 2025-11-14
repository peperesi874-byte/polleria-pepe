<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  notificaciones: Object,             // paginator
  estado: { type: String, default: 'todas' },
  unread_count: { type: Number, default: 0 },
})

// ✅ marcar UNA como leída
const leer = id => {
  router.post(
    route('vendedor.notificaciones.leer', id),
    {},
    { preserveScroll: true }
  )
}

// ✅ marcar TODAS como leídas
const leerTodas = () => {
  router.post(
    route('vendedor.notificaciones.leer_todas'),
    {},
    { preserveScroll: true }
  )
}

const fmtFecha = iso => {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleString('es-MX', {
    dateStyle: 'short',
    timeStyle: 'short',
  })
}
</script>

<template>
  <AuthenticatedLayout role="vendedor">
    <Head title="Notificaciones" />

    <!-- HEADER -->
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-gray-900">
          Notificaciones
        </h2>
        <div class="flex flex-wrap gap-2">
          <Link
            :href="route('vendedor.dashboard')"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
          >
            ← Volver al panel
          </Link>

          <button
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
            @click="leerTodas"
          >
            Marcar todas como leídas
          </button>
        </div>
      </div>
    </template>

    <!-- LISTA -->
    <div class="mx-auto max-w-5xl space-y-4">
      <div
        v-for="n in notificaciones.data"
        :key="n.id"
        class="flex items-start justify-between gap-4 rounded-xl border bg-white p-4"
        :class="n.leida ? 'opacity-70' : ''"
      >
        <div>
          <p class="text-sm text-gray-500">
            {{ fmtFecha(n.created_at) }}
          </p>

          <h3 class="mt-1 font-semibold text-gray-900">
            {{ n.titulo || 'Notificación' }}
          </h3>

          <p class="text-gray-700">
            {{ n.mensaje }}
          </p>

          <div class="mt-2 flex flex-wrap gap-3 items-center">
            <!-- Enlace a pedido relacionado si viene en meta -->
            <Link
              v-if="n.meta?.pedido_id"
              :href="route('vendedor.pedidos.show', n.meta.pedido_id)"
              class="text-sm text-indigo-600 hover:underline"
            >
              Ver pedido →
            </Link>

            <button
              v-if="!n.leida"
              class="text-sm text-gray-600 hover:text-gray-900"
              @click="leer(n.id)"
            >
              Marcar leída
            </button>
          </div>
        </div>

        <div class="mt-1">
          <span
            class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold"
            :class="n.leida ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-800'"
          >
            {{ n.leida ? 'Leída' : 'Nueva' }}
          </span>
        </div>
      </div>

      <div v-if="!notificaciones.data?.length" class="text-center text-gray-500">
        No hay notificaciones.
      </div>

      <!-- Paginación -->
      <div
        v-if="notificaciones.total > 0"
        class="flex items-center justify-between pt-2"
      >
        <div class="text-sm text-gray-500">
          Mostrando {{ notificaciones.from }}–{{ notificaciones.to }} de {{ notificaciones.total }}
        </div>
        <div class="flex gap-2">
          <Link
            v-if="notificaciones.prev_page_url"
            :href="notificaciones.prev_page_url"
            class="rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
            preserve-scroll
          >
            Anterior
          </Link>
          <Link
            v-if="notificaciones.next_page_url"
            :href="notificaciones.next_page_url"
            class="rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
            preserve-scroll
          >
            Siguiente
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
