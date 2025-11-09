<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  pedido: { type: Object, required: true },          // { id, folio, estado, tipo, total, asignado_a, items[], created_at, observaciones, logs[] }
  repartidores: { type: Array, default: () => [] }   // [{id,name}]
})

/* ===== Helpers ===== */
const processing   = ref(false)
const money = n => new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n ?? 0)

const estadoActual = computed(() => props.pedido?.estado ?? 'pendiente')
const bloqueado    = computed(() => ['cancelado','entregado'].includes(estadoActual.value))

const estadosOrden = ['pendiente','preparando','listo','en_camino','entregado']
const estadoLabel  = e => (e || '').replace('_',' ')
const stepIndex    = computed(() => Math.max(0, estadosOrden.indexOf(estadoActual.value)))

const pillEstado = (e) => {
  const m = {
    cancelado : 'bg-rose-100 text-rose-700',
    entregado : 'bg-green-100 text-green-700',
    en_camino : 'bg-indigo-100 text-indigo-700',
    listo     : 'bg-blue-100 text-blue-700',
    preparando: 'bg-amber-100 text-amber-700',
    pendiente : 'bg-gray-100 text-gray-700',
  }
  return m[e] || 'bg-gray-100 text-gray-700'
}

/* ===== Acciones ===== */
function setEstado(estado) {
  if (bloqueado.value) return alert('El pedido ya no permite cambios.')
  if (estado === estadoActual.value) return
  processing.value = true
  router.put(
    route('admin.pedidos.estado', props.pedido.id),
    { estado },
    { preserveScroll: true, preserveState: true, onFinish: () => (processing.value = false) }
  )
}

function doCancelar() {
  if (estadoActual.value === 'entregado') return alert('Un pedido entregado no se puede cancelar.')
  const motivo = prompt('Motivo de cancelación:')
  if (!motivo) return
  processing.value = true
  router.put(
    route('admin.pedidos.cancelar', props.pedido.id),
    { motivo },
    { preserveScroll: true, preserveState: true, onFinish: () => (processing.value = false) }
  )
}

function asignar(val) {
  if (estadoActual.value === 'cancelado') return alert('No se puede asignar un pedido cancelado.')
  const repartidor_id = val ? Number(val) : null
  processing.value = true
  router.put(
    route('admin.pedidos.asignar', props.pedido.id),
    { repartidor_id },
    { preserveScroll: true, preserveState: true, onFinish: () => (processing.value = false) }
  )
}

/* ===== Helpers visuales Bitácora ===== */
const actionBadge = (a = '') => {
  a = a.toLowerCase()
  if (a === 'estado_cambiado') return 'bg-amber-100 text-amber-800'
  if (['asignado','desasignado'].includes(a)) return 'bg-blue-100 text-blue-800'
  if (a === 'cancelado') return 'bg-rose-100 text-rose-800'
  return 'bg-gray-100 text-gray-700'
}

const actionIcon = (a = '') => {
  a = a.toLowerCase()
  if (a === 'estado_cambiado') return '🪄'
  if (a === 'asignado' || a === 'desasignado') return '👤'
  if (a === 'cancelado') return '⛔'
  return '📝'
}

const actionLabel = (a = '') => a.replaceAll('_',' ').toUpperCase()
</script>

<template>
  <AuthenticatedLayout>

    <!-- ===== Header Unificado ===== -->
    <template #header>
      <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60 border">
        <div class="flex items-center gap-3">
          <div class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-100 text-indigo-700 text-lg">📦</div>

          <div>
            <h2 class="text-2xl font-bold text-gray-900">
              Pedido #{{ pedido.id }}
              <span v-if="pedido.folio" class="ml-1 text-base text-gray-500">({{ pedido.folio }})</span>
            </h2>
            <p class="text-sm text-gray-500">Creado: {{ pedido.created_at }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('admin.pedidos.index')"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
          >
            ← Volver a la lista
          </Link>

          <button
            class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-50"
            :disabled="processing || estadoActual==='entregado'"
            @click="doCancelar"
          >
            Cancelar pedido
          </button>
        </div>
      </div>
    </template>

    <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">

      <!-- ===== Stepper ===== -->
      <div class="rounded-2xl border bg-white p-4 shadow-sm">
        <div class="mb-3">
          <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="pillEstado(estadoActual)">
            {{ estadoLabel(estadoActual) }}
          </span>
        </div>

        <div class="relative">
          <div class="h-2 w-full rounded-full bg-gray-100"></div>

          <div
            class="absolute inset-y-0 left-0 h-2 rounded-full bg-indigo-500 transition-all"
            :style="{ width: ((stepIndex + 1) / estadosOrden.length) * 100 + '%' }"
          />

          <div class="mt-3 grid grid-cols-5 text-[11px] text-gray-600">
            <span v-for="e in estadosOrden" :key="e" class="text-center capitalize">{{ e.replace('_',' ') }}</span>
          </div>
        </div>
      </div>

      <!-- ===== Acciones ===== -->
      <div class="grid gap-4 md:grid-cols-2">
        <!-- Estado -->
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <p class="mb-3 text-sm text-gray-500">Cambiar estado</p>

          <div class="flex flex-wrap gap-2">
            <button class="rounded-md bg-amber-600 px-3 py-1.5 text-white hover:bg-amber-700 disabled:opacity-50"
              :disabled="processing || bloqueado || estadoActual==='preparando'"
              @click="setEstado('preparando')">Preparando</button>

            <button class="rounded-md bg-blue-600 px-3 py-1.5 text-white hover:bg-blue-700 disabled:opacity-50"
              :disabled="processing || bloqueado || estadoActual==='listo'"
              @click="setEstado('listo')">Listo</button>

            <button class="rounded-md bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700 disabled:opacity-50"
              :disabled="processing || bloqueado || estadoActual==='en_camino'"
              @click="setEstado('en_camino')">En camino</button>

            <button class="rounded-md bg-green-600 px-3 py-1.5 text-white hover:bg-green-700 disabled:opacity-50"
              :disabled="processing || estadoActual==='entregado'"
              @click="setEstado('entregado')">Entregado</button>
          </div>
        </div>

        <!-- Repartidor -->
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <p class="mb-3 text-sm text-gray-500">Asignación</p>

          <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
              <select
                class="w-56 appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                :disabled="processing || estadoActual==='cancelado'"
                :value="pedido.asignado_a || ''"
                @change="asignar($event.target.value)"
              >
                <option value="">— Sin asignar —</option>
                <option v-for="r in repartidores" :key="r.id" :value="r.id">
                  {{ r.name }}
                </option>
              </select>

              <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500"
                   xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </div>

            <p v-if="repartidores.length === 0" class="text-sm text-gray-500">
              No hay repartidores registrados.
            </p>
          </div>
        </div>
      </div>

      <!-- ===== Resumen ===== -->
      <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <p class="text-sm text-gray-500">Estado</p>
          <p class="text-lg font-semibold capitalize">{{ pedido.estado.replace('_',' ') }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <p class="text-sm text-gray-500">Tipo de entrega</p>
          <p class="text-lg font-semibold capitalize">{{ pedido.tipo.replace('_',' ') }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <p class="text-sm text-gray-500">Total</p>
          <p class="text-lg font-semibold">{{ money(pedido.total) }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-4 shadow-sm">
          <p class="text-sm text-gray-500">Asignado a</p>
          <p class="text-lg font-semibold">
            {{ (repartidores.find(r => Number(r.id) === Number(pedido.asignado_a))?.name) || '—' }}
          </p>
        </div>
      </div>

      <!-- ===== Items ===== -->
      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Producto</th>
              <th class="px-4 py-3 text-left">Cantidad</th>
              <th class="px-4 py-3 text-left">Precio</th>
              <th class="px-4 py-3 text-left">Subtotal</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="it in pedido.items" :key="it.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-3">{{ it.producto }}</td>
              <td class="px-4 py-3">{{ it.cantidad }}</td>
              <td class="px-4 py-3">{{ money(it.precio) }}</td>
              <td class="px-4 py-3 font-semibold">{{ money(it.subtotal) }}</td>
            </tr>

            <tr v-if="!pedido.items?.length">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                Este pedido no tiene items.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ===== Observaciones ===== -->
      <div v-if="pedido.observaciones" class="rounded-2xl border bg-white p-5 shadow-sm">
        <p class="mb-1 text-sm text-gray-500">Observaciones</p>
        <p class="text-gray-800">{{ pedido.observaciones }}</p>
      </div>

      <!-- ===== Bitácora (Timeline mejorado) ===== -->
      <div class="rounded-2xl border bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-lg font-semibold text-gray-900">Bitácora</h3>

        <ul class="space-y-6">
          <li
            v-for="log in pedido.logs"
            :key="log.id"
            class="flex items-start gap-4 border-b pb-4 last:border-0"
          >
            <!-- Icono -->
            <div class="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-lg"
              :class="{
                'border-amber-300': log.accion==='estado_cambiado',
                'border-blue-300': ['asignado','desasignado'].includes(log.accion),
                'border-rose-300': log.accion==='cancelado',
                'border-gray-300': true
              }"
            >
              {{ actionIcon(log.accion) }}
            </div>

            <!-- Contenido -->
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">{{ log.fecha }}</span>
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold" :class="actionBadge(log.accion)">
                  {{ actionLabel(log.accion) }}
                </span>
              </div>

              <p class="mt-1 text-sm text-gray-800">
                <span class="text-gray-500">por</span>
                <span class="font-medium text-gray-900">{{ log.by }}</span>

                <!-- Estado cambiado -->
                <template v-if="log.accion==='estado_cambiado'">
                  — estado:
                  <span class="italic">{{ log.de || '—' }}</span>
                  →
                  <span class="italic">{{ log.a || '—' }}</span>
                </template>

                <!-- Asignaciones -->
                <template v-if="['asignado','desasignado'].includes(log.accion)">
                  — repartidor:
                  <span class="italic">{{ log.de || '—' }}</span>
                  →
                  <span class="italic">{{ log.a || '—' }}</span>
                </template>

                <!-- Motivo -->
                <template v-if="log.motivo">
                  — motivo:
                  <span class="italic">{{ log.motivo }}</span>
                </template>
              </p>
            </div>
          </li>

          <li v-if="!pedido.logs?.length" class="text-sm text-gray-500">
            Aún no hay eventos registrados para este pedido.
          </li>
        </ul>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
