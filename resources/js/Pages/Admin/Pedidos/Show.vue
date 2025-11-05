<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  pedido: { type: Object, required: true },          // viene con items + datos básicos
  repartidores: { type: Array, default: () => [] }   // [{id,name}]
})

const processing = ref(false)

const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

const estadoActual = computed(() => props.pedido?.estado ?? 'pendiente')
const bloqueado = computed(() =>
  estadoActual.value === 'cancelado' || estadoActual.value === 'entregado'
)

// --- Acciones ---
function setEstado(estado) {
  if (bloqueado.value) return alert('El pedido ya no permite cambios.')
  if (estado === estadoActual.value) return // no hagas nada si es el mismo
  processing.value = true
  router.put(
    route('admin.pedidos.estado', props.pedido.id),
    { estado },
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => (processing.value = false),
    }
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
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => (processing.value = false),
    }
  )
}

function asignar(val) {
  if (estadoActual.value === 'cancelado') return alert('No se puede asignar un pedido cancelado.')
  // normaliza a Number o null
  const repartidorId = val ? Number(val) : null
  processing.value = true
  router.put(
    route('admin.pedidos.asignar', props.pedido.id),
    { repartidor_id: repartidorId },
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => (processing.value = false),
    }
  )
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-6 py-8">
    <div class="mb-6 flex items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-3xl font-semibold text-indigo-800">
          Pedido #{{ pedido.id }}
          <span v-if="pedido.folio" class="text-gray-400">({{ pedido.folio }})</span>
        </h1>
        <p class="text-gray-500">Creado: {{ pedido.created_at }}</p>
      </div>

      <Link :href="route('admin.pedidos.index')" class="text-indigo-600 hover:text-indigo-700 font-medium">
        ← Volver a la lista
      </Link>
    </div>

    <!-- Acciones -->
    <div class="mb-6 grid gap-4 md:grid-cols-2">
      <!-- Cambiar estado -->
      <div class="rounded-xl border bg-white p-4">
        <p class="mb-3 text-sm text-gray-500">Cambiar estado</p>
        <div class="flex flex-wrap gap-2">
          <button
            class="rounded-md bg-amber-600 px-3 py-1.5 text-white hover:bg-amber-700 disabled:opacity-50"
            :disabled="processing || bloqueado || estadoActual==='preparando'"
            @click="setEstado('preparando')"
          >Preparando</button>

          <button
            class="rounded-md bg-blue-600 px-3 py-1.5 text-white hover:bg-blue-700 disabled:opacity-50"
            :disabled="processing || bloqueado || estadoActual==='listo'"
            @click="setEstado('listo')"
          >Listo</button>

          <button
            class="rounded-md bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700 disabled:opacity-50"
            :disabled="processing || bloqueado || estadoActual==='en_camino'"
            @click="setEstado('en_camino')"
          >En camino</button>

          <button
            class="rounded-md bg-green-600 px-3 py-1.5 text-white hover:bg-green-700 disabled:opacity-50"
            :disabled="processing || estadoActual==='entregado'"
            @click="setEstado('entregado')"
          >Entregado</button>
        </div>
      </div>

      <!-- Asignar repartidor + cancelar -->
      <div class="rounded-xl border bg-white p-4">
        <p class="mb-3 text-sm text-gray-500">Asignación y cancelación</p>
        <div class="flex flex-wrap items-center gap-3">
          <div class="relative">
            <select
              class="w-56 rounded-lg border border-gray-300 bg-white px-3 py-2 pr-9 text-sm
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 appearance-none disabled:opacity-50"
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
                    clip-rule="evenodd" />
            </svg>
          </div>

          <p v-if="repartidores.length === 0" class="text-sm text-gray-500">
            No hay repartidores registrados (rol Repartidor).
          </p>

          <button
            class="ml-auto rounded-md bg-rose-600 px-3 py-1.5 text-white hover:bg-rose-700 disabled:opacity-50"
            :disabled="processing || estadoActual==='entregado'"
            @click="doCancelar"
          >
            Cancelar pedido
          </button>
        </div>
      </div>
    </div>

    <!-- Resumen -->
    <div class="grid md:grid-cols-4 gap-4 mb-6">
      <div class="rounded-xl border bg-white p-4">
        <p class="text-sm text-gray-500">Estado</p>
        <p class="text-lg font-semibold capitalize">{{ pedido.estado.replace('_',' ') }}</p>
      </div>
      <div class="rounded-xl border bg-white p-4">
        <p class="text-sm text-gray-500">Tipo de entrega</p>
        <p class="text-lg font-semibold capitalize">{{ pedido.tipo.replace('_',' ') }}</p>
      </div>
      <div class="rounded-xl border bg-white p-4">
        <p class="text-sm text-gray-500">Total</p>
        <p class="text-lg font-semibold">{{ money(pedido.total) }}</p>
      </div>
      <div class="rounded-xl border bg-white p-4">
        <p class="text-sm text-gray-500">Asignado a</p>
        <p class="text-lg font-semibold">
          {{ (repartidores.find(r => Number(r.id) === Number(pedido.asignado_a))?.name) || '—' }}
        </p>
      </div>
    </div>

    <!-- Items -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
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

    <div v-if="pedido.observaciones" class="mt-6 rounded-xl border bg-white p-4">
      <p class="text-sm text-gray-500 mb-1">Observaciones</p>
      <p class="text-gray-800">{{ pedido.observaciones }}</p>
    </div>
    <!-- Bitácora -->
<div class="mt-6 rounded-xl border bg-white p-4">
  <h3 class="mb-3 text-lg font-semibold text-gray-800">Bitácora</h3>

  <div v-if="pedido.logs?.length">
    <ul class="space-y-2">
      <li v-for="log in pedido.logs" :key="log.id" class="text-sm text-gray-700">
        <span class="font-medium text-gray-900">{{ log.fecha }}</span>
        —
        <span class="uppercase tracking-wide text-xs px-2 py-0.5 rounded-full"
              :class="{
                'bg-blue-100 text-blue-800': log.accion === 'asignado' || log.accion === 'desasignado',
                'bg-amber-100 text-amber-800': log.accion === 'estado_cambiado',
                'bg-rose-100 text-rose-800': log.accion === 'cancelado'
              }">
          {{ log.accion.replace('_',' ') }}
        </span>
        por <span class="font-medium">{{ log.by }}</span>

        <template v-if="log.accion === 'estado_cambiado'">
          — estado: <span class="italic">{{ log.de || '—' }}</span> → <span class="italic">{{ log.a || '—' }}</span>
        </template>

        <template v-else-if="log.accion === 'asignado' || log.accion === 'desasignado'">
          — repartidor: <span class="italic">{{ log.de || '—' }}</span> → <span class="italic">{{ log.a || '—' }}</span>
        </template>

        <template v-if="log.motivo">
          — motivo: <span class="italic">{{ log.motivo }}</span>
        </template>
      </li>
    </ul>
  </div>

  <p v-else class="text-sm text-gray-500">Aún no hay eventos registrados para este pedido.</p>
</div>

  </div>
</template>
