<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

/** Props desde Inertia */
const page    = usePage()
const users   = computed(() => page.props.users ?? { data: [], links: [], from: 1 })
const items   = computed(() => users.value?.data ?? [])
const links   = computed(() => users.value?.links ?? [])
const filters = page.props.filters ?? { q: '' }

/** Ziggy seguro */
function safeRoute(name, params = {}, absolute = true) {
  try {
    if (typeof route !== 'undefined' && route().has(name)) {
      return route(name, params, absolute)
    }
  } catch {}
  return null
}

/** Buscador con debounce */
const q = ref(filters.q ?? '')
let t = null
watch(q, (val) => {
  clearTimeout(t)
  t = setTimeout(() => {
    const url = safeRoute('admin.usuarios.index', { q: val })
    if (url) router.get(url, {}, { preserveState: true, replace: true })
  }, 350)
})

/** Acciones */
function borrar(id) {
  const url = safeRoute('admin.usuarios.destroy', id)
  if (!url) return
  if (!confirm('¿Eliminar este usuario?')) return
  router.delete(url, { preserveScroll: true })
}

/** Badge de rol (colores suaves) */
function rolePillClass(nombre) {
  const n = (nombre || '').toString().toLowerCase()
  if (n.includes('admin'))   return 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'
  if (n.includes('cajero'))  return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
  if (n.includes('repart'))  return 'bg-sky-50 text-sky-700 ring-1 ring-sky-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}
</script>

<template>
  <Head title="Usuarios" />

  <AuthenticatedLayout>
    <!-- ===== HEADER TIPO “CONFIGURACIÓN” ===== -->
    <template #header>
      <div
        class="flex items-center justify-between rounded-3xl bg-gradient-to-r from-indigo-50 to-white px-5 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <div class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-100 text-indigo-700">
            <!-- icono -->
            <span class="text-xl">👥</span>
          </div>
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Usuarios</h2>
            <p class="text-xs text-gray-500">Gestiona cuentas y permisos.</p>
          </div>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <Link
            :href="safeRoute('admin.dashboard') ?? '#'"
            class="text-sm text-indigo-600 hover:underline"
          >
            ← Volver al panel
          </Link>

          <Link
            v-if="safeRoute('admin.usuarios.create')"
            :href="safeRoute('admin.usuarios.create')"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-white shadow-sm transition hover:bg-indigo-700"
          >
            <span class="-ml-1 text-lg">＋</span>
            Nuevo usuario
          </Link>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Buscador -->
      <div class="mb-6">
        <div class="relative w-full md:w-[28rem]">
          <input
            v-model="q"
            type="search"
            placeholder="Buscar por nombre o correo…"
            class="w-full rounded-xl border border-gray-300/80 bg-white px-4 py-2.5 pr-10 text-sm shadow-sm outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">🔍</span>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-sm">
          <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left w-[72px]">ID</th>
              <th class="px-4 py-3 text-left">Nombre</th>
              <th class="px-4 py-3 text-left">Email</th>
              <th class="px-4 py-3 text-left">Rol</th>
              <th class="px-4 py-3 text-left">Creado</th>
              <th class="px-4 py-3 text-right w-[180px]">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(u, i) in items"
              :key="u.id"
              class="border-t border-gray-100 odd:bg-white even:bg-gray-50/30 hover:bg-gray-50/80"
            >
              <td class="px-4 py-3 text-gray-600">
                {{ (users.from ?? 1) + i }}
              </td>

              <td class="px-4 py-3 font-medium text-gray-900">{{ u.name }}</td>
              <td class="px-4 py-3 text-gray-700">{{ u.email }}</td>

              <td class="px-4 py-3">
                <span
                  class="rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="rolePillClass(u.role?.nombre)"
                >
                  {{ u.role?.nombre ?? '—' }}
                </span>
              </td>

              <td class="px-4 py-3 text-gray-500">
                {{ new Date(u.created_at).toLocaleString() }}
              </td>

              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <Link
                    v-if="safeRoute('admin.usuarios.edit', u.id)"
                    :href="safeRoute('admin.usuarios.edit', u.id)"
                    class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 px-3 py-1.5 text-indigo-700 hover:bg-indigo-50"
                  >
                    ✏️ <span class="text-sm font-medium">Editar</span>
                  </Link>

                  <button
                    v-if="safeRoute('admin.usuarios.destroy', u.id)"
                    @click="borrar(u.id)"
                    class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-rose-700 hover:bg-rose-50"
                  >
                    🗑 <span class="text-sm font-medium">Eliminar</span>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!items.length">
              <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                No hay usuarios.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="links?.length" class="mt-6 flex justify-end gap-2">
        <Link
          v-for="(lnk, i) in links"
          :key="i"
          :href="lnk.url || '#'"
          v-html="lnk.label"
          :class="[
            'min-w-9 select-none rounded-lg border px-3 py-1.5 text-center text-sm transition',
            lnk.active
              ? 'border-indigo-600 bg-indigo-600 text-white'
              : 'border-gray-300 bg-white text-gray-700 hover:bg-indigo-50',
            !lnk.url && 'pointer-events-none opacity-40',
          ]"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
