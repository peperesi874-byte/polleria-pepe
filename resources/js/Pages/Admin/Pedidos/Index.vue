<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { route } from 'ziggy-js' // 👈 Asegura Ziggy por import nombrado

const props = defineProps({
  role: { type: String, default: 'admin' }, // 👈 viene del controlador (admin|vendedor)
  pedidos: Object,        // paginator
  filters: Object,        // { q, estado, asignado }
  estados: Array,
})

/* --------- Prefijo por rol (admin. | vendedor.) --------- */
const prefix = computed(() => (props.role === 'vendedor' ? 'vendedor.' : 'admin.'))

/* ---------------- Filtros con debounce ---------------- */
const q        = ref(props.filters?.q ?? '')
const estado   = ref(props.filters?.estado ?? '')
const asignado = ref(props.filters?.asignado ?? '') // '', 'any', 'none'

let t = null
watch([q, estado, asignado], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route(prefix.value + 'pedidos.index'), // 👈 usa el grupo correcto
      { q: q.value, estado: estado.value, asignado: asignado.value },
      { preserveState: true, replace: true }
    )
  }, 300)
})

/* ---------------- Helpers UI ---------------- */
const money = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

const estadoPill = (e) => {
  if (e === 'cancelado') return 'bg-rose-100 text-rose-700 ring-1 ring-rose-200'
  if (e === 'entregado') return 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200'
  return 'bg-amber-100 text-amber-700 ring-1 ring-amber-200'
}

const asignadoPill = (has) =>
  has ? 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200'
      : 'bg-gray-100 text-gray-600 ring-1 ring-gray-200'

const btn = (variant = 'solid') =>
  [
    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-300',
    variant === 'solid'
      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
      : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
  ].join(' ')
</script>

<template>
  <AuthenticatedLayout>
    <!-- ===== HEADER UNIFICADO (tipo Reportes/Inventario) ===== -->
    <template #header>
      <!-- Wrapper bonito -->
      <div
        class="rounded-2xl border border-gray-200 bg-gradient-to-r from-indigo-50 to-white px-6 py-4 shadow-sm flex items-center justify-between"
      >
        <!-- IZQUIERDA -->
        <div class="flex items-center gap-4">
          <!-- Icono redondo -->
          <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
            <!-- icono de pedidos -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 3h18v2H3zm2 4h14l-1.5 14h-11z"/>
            </svg>
          </div>

          <!-- Títulos -->
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">Pedidos</h2>
            <p class="text-sm text-gray-500">
              Gestión y seguimiento de pedidos del sistema.
            </p>
          </div>
        </div>

        <!-- DERECHA -->
        <div class="flex items-center gap-2">
          <Link
            :href="route(prefix + 'dashboard')"  <!-- 👈 vuelve al dashboard correcto -->
            class="text-sm text-indigo-600 hover:underline"
          >
            ← Volver al panel
          </Link>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Filtros -->
      <div class="mb-6 flex flex-wrap items-center gap-3">
        <div class="relative w-full md:w-80">
          <input
            v-model="q"
            type="text"
            placeholder="Buscar por folio u observaciones…"
            class="w-full rounded-xl border border-gray-300/80 bg-white px-4 py-2.5 pr-10 text-sm shadow-sm outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">🔍</span>
        </div>

        <select
          v-model="estado"
          class="rounded-xl border border-gray-300/80 bg-white px-3 py-2.5 text-sm shadow-sm outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
        >
          <option value="">Todos los estados</option>
          <option v-for="e in estados" :key="e" :value="e">
            {{ e.replace('_',' ') }}
          </option>
        </select>

        <select
          v-model="asignado"
          class="rounded-xl border border-gray-300/80 bg-white px-3 py-2.5 text-sm shadow-sm outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
        >
          <option value="">Asignados: todos</option>
          <option value="any">Solo asignados</option>
          <option value="none">Solo sin asignar</option>
        </select>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-sm">
          <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left w-[64px]">#</th>
              <th class="px-4 py-3 text-left">Folio</th>
              <th class="px-4 py-3 text-left">Estado</th>
              <th class="px-4 py-3 text-left">Tipo</th>
              <th class="px-4 py-3 text-left">Items</th>
              <th class="px-4 py-3 text-left">Asignado a</th>
              <th class="px-4 py-3 text-left">Total</th>
              <th class="px-4 py-3 text-left">Creado</th>
              <th class="px-4 py-3 text-right w-[160px]">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(p, i) in props.pedidos.data"
              :key="p.id"
              class="border-t border-gray-100 odd:bg-white even:bg-gray-50/30 hover:bg-gray-50/80"
            >
              <td class="px-4 py-3 text-gray-600">{{ (props.pedidos.from ?? 1) + i }}</td>
              <td class="px-4 py-3 font-medium text-gray-900">{{ p.folio ?? '—' }}</td>

              <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="estadoPill(p.estado)">
                  {{ p.estado.replace('_',' ') }}
                </span>
              </td>

              <td class="px-4 py-3 capitalize text-gray-700">{{ p.tipo.replace('_',' ') }}</td>
              <td class="px-4 py-3">{{ p.items }}</td>

              <td class="px-4 py-3">
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1" :class="asignadoPill(!!p.repartidor)">
                  {{ p.repartidor ?? '—' }}
                </span>
              </td>

              <td class="px-4 py-3 font-medium text-gray-800">{{ money(p.total) }}</td>
              <td class="px-4 py-3 text-gray-500">{{ p.created_at }}</td>

              <td class="px-4 py-3 text-right">
                <Link
                  :href="route(prefix + 'pedidos.show', p.id)"  <!-- 👈 detalle con prefijo por rol -->
                  class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 px-3 py-1.5 text-indigo-700 hover:bg-indigo-50"
                >
                  Ver detalle →
                </Link>
              </td>
            </tr>

            <tr v-if="props.pedidos.data.length === 0">
              <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                No hay pedidos con esos filtros.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex justify-end gap-2">
        <Link
          v-for="(lnk, i) in props.pedidos.links"
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
