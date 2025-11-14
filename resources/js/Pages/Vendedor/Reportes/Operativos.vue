<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  fecha: {
    type: String,
    default: null,
  },
  // Objeto que te manda el controlador con los totales
  resumen: {
    type: Object,
    default: () => ({}),
  },
})

/* 🔹 Fecha seleccionada */
const fecha = ref(props.fecha ?? new Date().toISOString().slice(0, 10))

/* 🔹 Normalizar números (por si vienen null / undefined) */
const stats = computed(() => ({
  total:           Number(props.resumen.total           ?? 0),
  pendientes:      Number(props.resumen.pendientes      ?? 0),
  en_camino:       Number(props.resumen.en_camino       ?? 0),
  entregados:      Number(props.resumen.entregados      ?? 0),
  cancelados:      Number(props.resumen.cancelados      ?? 0),
  mostrador:       Number(props.resumen.mostrador       ?? 0),
  domicilio:       Number(props.resumen.domicilio       ?? 0),
  total_importe:   Number(props.resumen.total_importe   ?? 0),
  mostrador_monto: Number(props.resumen.mostrador_monto ?? 0),
  domicilio_monto: Number(props.resumen.domicilio_monto ?? 0),
}))

const fmtInt = n =>
  new Intl.NumberFormat('es-MX').format(Number(n ?? 0))

const fmtMoney = n =>
  new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
  }).format(Number(n ?? 0))

/* 🔹 Consultar otro día */
const consultar = () => {
  router.get(
    route('vendedor.reportes.operativos'),
    { fecha: fecha.value },
    { preserveState: true, preserveScroll: true }
  )
}

/* 🔹 Ir a pedidos con filtros al hacer clic en tarjetas */
const irAPedidos = (params = {}) => {
  router.get(
    route('vendedor.pedidos.index'),
    params,
    { preserveScroll: true }
  )
}
</script>

<template>
  <AuthenticatedLayout role="vendedor">
    <Head title="Reportes operativos" />

    <!-- HEADER -->
    <template #header>
      <div
        class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-indigo-50 via-white to-white px-4 py-4 ring-1 ring-indigo-100/70"
      >
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">
         📝 Reportes operativos
          </h1>
          <p class="mt-1 text-sm text-gray-500">
            Resumen del día seleccionado.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <input
            v-model="fecha"
            type="date"
            class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          />
          <button
            type="button"
            @click="consultar"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
          >
            Consultar
          </button>

          <Link
            :href="route('vendedor.dashboard')"
            class="text-sm text-indigo-700 hover:text-indigo-900"
          >
            ← Volver al panel
          </Link>
        </div>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="mx-auto max-w-7xl space-y-6 p-6">

      <!-- FILA 1: RESUMEN GENERAL -->
      <section class="grid gap-4 md:grid-cols-3">
        <!-- Total pedidos -->
        <div
          class="cursor-pointer rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
          @click="irAPedidos()"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                Total de pedidos
              </p>
              <p class="mt-2 text-3xl font-semibold text-gray-900">
                {{ fmtInt(stats.total) }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                Para la fecha seleccionada.
              </p>
            </div>
            <div
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
            >
              📦
            </div>
          </div>
        </div>

        <!-- Importe total -->
        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                Importe total
              </p>
              <p class="mt-2 text-2xl font-semibold text-gray-900">
                {{ fmtMoney(stats.total_importe) }}
              </p>
              <p class="mt-1 text-xs text-gray-500">
                Suma de todos los pedidos del día.
              </p>
            </div>
            <div
              class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
            >
              💰
            </div>
          </div>
        </div>

        <!-- Pedidos hoy (solo mostrador + domicilio separados) -->
        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">
            Tipo de pedidos
          </p>
          <div class="mt-3 space-y-1 text-sm text-gray-700">
            <p>
              <span class="font-medium">Mostrador:</span>
              {{ fmtInt(stats.mostrador) }} pedidos
            </p>
            <p>
              <span class="font-medium">Domicilio:</span>
              {{ fmtInt(stats.domicilio) }} pedidos
            </p>
          </div>
        </div>
      </section>

      <!-- FILA 2: POR ESTADO -->
      <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">
          Pedidos por estado
        </h2>

        <div class="grid gap-4 md:grid-cols-4">
          <!-- Pendientes -->
          <div
            class="cursor-pointer rounded-2xl border border-amber-100 bg-amber-50/60 p-4 text-amber-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            @click="irAPedidos({ estado: 'pendiente' })"
          >
            <p class="text-xs font-semibold uppercase tracking-wide">Pendientes</p>
            <p class="mt-2 text-2xl font-semibold">
              {{ fmtInt(stats.pendientes) }}
            </p>
            <p class="mt-1 text-xs text-amber-900/80">
              Aún por atender.
            </p>
          </div>

          <!-- En camino -->
          <div
            class="cursor-pointer rounded-2xl border border-sky-100 bg-sky-50/70 p-4 text-sky-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            @click="irAPedidos({ estado: 'en_camino' })"
          >
            <p class="text-xs font-semibold uppercase tracking-wide">En camino</p>
            <p class="mt-2 text-2xl font-semibold">
              {{ fmtInt(stats.en_camino) }}
            </p>
            <p class="mt-1 text-xs text-sky-900/80">
              Van en ruta al cliente.
            </p>
          </div>

          <!-- Entregados -->
          <div
            class="cursor-pointer rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-emerald-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            @click="irAPedidos({ estado: 'entregado' })"
          >
            <p class="text-xs font-semibold uppercase tracking-wide">Entregados</p>
            <p class="mt-2 text-2xl font-semibold">
              {{ fmtInt(stats.entregados) }}
            </p>
            <p class="mt-1 text-xs text-emerald-900/80">
              Completados correctamente.
            </p>
          </div>

          <!-- Cancelados -->
          <div
            class="cursor-pointer rounded-2xl border border-rose-100 bg-rose-50/70 p-4 text-rose-900 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            @click="irAPedidos({ estado: 'cancelado' })"
          >
            <p class="text-xs font-semibold uppercase tracking-wide">Cancelados</p>
            <p class="mt-2 text-2xl font-semibold">
              {{ fmtInt(stats.cancelados) }}
            </p>
            <p class="mt-1 text-xs text-rose-900/80">
              Pedidos anulados.
            </p>
          </div>
        </div>
      </section>

      <!-- FILA 3: MOSTRADOR / DOMICILIO (CLICKEABLES) -->
      <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">
          Detalle por tipo de pedido
        </h2>

        <div class="grid gap-4 md:grid-cols-2">
          <!-- Mostrador -->
          <div
            class="cursor-pointer rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            @click="irAPedidos({ tipo: 'mostrador' })"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                  Pedidos en mostrador
                </p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                  {{ fmtInt(stats.mostrador) }} pedidos
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  Monto: {{ fmtMoney(stats.mostrador_monto) }}
                </p>
              </div>
              <div
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
              >
                🧾
              </div>
            </div>
          </div>

          <!-- Domicilio -->
          <div
            class="cursor-pointer rounded-2xl border border-purple-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            @click="irAPedidos({ tipo: 'domicilio' })"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-purple-600">
                  Pedidos a domicilio
                </p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">
                  {{ fmtInt(stats.domicilio) }} pedidos
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  Monto: {{ fmtMoney(stats.domicilio_monto) }}
                </p>
              </div>
              <div
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600"
              >
                🏍️
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
