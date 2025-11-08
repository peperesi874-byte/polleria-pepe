<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  notificaciones: Object, // paginator
  filters: Object
})

const q = ref(props.filters?.q ?? '')

const buscar = () => {
  router.get(route('admin.notificaciones.index'), { q: q.value }, { preserveState: true, replace: true })
}

const items = computed(() => props.notificaciones?.data ?? [])
</script>

<template>
  <Head title="Notificaciones" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Notificaciones</h2>
          <p class="text-sm text-gray-500">Historial de avisos del sistema.</p>
        </div>

        <div class="flex gap-2">
          <input
            v-model="q"
            @keyup.enter="buscar"
            type="text"
            placeholder="Buscar por título o mensaje…"
            class="w-72 rounded-xl border px-3 py-2 text-sm"
          />
          <button @click="buscar" class="rounded-xl bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
            Buscar
          </button>
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-7xl p-6 bg-white rounded-2xl border shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
              <th class="px-3 py-2 text-left w-24">Fecha</th>
              <th class="px-3 py-2 text-left w-72">Título</th>
              <th class="px-3 py-2 text-left">Mensaje</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="n in items" :key="n.id" class="border-b last:border-0">
              <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ n.fecha }}</td>
              <td class="px-3 py-2 font-medium text-gray-900">{{ n.titulo }}</td>
              <td class="px-3 py-2 text-gray-700">{{ n.mensaje }}</td>
            </tr>

            <tr v-if="items.length === 0">
              <td colspan="3" class="px-3 py-8 text-center text-gray-500">No hay notificaciones.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- paginación -->
      <div class="mt-4 flex justify-end gap-2" v-if="notificaciones?.links?.length">
        <Link
          v-for="(l,i) in notificaciones.links"
          :key="i"
          :href="l.url || ''"
          v-html="l.label"
          :class="[
            'px-3 py-1 rounded-lg border text-sm',
            l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white hover:bg-gray-50'
          ]"
          preserve-state
          preserve-scroll
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
