<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  notifs: Object, // paginator
})

const leer = id => {
  router.patch(route('vendedor.notificaciones.read', id), {}, { preserveScroll: true })
}

const leerTodas = () => {
  router.patch(route('vendedor.notificaciones.read_all'), {}, { preserveScroll: true })
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Notificaciones</h2>
        <div class="flex gap-2">
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

    <div class="mx-auto max-w-5xl space-y-4">
      <div
        v-for="n in notifs.data"
        :key="n.id"
        class="rounded-xl border p-4 bg-white flex items-start justify-between gap-4"
        :class="n.read_at ? 'opacity-70' : ''"
      >
        <div>
          <p class="text-sm text-gray-500">{{ n.created }}</p>
          <h3 class="mt-1 font-semibold text-gray-900">{{ n.title }}</h3>
          <p class="text-gray-700">{{ n.message }}</p>

          <div class="mt-2 flex gap-2">
            <a
              v-if="n.url"
              :href="n.url"
              target="_blank"
              rel="noopener"
              class="text-sm text-indigo-600 hover:underline"
            >Ver detalle →</a>

            <button
              v-if="!n.read_at"
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
            :class="n.read_at ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-800'"
          >
            {{ n.read_at ? 'Leída' : 'Nueva' }}
          </span>
        </div>
      </div>

      <div v-if="notifs.data?.length === 0" class="text-center text-gray-500">
        No hay notificaciones.
      </div>

      <div class="flex items-center justify-between pt-2">
        <div class="text-sm text-gray-500">
          Mostrando {{ notifs.from }}–{{ notifs.to }} de {{ notifs.total }}
        </div>
        <div class="flex gap-2">
          <Link
            v-if="notifs.prev_page_url"
            :href="notifs.prev_page_url"
            class="rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
            preserve-scroll
          >Anterior</Link>
          <Link
            v-if="notifs.next_page_url"
            :href="notifs.next_page_url"
            class="rounded-md border px-3 py-1.5 text-sm hover:bg-gray-50"
            preserve-scroll
          >Siguiente</Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
