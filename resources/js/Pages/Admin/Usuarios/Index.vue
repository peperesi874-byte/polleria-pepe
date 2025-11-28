<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

/** Props desde Inertia */
const page    = usePage()
const users   = computed(() => page.props.users ?? { data: [], links: [], from: 1, total: 0 })
const items   = computed(() => users.value?.data ?? [])
const links   = computed(() => users.value?.links ?? [])
const filters = page.props.filters ?? { q: '' }
const total   = computed(() => users.value?.total ?? items.value.length)

/** Stats rápidos por rol para las tarjetas */
const roleStats = computed(() => {
  const stats = { total: total.value, admin: 0, cajero: 0, repartidor: 0 }
  items.value.forEach(u => {
    const n = (u.role?.nombre || '').toLowerCase()
    if (n.includes('admin')) stats.admin++
    else if (n.includes('cajer')) stats.cajero++
    else if (n.includes('repart')) stats.repartidor++
  })
  return stats
})

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

/** Badge de rol (colores pastel estilo panel) */
function rolePillClass(nombre) {
  const n = (nombre || '').toString().toLowerCase()
  if (n.includes('admin'))   return 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'
  if (n.includes('cajer'))   return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
  if (n.includes('repart'))  return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
  return 'bg-slate-50 text-slate-700 ring-1 ring-slate-200'
}

/** Iniciales (avatar mini) */
function initials(name = '') {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(p => p[0]?.toUpperCase() ?? '')
    .join('')
}
</script>

<template>
  <Head title="Usuarios" />

  <AuthenticatedLayout>
    <!-- ===== HERO NARANJA TIPO PANEL PRINCIPAL ===== -->
    <template #header>
      <div
        class="rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
      >
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs">
              <span class="text-lg">👥</span>
              <span class="font-semibold">Módulo de usuarios — Pollería Pepe</span>
            </div>

            <p class="text-sm opacity-90">
              Controla quién puede acceder al sistema, gestionar pedidos y administrar el inventario.
            </p>

            <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight">
              Gestiona tus usuarios y permisos en un solo lugar
            </h2>

            <div class="flex flex-wrap gap-2 text-xs">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"
              >
                <span>👑</span>
                <span>{{ roleStats.admin }} administradores</span>
              </div>
              <div
                class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"
              >
                <span>💳</span>
                <span>{{ roleStats.cajero }} cajeros</span>
              </div>
              <div
                class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"
              >
                <span>🚚</span>
                <span>{{ roleStats.repartidor }} repartidores</span>
              </div>
              <div
                class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1"
              >
                <span>📊</span>
                <span>{{ roleStats.total }} usuarios en el sistema</span>
              </div>
            </div>
          </div>

          <div
            class="mt-4 w-full max-w-xs rounded-3xl bg-white/95 px-5 py-4 text-xs text-slate-800 shadow-xl md:mt-0"
          >
            <p class="text-[11px] font-semibold text-amber-500 uppercase tracking-wide">
              Acciones rápidas
            </p>
            <p class="mt-1 text-sm font-semibold text-slate-900">
              Mantén tu equipo organizado.
            </p>
            <p class="mt-1 text-[11px] text-slate-500">
              Agrega nuevos usuarios o revisa el listado actual para ajustar permisos.
            </p>

            <div class="mt-4 space-y-2">
              <Link
                v-if="safeRoute('admin.usuarios.create')"
                :href="safeRoute('admin.usuarios.create')"
                class="flex items-center justify-between rounded-2xl bg-amber-400 px-3 py-2 text-[11px] font-semibold text-slate-900 shadow-sm hover:bg-amber-300"
              >
                <span class="flex items-center gap-2">
                  <span class="grid h-6 w-6 place-items-center rounded-full bg-white/80">
                    ＋
                  </span>
                  Nuevo usuario
                </span>
                <span>→</span>
              </Link>

              <Link
                :href="safeRoute('admin.dashboard') ?? '#'"
                class="flex items-center justify-between rounded-2xl bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-700 hover:bg-slate-200"
              >
                <span>Volver al panel</span>
                <span>→</span>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="max-w-7xl mx-auto px-6 py-8 space-y-6">
      <!-- FILA DE TARJETAS PASTEL + BUSCADOR -->
      <div class="flex flex-col gap-4 lg:flex-row lg:items-stretch">
        <!-- Tarjetas -->
        <div class="flex-1 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            class="rounded-3xl border border-yellow-100 bg-gradient-to-b from-yellow-50 to-amber-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-amber-700">USUARIOS</p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-amber-400/90 text-white shadow-md"
              >
                👥
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">
              {{ roleStats.total }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Activos en el sistema.
            </p>
          </div>

          <div
            class="rounded-3xl border border-pink-100 bg-gradient-to-b from-pink-50 to-rose-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-rose-700">ADMINISTRADORES</p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-rose-400/90 text-white shadow-md"
              >
                👑
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">
              {{ roleStats.admin }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Con acceso completo.
            </p>
          </div>

          <div
            class="rounded-3xl border border-sky-100 bg-gradient-to-b from-sky-50 to-blue-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-sky-700">CAJEROS</p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-sky-400/90 text-white shadow-md"
              >
                💳
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">
              {{ roleStats.cajero }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Manejo de ventas y cobros.
            </p>
          </div>

          <div
            class="rounded-3xl border border-emerald-100 bg-gradient-to-b from-emerald-50 to-emerald-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold text-emerald-700">REPARTIDORES</p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-emerald-400/90 text-white shadow-md"
              >
                🚚
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">
              {{ roleStats.repartidor }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Encargados de entregas.
            </p>
          </div>
        </div>

        <!-- Buscador -->
        <div class="lg:w-72">
          <p class="mb-1 text-xs font-semibold text-slate-500 uppercase tracking-wide">
            Buscar usuario
          </p>
          <div class="relative">
            <div
              class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-r from-amber-300/0 via-amber-300/20 to-amber-300/0 blur"
            />
            <div
              class="relative flex items-center gap-2 rounded-2xl border border-amber-100 bg-white px-3 py-2 shadow-sm"
            >
              <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-sm">
                🔍
              </span>
              <input
                v-model="q"
                type="search"
                placeholder="Nombre o correo…"
                class="w-full border-0 bg-transparent text-sm text-slate-800 outline-none focus:ring-0"
              />
              <button
                v-if="q"
                type="button"
                @click="q = ''"
                class="text-[11px] text-slate-400 hover:text-slate-600"
              >
                Limpiar
              </button>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">
              {{ total }} usuario{{ total === 1 ? '' : 's' }} en el sistema.
            </p>
          </div>
        </div>
      </div>

      <!-- CONTENEDOR PASTEL + TABLA CON ENCABEZADO OSCURO -->
      <div
        class="overflow-hidden rounded-[28px] border border-amber-100 bg-gradient-to-b from-amber-50 via-rose-50/40 to-orange-50 shadow-[0_20px_45px_rgba(15,23,42,0.08)]"
      >
        <!-- Encabezado tipo “Últimos pedidos” -->
        <div class="flex items-center justify-between px-5 pt-4 pb-3">
          <div class="flex items-center gap-2">
            <div
              class="grid h-8 w-8 place-items-center rounded-full bg-amber-400 text-white shadow-md"
            >
              👥
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-900">
                Usuarios internos
              </p>
              <p class="text-[11px] text-slate-600">
                Listado de las cuentas más recientes en el sistema.
              </p>
            </div>
          </div>

          <div class="text-right text-[11px] text-slate-500">
            <p class="font-semibold text-slate-800">
              {{ total }} usuario{{ total === 1 ? '' : 's' }}
            </p>
            <p v-if="q" class="text-[10px] text-slate-500">
              Filtro: “{{ q }}”
            </p>
          </div>
        </div>

        <!-- Tabla -->
        <div class="mt-1 overflow-x-auto">
          <table class="w-full border-collapse text-sm">
            <thead>
              <tr class="bg-slate-950 text-xs font-semibold uppercase text-white">
                <th class="px-4 py-3 text-left w-[60px]">#</th>
                <th class="px-4 py-3 text-left">Usuario</th>
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
                class="border-t border-amber-100 bg-white hover:bg-amber-50/70"
              >
                <td class="px-4 py-3 text-[11px] font-medium text-slate-500">
                  {{ (users.from ?? 1) + i }}
                </td>

                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-amber-300 to-pink-400 text-[0.7rem] font-bold text-slate-900 shadow-md"
                    >
                      {{ initials(u.name) }}
                    </div>
                    <div class="flex flex-col">
                      <span class="font-semibold text-slate-900">
                        {{ u.name }}
                      </span>
                      <span class="text-[11px] text-slate-400">
                        ID: {{ u.id }}
                      </span>
                    </div>
                  </div>
                </td>

                <td class="px-4 py-3 text-slate-800">
                  <span
                    class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-1 text-[11px]"
                  >
                    {{ u.email }}
                  </span>
                </td>

                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm"
                    :class="rolePillClass(u.role?.nombre)"
                  >
                    {{ u.role?.nombre ?? '—' }}
                  </span>
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                  {{ new Date(u.created_at).toLocaleDateString() }}
                  <span class="block text-[10px] text-slate-400">
                    {{ new Date(u.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                  </span>
                </td>

                <td class="px-4 py-3 text-right">
                  <div class="inline-flex items-center gap-2">
                    <Link
                      v-if="safeRoute('admin.usuarios.edit', u.id)"
                      :href="safeRoute('admin.usuarios.edit', u.id)"
                      class="inline-flex items-center gap-1 rounded-full bg-slate-900 px-3 py-1.5 text-[11px] font-medium text-amber-200 shadow-sm hover:bg-slate-800"
                    >
                      ✏️ <span>Editar</span>
                    </Link>

                    <button
                      v-if="safeRoute('admin.usuarios.destroy', u.id)"
                      @click="borrar(u.id)"
                      class="inline-flex items-center gap-1 rounded-full bg-rose-500 px-3 py-1.5 text-[11px] font-medium text-white shadow-sm hover:bg-rose-400"
                    >
                      🗑 <span>Eliminar</span>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!items.length">
                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                  No hay usuarios registrados aún.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Paginación estilo chips -->
      <div v-if="links?.length" class="mt-4 flex flex-wrap justify-end gap-2">
        <Link
          v-for="(lnk, i) in links"
          :key="i"
          :href="lnk.url || '#'"
          v-html="lnk.label"
          :class="[
            'min-w-9 select-none rounded-full border px-3 py-1.5 text-center text-xs font-medium transition-all',
            lnk.active
              ? 'border-slate-900 bg-slate-900 text-amber-200 shadow-sm'
              : 'border-slate-300 bg-white text-slate-700 hover:border-slate-900 hover:bg-amber-50',
            !lnk.url && 'pointer-events-none opacity-40',
          ]"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
