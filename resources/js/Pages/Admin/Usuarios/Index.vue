<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const page = usePage()
const users  = computed(() => page.props.users ?? { data: [], links: [] })
const items  = computed(() => users.value?.data ?? [])
const links  = computed(() => users.value?.links ?? [])
const filters = ref({ q: page.props.filters?.q ?? '' })

function buscar() {
  router.get(route('admin.usuarios.index'), { q: filters.value.q }, { preserveState: true })
}

function borrar(id) {
  if (!confirm('¿Eliminar este usuario?')) return
  router.delete(route('admin.usuarios.destroy', id), { preserveScroll: true })
}
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between gap-4 mb-6">
      <h1 class="text-2xl font-bold">Usuarios</h1>
      <Link
        :href="route('admin.usuarios.create')"
        class="inline-flex items-center rounded-lg bg-amber-600 text-white px-4 py-2 font-semibold hover:bg-amber-700"
      >
        Nuevo usuario
      </Link>
    </div>

    <div class="mb-4 flex gap-2">
      <input
        v-model="filters.q"
        @keyup.enter="buscar"
        type="search"
        placeholder="Buscar por nombre o email"
        class="w-full rounded-lg border border-neutral-300 px-3 py-2"
      />
      <button @click="buscar" class="rounded-lg border px-3 py-2">Buscar</button>
    </div>

    <div class="overflow-x-auto bg-white border rounded-xl">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b bg-neutral-50 text-left">
            <th class="px-4 py-3">ID</th>
            <th class="px-4 py-3">Nombre</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Creado</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in items" :key="u.id" class="border-b">
            <td class="px-4 py-3">{{ u.id }}</td>
            <td class="px-4 py-3">{{ u.name }}</td>
            <td class="px-4 py-3">{{ u.email }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs">
                {{ u.role?.nombre ?? '—' }}
              </span>
            </td>
            <td class="px-4 py-3">{{ new Date(u.created_at).toLocaleString() }}</td>
            <td class="px-4 py-3 text-right">
              <Link :href="route('admin.usuarios.edit', u.id)" class="text-amber-600 hover:underline me-3">Editar</Link>
              <button @click="borrar(u.id)" class="text-red-600 hover:underline">Eliminar</button>
            </td>
          </tr>
          <tr v-if="!items.length">
            <td colspan="6" class="px-4 py-8 text-center text-neutral-500">
              No hay usuarios.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div v-if="links?.length" class="mt-6 flex flex-wrap items-center justify-center gap-2">
      <template v-for="(link, i) in links" :key="i">
        <button
          v-if="link.url"
          class="rounded-lg border px-3 py-1.5 text-sm"
          :class="link.active
            ? 'border-amber-500 text-amber-600 font-semibold'
            : 'border-neutral-300 text-neutral-700 hover:border-amber-400 hover:text-amber-600'"
          v-html="link.label"
          @click="router.visit(link.url, { preserveScroll: true })"
        />
        <span v-else class="px-3 py-1.5 text-sm text-neutral-400" v-html="link.label" />
      </template>
    </div>
  </div>
</template>
