<script setup>
import ClienteHeader from '@/Components/ClienteHeader.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  pedidos: { type: Array, default: () => [] },
})

const fmtFecha = (f) =>
  new Date(f).toLocaleString('es-MX', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })

const fmtMoney = (n) =>
  new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
  }).format(Number(n ?? 0))

const estadoClase = (estado) => {
  const e = String(estado).toLowerCase()

  return {
    pendiente: "bg-amber-100 text-amber-900 border-amber-300",
    confirmado: "bg-blue-100 text-blue-800 border-blue-300",
    preparando: "bg-blue-100 text-blue-800 border-blue-300",
    listo: "bg-indigo-100 text-indigo-800 border-indigo-300",
    en_camino: "bg-indigo-100 text-indigo-800 border-indigo-300",
    entregado: "bg-emerald-100 text-emerald-800 border-emerald-300",
    cancelado: "bg-rose-100 text-rose-800 border-rose-300",
  }[e] || "bg-slate-100 text-slate-700 border-slate-300"
}
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <ClienteHeader />

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-8">

      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
            <span class="inline-flex h-9 w-9 items-center justify-center bg-amber-100 text-amber-700 rounded-full">
              📦
            </span>
            Mis pedidos
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Revisa el estado de tus compras y consulta el historial completo.
          </p>
        </div>
      </div>

      <div
        v-if="!props.pedidos.length"
        class="rounded-xl border border-dashed border-slate-300 bg-slate-50 py-12 text-center"
      >
        <p class="text-slate-500 text-sm">Aún no tienes pedidos realizados.</p>

        <div class="mt-4">
          <Link
            :href="route('catalogo.index')"
            class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-600 active:scale-95 transition"
          >
            🛍 Realizar mi primer pedido
          </Link>
        </div>
      </div>

      <div v-else class="overflow-hidden rounded-xl border border-slate-200">
        <table class="min-w-full text-sm text-slate-700">
          <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase tracking-wide">
            <tr>
              <th class="px-5 py-3">Folio</th>
              <th class="px-5 py-3">Fecha</th>
              <th class="px-5 py-3">Estado</th>
              <th class="px-5 py-3 text-right">Total</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200 bg-white">
            <tr
              v-for="p in props.pedidos"
              :key="p.id"
              class="hover:bg-slate-50 transition"
            >
              <td class="px-5 py-4 font-bold text-slate-900">
                PED-{{ p.id }}
              </td>

              <td class="px-5 py-4 text-slate-600">
                {{ fmtFecha(p.created_at) }}
              </td>

              <td class="px-5 py-4">
                <span
                  class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold"
                  :class="estadoClase(p.estado)"
                >
                  ● {{ p.estado }}
                </span>
              </td>

              <td class="px-5 py-4 text-right font-semibold text-slate-900">
                {{ fmtMoney(p.total) }}
              </td>
            </tr>
          </tbody>
        </table>

        <div class="bg-slate-50 text-[11px] text-slate-500 px-5 py-3 border-t border-slate-200">
          Los montos mostrados corresponden al total del pedido en el momento de confirmación.
        </div>
      </div>
    </section>
  </div>
</template>
