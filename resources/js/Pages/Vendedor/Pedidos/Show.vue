<script setup>
import VendedorLayout from '@/Layouts/VendedorLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  // viene tal cual del controlador:
  // 'pedido' contiene: id, folio, estado, tipo, total, observaciones, created_at,
  // asignado_a, items[], logs[]
  pedido: { type: Object, required: true },
  // lista [{id, name}]
  repartidores: { type: Array, default: () => [] },
})

/* ---------- Derivados ---------- */
const estado    = computed(() => props.pedido.estado ?? 'pendiente')
const isCancel  = computed(() => estado.value === 'cancelado')
const isEntreg  = computed(() => estado.value === 'entregado')
const canCancel = computed(() => !isCancel.value && !isEntreg.value)
const puedeCambiarEstado = computed(() => !isCancel.value)

const repartidorId = ref(props.pedido.asignado_a ?? '')

const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
    .format(Number(n ?? 0))

/* ---------- Acciones ---------- */
function toast(msg, ok = true) {
  // simple feedback (puedes reemplazar por tu componente toast)
  window.alert((ok ? '✅ ' : '⚠️ ') + msg)
}

function changeEstado(nuevo) {
  if (!puedeCambiarEstado.value) return
  if (!window.confirm(`¿Cambiar el estado del pedido a "${nuevo.replace('_', ' ')}"?`)) return

  router.patch(
    route('vendedor.pedidos.estado', props.pedido.id),
    { estado: nuevo },
    {
      preserveScroll: true,
      onSuccess: () => toast('Estado actualizado.'),
      onError:   () => toast('No se pudo actualizar el estado.', false),
    }
  )
}

function asignar() {
  const id = repartidorId.value || null
  router.patch(
    route('vendedor.pedidos.asignar', props.pedido.id),
    { repartidor_id: id },
    {
      preserveScroll: true,
      onSuccess: () => toast(id ? 'Pedido asignado.' : 'Pedido desasignado.'),
      onError:   () => toast('No se pudo actualizar la asignación.', false),
    }
  )
}

function cancelar() {
  if (!canCancel.value) return
  const motivo = window.prompt('Motivo de la cancelación:')
  if (!motivo) return

  if (!window.confirm('¿Cancelar este pedido? Esta acción no se puede deshacer.')) return

  router.post(
    route('vendedor.pedidos.cancelar', props.pedido.id),
    { motivo },
    {
      preserveScroll: true,
      onSuccess: () => toast('Pedido cancelado.'),
      onError:   () => toast('No se pudo cancelar el pedido.', false),
    }
  )
}

/* ---------- UI helpers ---------- */
function estadoPill(e) {
  if (e === 'cancelado')  return 'bg-rose-100 text-rose-700'
  if (e === 'entregado')  return 'bg-emerald-100 text-emerald-700'
  if (e === 'en_camino')  return 'bg-indigo-100 text-indigo-700'
  if (e === 'listo')      return 'bg-blue-100 text-blue-700'
  if (e === 'preparando') return 'bg-amber-100 text-amber-700'
  return 'bg-gray-100 text-gray-700'
}

const barraPct = computed(() => {
  const orden = ['pendiente', 'preparando', 'listo', 'en_camino', 'entregado']
  const i = Math.max(0, orden.indexOf(estado.value))
  return (i / (orden.length - 1)) * 100
})
</script>

<template>
  <VendedorLayout>
    <!-- HEADER -->
    <template #header>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">
            Pedido #{{ pedido.id }}
            <span class="ml-2 text-sm text-gray-500">({{ pedido.folio }})</span>
          </h1>
          <p class="text-sm text-gray-500">Creado: {{ pedido.created_at }}</p>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('vendedor.pedidos.index')"
            class="rounded-lg border px-3 py-2 text-sm text-indigo-700 hover:bg-indigo-50"
          >
            ← Volver a la lista
          </Link>

          <button
            v-if="canCancel"
            @click="cancelar"
            class="rounded-lg bg-rose-600 px-3 py-2 text-sm font-medium text-white hover:bg-rose-700"
            type="button"
          >
            Cancelar pedido
          </button>
        </div>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="mx-auto max-w-7xl px-6 py-8 space-y-8">
      <!-- Estado global + barra -->
      <section class="rounded-2xl border bg-white p-4">
        <div class="mb-4">
          <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="estadoPill(estado)">
            {{ estado.replace('_',' ') }}
          </span>
        </div>

        <div class="relative h-2 w-full rounded-full bg-gray-200">
          <div
            class="absolute left-0 top-0 h-2 rounded-full bg-indigo-600 transition-all"
            :style="{ width: barraPct + '%' }"
          />
        </div>

        <div class="mt-3 grid grid-cols-5 text-center text-xs text-gray-500">
          <span>Pendiente</span>
          <span>Preparando</span>
          <span>Listo</span>
          <span>En Camino</span>
          <span>Entregado</span>
        </div>
      </section>

      <!-- Acciones principales -->
      <section class="grid gap-6 md:grid-cols-2">
        <!-- Cambiar estado -->
        <div class="rounded-2xl border bg-white p-4">
          <h3 class="mb-3 text-sm font-semibold text-gray-900">Cambiar estado</h3>
          <div class="flex flex-wrap gap-2">
            <button
              class="rounded-lg bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-800 hover:bg-amber-200 disabled:opacity-50"
              :disabled="!puedeCambiarEstado"
              @click="changeEstado('preparando')"
            >
              Preparando
            </button>
            <button
              class="rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-800 hover:bg-blue-200 disabled:opacity-50"
              :disabled="!puedeCambiarEstado"
              @click="changeEstado('listo')"
            >
              Listo
            </button>
            <button
              class="rounded-lg bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-800 hover:bg-indigo-200 disabled:opacity-50"
              :disabled="!puedeCambiarEstado"
              @click="changeEstado('en_camino')"
            >
              En camino
            </button>
            <button
              class="rounded-lg bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-800 hover:bg-emerald-200 disabled:opacity-50"
              :disabled="!puedeCambiarEstado"
              @click="changeEstado('entregado')"
            >
              Entregado
            </button>
          </div>
        </div>

        <!-- Asignación -->
        <div class="rounded-2xl border bg-white p-4">
          <h3 class="mb-3 text-sm font-semibold text-gray-900">Asignación y cancelación</h3>

          <div class="flex items-center gap-3">
            <select
              v-model="repartidorId"
              class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">— Sin asignar —</option>
              <option v-for="r in repartidores" :key="r.id" :value="r.id">
                {{ r.name }}
              </option>
            </select>

            <button
              class="rounded-lg border px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              type="button"
              @click="asignar"
            >
              Guardar
            </button>
          </div>
        </div>
      </section>

      <!-- Resumen -->
      <section class="grid gap-6 md:grid-cols-3">
        <div class="rounded-2xl border bg-white p-4">
          <p class="text-sm text-gray-500">Estado</p>
          <p class="mt-1 text-lg font-semibold capitalize">
            {{ estado.replace('_',' ') }}
          </p>
        </div>

        <div class="rounded-2xl border bg-white p-4">
          <p class="text-sm text-gray-500">Tipo de entrega</p>
          <p class="mt-1 text-lg font-semibold capitalize">{{ pedido.tipo }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-4">
          <p class="text-sm text-gray-500">Total</p>
          <p class="mt-1 text-lg font-semibold">{{ money(pedido.total) }}</p>
        </div>
      </section>

      <!-- Items -->
      <section class="overflow-hidden rounded-2xl border bg-white">
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
            <tr
              v-for="it in pedido.items"
              :key="it.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="px-4 py-3">{{ it.producto }}</td>
              <td class="px-4 py-3">{{ it.cantidad }}</td>
              <td class="px-4 py-3">{{ money(it.precio) }}</td>
              <td class="px-4 py-3 font-medium">{{ money(it.subtotal) }}</td>
            </tr>

            <tr v-if="!pedido.items?.length">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                Sin productos.
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Observaciones -->
      <section v-if="pedido.observaciones" class="rounded-2xl border bg-white p-4">
        <h3 class="mb-1 text-sm font-semibold text-gray-900">Observaciones</h3>
        <p class="text-gray-700">{{ pedido.observaciones }}</p>
      </section>

      <!-- Bitácora / Logs -->
      <section v-if="pedido.logs?.length" class="rounded-2xl border bg-white p-4">
        <h3 class="mb-3 text-sm font-semibold text-gray-900">Bitácora</h3>
        <div class="space-y-2">
          <div
            v-for="l in pedido.logs"
            :key="l.id"
            class="flex items-center justify-between rounded-lg border px-3 py-2 text-sm"
          >
            <div>
              <span class="font-medium">{{ l.accion }}</span>
              <span v-if="l.de || l.a" class="text-gray-500">
                &nbsp;— {{ l.de ?? '—' }} → {{ l.a ?? '—' }}
              </span>
              <span v-if="l.motivo" class="text-gray-500"> ({{ l.motivo }})</span>
              <span class="text-gray-400"> — por {{ l.by }}</span>
            </div>
            <div class="text-gray-500">{{ l.fecha }}</div>
          </div>
        </div>
      </section>
    </div>
  </VendedorLayout>
</template>
