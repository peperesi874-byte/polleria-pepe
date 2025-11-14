<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()
const pedidos = computed(() => page.props.pedidos ?? [])

// -------------------- Helpers de formato --------------------
const fmtMoney = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
    .format(Number(n ?? 0))

const fmtDateTime = value => {
  if (!value) return ''
  const d = new Date(value)
  return d.toLocaleString('es-MX')
}

const repartidorName = computed(
  () => page.props?.auth?.user?.name ?? 'Repartidor'
)

// -------------------- Agrupar pedidos --------------------
const porSalir = computed(() =>
  pedidos.value.filter(p =>
    ['pendiente', 'confirmado', 'en_preparacion', 'listo'].includes(p.estado)
  )
)

const enCamino = computed(() =>
  pedidos.value.filter(p => ['en_camino', 'en_reparto'].includes(p.estado))
)

const historial = computed(() =>
  pedidos.value.filter(p => ['entregado', 'cancelado'].includes(p.estado))
)

// -------------------- Clases y labels --------------------
const estadoBadgeClass = estado => {
  switch (estado) {
    case 'pendiente':
    case 'confirmado':
    case 'en_preparacion':
    case 'listo':
      return 'bg-amber-100 text-amber-800'
    case 'en_camino':
    case 'en_reparto':
      return 'bg-sky-100 text-sky-800'
    case 'entregado':
      return 'bg-emerald-100 text-emerald-800'
    case 'cancelado':
      return 'bg-rose-100 text-rose-700'
    default:
      return 'bg-neutral-100 text-neutral-700'
  }
}

const estadoLabel = estado => {
  switch (estado) {
    case 'pendiente':        return 'Pendiente'
    case 'confirmado':       return 'Confirmado'
    case 'en_preparacion':   return 'En preparación'
    case 'listo':            return 'Listo para entregar'
    case 'en_reparto':
    case 'en_camino':        return 'En camino'
    case 'entregado':        return 'Entregado'
    case 'cancelado':        return 'Cancelado'
    default:                 return estado
  }
}

// -------------------- Acciones --------------------
const cambiarEstado = (pedido, estado) => {
  const label = estado === 'en_camino'
    ? 'iniciar el reparto'
    : 'marcar como entregado'

  if (!confirm(`¿Seguro que quieres ${label} del pedido #${pedido.folio ?? pedido.id}?`)) {
    return
  }

  router.post(
    route('repartidor.pedidos.cambiar-estado', pedido.id),
    { estado },
    { preserveScroll: true }
  )
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- HEADER del layout (igual concepto que Vendedor / Admin) -->
    <template #header>
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
          <h2 class="font-semibold text-xl text-neutral-900 leading-tight">
            Pedidos asignados
          </h2>
          <p class="text-sm text-neutral-500">
            Entregas pendientes y en ruta para {{ repartidorName }}.
          </p>
        </div>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="max-w-6xl mx-auto p-6 space-y-8">
      <!-- KPIs -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Por salir -->
        <div class="rounded-2xl border bg-white shadow-sm p-4 flex flex-col gap-2">
          <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide">
            Por salir
          </p>
          <p class="text-3xl font-bold text-neutral-900">
            {{ porSalir.length }}
          </p>
          <p class="text-xs text-neutral-500">
            Pedidos pendientes o listos que aún no han salido a reparto.
          </p>
        </div>

        <!-- En camino -->
        <div class="rounded-2xl border bg-white shadow-sm p-4 flex flex-col gap-2">
          <p class="text-xs font-semibold text-sky-700 uppercase tracking-wide">
            En camino
          </p>
          <p class="text-3xl font-bold text-neutral-900">
            {{ enCamino.length }}
          </p>
          <p class="text-xs text-neutral-500">
            Entregas actualmente en ruta.
          </p>
        </div>

        <!-- Historial (entregados) -->
        <div class="rounded-2xl border bg-white shadow-sm p-4 flex flex-col gap-2">
          <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">
            Entregados (recientes)
          </p>
          <p class="text-3xl font-bold text-neutral-900">
            {{ historial.filter(p => p.estado === 'entregado').length }}
          </p>
          <p class="text-xs text-neutral-500">
            Pedidos marcados como entregados en las últimas asignaciones.
          </p>
        </div>
      </section>

      <!-- SI NO HAY NADA -->
      <section
        v-if="!pedidos.length"
        class="border border-dashed rounded-2xl p-10 text-center bg-white/60 shadow-sm"
      >
        <p class="text-base font-semibold text-neutral-700">
          No tienes entregas asignadas por ahora.
        </p>
        <p class="mt-1 text-sm text-neutral-500 max-w-md mx-auto">
          Cuando el administrador o vendedor te asignen pedidos, aparecerán aquí.
        </p>
      </section>

      <!-- CONTENIDO PRINCIPAL -->
      <section
        v-else
        class="space-y-8"
      >
        <!-- POR SALIR -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-semibold text-neutral-800 uppercase tracking-wide">
                Pedidos por salir
              </h2>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700"
              >
                {{ porSalir.length }} pedidos
              </span>
            </div>
          </div>

          <div
            v-if="!porSalir.length"
            class="rounded-2xl border bg-neutral-50/60 p-4 text-xs text-neutral-500"
          >
            No hay pedidos pendientes ni listos para reparto.
          </div>

          <div
            v-else
            class="space-y-4"
          >
            <div
              v-for="pedido in porSalir"
              :key="'ps-' + pedido.id"
              class="border rounded-2xl p-4 bg-white shadow-sm flex flex-col gap-3"
            >
              <!-- Encabezado pedido -->
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                  <p class="text-xs text-neutral-500 uppercase tracking-wide">
                    Pedido
                  </p>
                  <p class="text-base font-semibold text-neutral-900">
                    #{{ pedido.folio ?? pedido.id }}
                  </p>
                  <p class="text-xs text-neutral-400">
                    Creado: {{ fmtDateTime(pedido.created_at) }}
                  </p>
                </div>

                <div class="flex items-center gap-4 justify-between md:justify-end">
                  <div class="flex flex-col items-start md:items-end gap-1">
                    <span class="text-xs text-neutral-500">Estado</span>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                      :class="estadoBadgeClass(pedido.estado)"
                    >
                      {{ estadoLabel(pedido.estado) }}
                    </span>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-neutral-400 uppercase tracking-wide">
                      Total
                    </p>
                    <p class="text-lg font-semibold text-neutral-900">
                      {{ fmtMoney(pedido.total) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Cliente / dirección -->
              <div class="grid md:grid-cols-2 gap-3 text-sm border-t pt-3">
                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Cliente / contacto
                  </p>
                  <p class="font-medium text-neutral-900">
                    {{ pedido.entrega_nombre || 'Nombre no especificado' }}
                  </p>
                  <p class="text-neutral-600">
                    Tel: {{ pedido.entrega_telefono || 'Sin teléfono' }}
                  </p>
                </div>

                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Dirección de entrega
                  </p>
                  <p class="text-neutral-700">
                    {{
                      [
                        pedido.entrega_calle,
                        pedido.entrega_numero,
                        pedido.entrega_colonia,
                        pedido.entrega_municipio,
                      ]
                        .filter(Boolean)
                        .join(', ') || 'Sin dirección registrada'
                    }}
                  </p>
                  <p
                    v-if="pedido.entrega_referencias"
                    class="text-neutral-500 text-xs"
                  >
                    Referencias: {{ pedido.entrega_referencias }}
                  </p>
                </div>
              </div>

              <!-- Acciones -->
              <div class="border-t pt-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <p class="text-xs text-neutral-500 max-w-sm">
                  Cuando salgas a ruta, inicia el reparto para que quede registrado en el sistema.
                </p>
                <div class="flex flex-wrap gap-2 justify-end">
                  <button
                    type="button"
                    class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full border border-sky-500 text-sky-600 hover:bg-sky-50 transition"
                    @click="cambiarEstado(pedido, 'en_camino')"
                  >
                    🚚 Iniciar reparto
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- EN CAMINO -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-semibold text-neutral-800 uppercase tracking-wide">
                Pedidos en camino
              </h2>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700"
              >
                {{ enCamino.length }} en ruta
              </span>
            </div>
          </div>

          <div
            v-if="!enCamino.length"
            class="rounded-2xl border bg-neutral-50/60 p-4 text-xs text-neutral-500"
          >
            No tienes entregas en camino en este momento.
          </div>

          <div
            v-else
            class="space-y-4"
          >
            <div
              v-for="pedido in enCamino"
              :key="'ec-' + pedido.id"
              class="border rounded-2xl p-4 bg-white shadow-sm flex flex-col gap-3"
            >
              <!-- Encabezado -->
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                  <p class="text-xs text-neutral-500 uppercase tracking-wide">
                    Pedido en ruta
                  </p>
                  <p class="text-base font-semibold text-neutral-900">
                    #{{ pedido.folio ?? pedido.id }}
                  </p>
                  <p class="text-xs text-neutral-400">
                    Iniciado: {{ fmtDateTime(pedido.created_at) }}
                  </p>
                </div>

                <div class="flex items-center gap-4 justify-between md:justify-end">
                  <div class="flex flex-col items-start md:items-end gap-1">
                    <span class="text-xs text-neutral-500">Estado</span>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                      :class="estadoBadgeClass(pedido.estado)"
                    >
                      {{ estadoLabel(pedido.estado) }}
                    </span>
                  </div>
                  <div class="text-right">
                    <p class="text-xs text-neutral-400 uppercase tracking-wide">
                      Total
                    </p>
                    <p class="text-lg font-semibold text-neutral-900">
                      {{ fmtMoney(pedido.total) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Cliente / dirección -->
              <div class="grid md:grid-cols-2 gap-3 text-sm border-t pt-3">
                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Cliente / contacto
                  </p>
                  <p class="font-medium text-neutral-900">
                    {{ pedido.entrega_nombre || 'Nombre no especificado' }}
                  </p>
                  <p class="text-neutral-600">
                    Tel: {{ pedido.entrega_telefono || 'Sin teléfono' }}
                  </p>
                </div>

                <div class="space-y-1">
                  <p class="text-xs uppercase tracking-wide text-neutral-400">
                    Dirección de entrega
                  </p>
                  <p class="text-neutral-700">
                    {{
                      [
                        pedido.entrega_calle,
                        pedido.entrega_numero,
                        pedido.entrega_colonia,
                        pedido.entrega_municipio,
                      ]
                        .filter(Boolean)
                        .join(', ') || 'Sin dirección registrada'
                    }}
                  </p>
                  <p
                    v-if="pedido.entrega_referencias"
                    class="text-neutral-500 text-xs"
                  >
                    Referencias: {{ pedido.entrega_referencias }}
                  </p>
                </div>
              </div>

              <!-- Acciones -->
              <div class="border-t pt-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <p class="text-xs text-neutral-500 max-w-sm">
                  Al entregar el pedido al cliente, márcalo como entregado para cerrar la ruta.
                </p>
                <div class="flex flex-wrap gap-2 justify-end">
                  <button
                    type="button"
                    class="inline-flex items-center px-4 py-1.5 text-xs font-semibold rounded-full border border-emerald-500 text-emerald-600 hover:bg-emerald-50 transition"
                    @click="cambiarEstado(pedido, 'entregado')"
                  >
                    ✅ Marcar como entregado
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- HISTORIAL -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-semibold text-neutral-800 uppercase tracking-wide">
                Historial reciente
              </h2>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-50 text-neutral-700"
              >
                {{ historial.length }} registros
              </span>
            </div>
          </div>

          <div
            v-if="!historial.length"
            class="rounded-2xl border bg-neutral-50/60 p-4 text-xs text-neutral-500"
          >
            Aún no hay historial de entregas o cancelaciones recientes.
          </div>

          <div
            v-else
            class="rounded-2xl border bg-white shadow-sm divide-y"
          >
            <div
              v-for="pedido in historial"
              :key="'hist-' + pedido.id"
              class="px-4 py-3 flex items-center justify-between gap-3"
            >
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-neutral-900 truncate">
                  Pedido #{{ pedido.folio ?? pedido.id }}
                </p>
                <p class="text-xs text-neutral-500">
                  {{ pedido.entrega_nombre || 'Cliente sin nombre' }} ·
                  {{ fmtMoney(pedido.total) }}
                </p>
              </div>
              <div class="flex flex-col items-end gap-1">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="estadoBadgeClass(pedido.estado)"
                >
                  {{ estadoLabel(pedido.estado) }}
                </span>
                <span class="text-[11px] text-neutral-400">
                  {{ fmtDateTime(pedido.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
