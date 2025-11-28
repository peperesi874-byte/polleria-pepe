<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  role: { type: String, default: 'admin' },
  pedido: { type: Object, required: true },
  repartidores: { type: Array, default: () => [] }
})

const prefix = computed(() => (props.role === 'vendedor' ? 'vendedor.' : 'admin.'))

const processing = ref(false)
const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)

const estadoActual = computed(() => props.pedido?.estado ?? 'pendiente')
const bloqueado = computed(() => ['cancelado', 'entregado'].includes(estadoActual.value))

const estadosOrden = ['pendiente','preparando','listo','en_camino','entregado']
const estadoLabel  = e => (e || '').replace('_',' ')
const stepIndex    = computed(() => Math.max(0, estadosOrden.indexOf(estadoActual.value)))

const pillEstado = (e) => {
  const m = {
    cancelado : 'bg-rose-100 text-rose-700 ring-1 ring-rose-300',
    entregado : 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-300',
    en_camino : 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-300',
    listo     : 'bg-sky-100 text-sky-700 ring-1 ring-sky-300',
    preparando: 'bg-amber-100 text-amber-700 ring-1 ring-amber-300',
    pendiente : 'bg-slate-100 text-slate-700 ring-1 ring-slate-300',
  }
  return m[e] || 'bg-slate-100 text-slate-700 ring-1 ring-slate-300'
}

/* Acciones */
function setEstado(estado) {
  if (bloqueado.value) return alert('El pedido ya no permite cambios.')
  if (estado === estadoActual.value) return
  processing.value = true
  router.put(
    route(prefix.value + 'pedidos.estado', props.pedido.id),
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
    route(prefix.value + 'pedidos.cancelar', props.pedido.id),
    { motivo },
    { preserveScroll: true, preserveState: true, onFinish: () => (processing.value = false) }
  )
}

function asignar(val) {
  if (estadoActual.value === 'cancelado') return alert('No se puede asignar un pedido cancelado.')
  const repartidor_id = val ? Number(val) : null
  processing.value = true
  router.put(
    route(prefix.value + 'pedidos.asignar', props.pedido.id),
    { repartidor_id },
    { preserveScroll: true, preserveState: true, onFinish: () => (processing.value = false) }
  )
}

/* Bitácora helpers */
const actionBadge = (a = '') => {
  a = a.toLowerCase()
  if (a === 'estado_cambiado') return 'bg-amber-100 text-amber-800 ring-1 ring-amber-200'
  if (a === 'asignado' || a === 'desasignado') return 'bg-sky-100 text-sky-800 ring-1 ring-sky-200'
  if (a === 'cancelado') return 'bg-rose-100 text-rose-800 ring-1 ring-rose-200'
  return 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'
}
const actionIcon = (a = '') => {
  a = a.toLowerCase()
  if (a === 'estado_cambiado') return '🪄'
  if (a === 'asignado' || a === 'desasignado') return '👤'
  if (a === 'cancelado') return '⛔'
  return '📝'
}
const actionLabel = (a = '') => (a || '').replaceAll('_',' ').toUpperCase()
</script>

<template>
  <Head :title="`Pedido #${pedido.id}`" />

  <AuthenticatedLayout>
    <!-- ===== HEADER ESTILO DASHBOARD ===== -->
    <template #header>
      <div class="mx-auto max-w-7xl px-4 lg:px-6">
        <div
          class="relative flex flex-wrap items-center justify-between gap-4 rounded-[32px] bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-6 py-4 text-white shadow-[0_26px_70px_rgba(249,115,22,0.55)] ring-1 ring-amber-400/70"
        >
          <!-- decor -->
          <div class="pointer-events-none absolute -left-16 -top-16 h-32 w-32 rounded-full bg-white/20 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-32 w-32 rounded-full bg-black/25 blur-3xl" />

          <div class="relative flex items-start gap-3">
            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20 text-amber-50 text-lg shadow-md shadow-black/20">
              🧾
            </div>

            <div class="space-y-1">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
              >
                <span class="uppercase tracking-wide">Detalle de pedido</span>
                <span class="h-1 w-1 rounded-full bg-emerald-400" />
                <span>Pollería Pepe</span>
              </div>

              <h2 class="text-2xl font-extrabold tracking-tight text-white">
                Pedido #{{ pedido.id }}
                <span v-if="pedido.folio" class="ml-1 text-base font-normal text-amber-50/90">
                  ({{ pedido.folio }})
                </span>
              </h2>

              <p class="text-xs text-amber-50/85">
                Creado: {{ pedido.created_at }}
              </p>

              <span class="inline-flex items-center gap-2 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-amber-900 shadow-sm">
                <span
                  class="h-1.5 w-1.5 rounded-full"
                  :class="{
                    'bg-emerald-500': estadoActual === 'entregado',
                    'bg-rose-500': estadoActual === 'cancelado',
                    'bg-amber-500': estadoActual !== 'entregado' && estadoActual !== 'cancelado'
                  }"
                />
                {{ estadoLabel(estadoActual).toUpperCase() }}
              </span>
            </div>
          </div>

          <div class="relative flex flex-wrap items-center justify-end gap-2">
            <Link
              :href="route(prefix + 'pedidos.index')"
              class="inline-flex items-center gap-1 rounded-full border border-amber-100 bg-white/95 px-3 py-1.5 text-xs font-medium text-amber-800 shadow-sm hover:bg-amber-50"
            >
              ← Volver a la lista
            </Link>

            <!-- PDF -->
            <a
              :href="route(prefix + 'pedidos.ticket', pedido.id)"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-1 rounded-full border border-amber-100 bg-white/95 px-3 py-1.5 text-xs font-medium text-amber-800 shadow-sm hover:bg-amber-50"
            >
              🖨 Imprimir ticket
            </a>

            <button
              class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 disabled:opacity-50"
              :disabled="processing || estadoActual==='entregado'"
              @click="doCancelar"
            >
              ⛔ Cancelar pedido
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/20 to-slate-50">
      <div class="mx-auto max-w-7xl px-6 py-8 space-y-6">
        <!-- Estado / progreso -->
        <div
          class="rounded-3xl border border-slate-900/60 bg-slate-950/95 px-5 py-4 text-slate-50 shadow-[0_20px_55px_rgba(15,23,42,0.85)]"
        >
          <div class="mb-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <span
                class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-semibold"
                :class="pillEstado(estadoActual)"
              >
                Estado actual: {{ estadoLabel(estadoActual) }}
              </span>
              <span
                v-if="bloqueado"
                class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[11px] font-semibold text-emerald-200 ring-1 ring-emerald-500/50"
              >
                ✓ Pedido cerrado
              </span>
            </div>
            <p class="text-[11px] text-slate-400">
              Sigue el recorrido del pedido desde pendiente hasta entregado.
            </p>
          </div>

          <div class="relative mt-1">
            <div class="h-1.5 w-full rounded-full bg-slate-800" />
            <div
              class="absolute inset-y-0 left-0 h-1.5 rounded-full bg-emerald-400 transition-all"
              :style="{ width: ((stepIndex + 1) / estadosOrden.length) * 100 + '%' }"
            />

            <div class="mt-3 grid grid-cols-5 text-[11px] font-medium text-slate-300">
              <div
                v-for="(e, idx) in estadosOrden"
                :key="e"
                class="flex flex-col items-center gap-1"
              >
                <div
                  class="grid h-5 w-5 place-items-center rounded-full border text-[10px]"
                  :class="idx <= stepIndex
                    ? 'border-emerald-400 bg-emerald-500 text-slate-950'
                    : 'border-slate-600 bg-slate-900 text-slate-300'"
                >
                  {{ idx + 1 }}
                </div>
                <span class="capitalize">{{ e.replace('_',' ') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones principales -->
        <div class="grid gap-4 md:grid-cols-2">
          <!-- Cambiar estado -->
          <div class="rounded-3xl border border-amber-100 bg-white/95 p-5 shadow-sm">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-amber-700">
              Cambiar estado
            </p>
            <div class="flex flex-wrap gap-2">
              <button
                class="rounded-full bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 disabled:opacity-50"
                :disabled="processing || bloqueado || estadoActual==='preparando'"
                @click="setEstado('preparando')"
              >
                Preparando
              </button>

              <button
                class="rounded-full bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-sky-600 disabled:opacity-50"
                :disabled="processing || bloqueado || estadoActual==='listo'"
                @click="setEstado('listo')"
              >
                Listo
              </button>

              <button
                class="rounded-full bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-600 disabled:opacity-50"
                :disabled="processing || bloqueado || estadoActual==='en_camino'"
                @click="setEstado('en_camino')"
              >
                En camino
              </button>

              <button
                class="rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50"
                :disabled="processing || estadoActual==='entregado'"
                @click="setEstado('entregado')"
              >
                Entregado
              </button>
            </div>
          </div>

          <!-- Asignación -->
          <div class="rounded-3xl border border-amber-100 bg-white/95 p-5 shadow-sm">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-amber-700">
              Asignación
            </p>
            <div class="flex flex-wrap items-center gap-3">
              <div class="relative">
                <select
                  class="w-56 appearance-none rounded-2xl border border-amber-200 bg-white px-3 py-2 pr-9 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80 disabled:opacity-50"
                  :disabled="processing || estadoActual==='cancelado'"
                  :value="pedido.asignado_a || ''"
                  @change="asignar($event.target.value)"
                >
                  <option value="">— Sin asignar —</option>
                  <option v-for="r in repartidores" :key="r.id" :value="r.id">{{ r.name }}</option>
                </select>
                <svg
                  class="pointer-events-none absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 text-amber-500"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <p v-if="repartidores.length === 0" class="text-xs text-slate-500">
                No hay repartidores registrados.
              </p>
            </div>
          </div>
        </div>

        <!-- KPIs -->
        <div class="grid gap-4 md:grid-cols-4">
          <div class="rounded-3xl border border-amber-100 bg-white/95 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Estado</p>
            <p class="mt-1 text-sm font-semibold capitalize text-slate-900">
              {{ pedido.estado.replace('_',' ') }}
            </p>
          </div>
          <div class="rounded-3xl border border-amber-100 bg-white/95 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Tipo de entrega</p>
            <p class="mt-1 text-sm font-semibold capitalize text-slate-900">
              {{ pedido.tipo.replace('_',' ') }}
            </p>
          </div>
          <div class="rounded-3xl border border-amber-100 bg-white/95 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total</p>
            <p class="mt-1 text-lg font-extrabold text-emerald-700">
              {{ money(pedido.total) }}
            </p>
          </div>
          <div class="rounded-3xl border border-amber-100 bg-white/95 p-4 shadow-sm">
            <p class="text-xs text-slate-500">Asignado a</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">
              {{ (repartidores.find(r => Number(r.id) === Number(pedido.asignado_a))?.name) || '—' }}
            </p>
          </div>
        </div>

        <!-- Datos del cliente -->
        <div
          v-if="pedido?.cliente"
          class="rounded-3xl border border-amber-100 bg-white/95 p-5 shadow-sm"
        >
          <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-700">
            Datos del cliente
          </h3>

          <div class="grid gap-6 text-sm sm:grid-cols-3">
            <div>
              <p class="text-xs text-slate-500">Nombre</p>
              <p class="mt-1 font-semibold text-slate-900">
                {{ pedido.cliente.nombre || '—' }}
              </p>
            </div>
            <div v-if="pedido.cliente.telefono">
              <p class="text-xs text-slate-500">Teléfono</p>
              <p class="mt-1 text-slate-900">
                {{ pedido.cliente.telefono }}
              </p>
            </div>
            <div v-if="pedido.cliente.email">
              <p class="text-xs text-slate-500">Correo electrónico</p>
              <p class="mt-1 text-slate-900">
                {{ pedido.cliente.email }}
              </p>
            </div>
          </div>
        </div>

        <!-- Envío domicilio -->
        <div
          v-if="pedido?.tipo === 'domicilio'"
          class="rounded-3xl border border-amber-100 bg-white/95 p-5 shadow-sm"
        >
          <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-700">
            Datos de envío a domicilio
          </h3>

          <div class="grid gap-6 text-sm sm:grid-cols-2">
            <div>
              <p class="text-xs text-slate-500">Destinatario</p>
              <p class="mt-1 text-slate-900">
                {{ pedido?.envio?.nombre ?? pedido?.entrega_nombre ?? pedido?.dom?.nombre ?? '—' }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Teléfono</p>
              <p class="mt-1 text-slate-900">
                {{ pedido?.envio?.telefono ?? pedido?.entrega_telefono ?? pedido?.dom?.telefono ?? '—' }}
              </p>
            </div>
            <div class="sm:col-span-2">
              <p class="text-xs text-slate-500">Dirección</p>
              <p class="mt-1 text-slate-900">
                {{ pedido?.envio?.direccion ?? pedido?.entrega_direccion ?? pedido?.dom?.direccion ?? '—' }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Colonia</p>
              <p class="mt-1 text-slate-900">
                {{ pedido?.envio?.colonia ?? pedido?.entrega_colonia ?? pedido?.dom?.colonia ?? '—' }}
              </p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Ciudad</p>
              <p class="mt-1 text-slate-900">
                {{ pedido?.envio?.ciudad ?? pedido?.entrega_municipio ?? pedido?.dom?.ciudad ?? '—' }}
              </p>
            </div>
            <div class="sm:col-span-2">
              <p class="text-xs text-slate-500">Referencias</p>
              <p class="mt-1 text-slate-900">
                {{ pedido?.envio?.referencias ?? pedido?.entrega_referencias ?? pedido?.dom?.referencias ?? '—' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Tabla de items (oscura elegante) -->
        <div
          class="overflow-hidden rounded-3xl border border-slate-900/80 bg-slate-950/95 shadow-[0_18px_50px_rgba(15,23,42,0.85)]"
        >
          <table class="w-full text-sm">
            <thead class="bg-slate-900/95 text-[11px] font-semibold uppercase tracking-wide text-emerald-50">
              <tr>
                <th class="px-4 py-3 text-left">Producto</th>
                <th class="px-4 py-3 text-center">Cantidad</th>
                <th class="px-4 py-3 text-right">Precio</th>
                <th class="px-4 py-3 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/70">
              <tr
                v-for="it in pedido.items"
                :key="it.id"
                class="bg-slate-950/95 text-slate-100 transition hover:bg-slate-900/70"
              >
                <td class="px-4 py-3 text-sm font-medium text-slate-50">
                  {{ it.producto }}
                </td>
                <td class="px-4 py-3 text-center text-sm">
                  {{ it.cantidad }}
                </td>
                <td class="px-4 py-3 text-right text-sm text-slate-100">
                  {{ money(it.precio) }}
                </td>
                <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-400">
                  {{ money(it.subtotal) }}
                </td>
              </tr>
              <tr v-if="!pedido.items?.length">
                <td
                  colspan="4"
                  class="px-4 py-6 text-center text-sm text-slate-400 bg-slate-950/95"
                >
                  Este pedido no tiene items.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Observaciones -->
        <div
          v-if="pedido.observaciones"
          class="rounded-3xl border border-amber-100 bg-white/95 p-5 shadow-sm"
        >
          <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
            Observaciones
          </p>
          <p class="text-sm text-slate-800">
            {{ pedido.observaciones }}
          </p>
        </div>

        <!-- Bitácora -->
        <div class="rounded-3xl border border-amber-100 bg-white/95 p-5 shadow-sm">
          <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-amber-700">
            Bitácora de cambios
          </h3>
          <ul class="space-y-6">
            <li
              v-for="log in pedido.logs"
              :key="log.id"
              class="flex items-start gap-4 border-b border-amber-50 pb-4 last:border-0"
            >
              <div
                class="flex h-10 w-10 items-center justify-center rounded-full border bg-white text-lg"
                :class="{
                  'border-amber-300': log.accion==='estado_cambiado',
                  'border-sky-300': ['asignado','desasignado'].includes(log.accion),
                  'border-rose-300': log.accion==='cancelado',
                  'border-slate-200': !['estado_cambiado','asignado','desasignado','cancelado'].includes(log.accion)
                }"
              >
                {{ actionIcon(log.accion) }}
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between gap-3">
                  <span class="text-[11px] text-slate-500">{{ log.fecha }}</span>
                  <span
                    class="rounded-full px-3 py-1 text-[11px] font-semibold"
                    :class="actionBadge(log.accion)"
                  >
                    {{ actionLabel(log.accion) }}
                  </span>
                </div>
                <p class="mt-1 text-sm text-slate-800">
                  <span class="text-slate-500">por</span>
                  <span class="font-medium text-slate-900"> {{ log.by }} </span>

                  <template v-if="log.accion==='estado_cambiado'">
                    — estado:
                    <span class="italic">{{ log.de || '—' }}</span>
                    →
                    <span class="italic">{{ log.a || '—' }}</span>
                  </template>

                  <template v-if="['asignado','desasignado'].includes(log.accion)">
                    — repartidor:
                    <span class="italic">{{ log.de || '—' }}</span>
                    →
                    <span class="italic">{{ log.a || '—' }}</span>
                  </template>

                  <template v-if="log.motivo">
                    — motivo:
                    <span class="italic">{{ log.motivo }}</span>
                  </template>
                </p>
              </div>
            </li>

            <li
              v-if="!pedido.logs?.length"
              class="text-sm text-slate-500"
            >
              Aún no hay eventos registrados para este pedido.
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
