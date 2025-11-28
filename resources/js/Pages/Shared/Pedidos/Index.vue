<!-- resources/js/Pages/.../Pedidos/Index.vue (o donde lo tengas) -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  role:    { type: String, default: 'admin' },   // 'admin' | 'vendedor'
  pedidos: { type: Object, required: true },     // paginator { data, links, ... }
  q:       { type: String, default: '' },
  estado:  { type: String, default: '' },
  asignado:{ type: String, default: '' },
  estados: { type: Array,  default: () => [] },
})

/* Base por rol */
const base = computed(() => (props.role === 'vendedor' ? 'vendedor' : 'admin'))

/* URL del form (GET clásico) */
const formAction = computed(() => `/${base.value}/pedidos`)

/* Filtros (con valores iniciales) */
const buscador       = ref(props.q ?? '')
const filtroEstado   = ref(props.estado ?? '')
const filtroAsignado = ref(props.asignado ?? '')

/* Autosubmit con debounce */
const formRef = ref(null)
let t = null
function autoSubmit() {
  clearTimeout(t)
  t = setTimeout(() => {
    if (formRef.value) formRef.value.submit()
  }, 300)
}

/* Formato de moneda */
const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n ?? 0)
</script>

<template>
  <AuthenticatedLayout>
    <!-- ===== HEADER NARANJA UNIFORME ===== -->
    <template #header>
      <div
        class="mx-auto max-w-7xl rounded-[32px] bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 px-6 py-4 shadow-[0_26px_70px_rgba(249,115,22,0.55)] ring-1 ring-amber-400/60 flex items-center justify-between"
      >
        <div class="flex items-center gap-4">
          <div
            class="h-12 w-12 flex items-center justify-center rounded-2xl bg-white/15 text-amber-50 text-2xl shadow-md shadow-black/20"
          >
            📦
          </div>

          <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-white drop-shadow-sm">
              {{ base === 'vendedor' ? 'Pedidos en mostrador' : 'Gestión de pedidos' }}
            </h2>
            <p class="text-xs text-amber-50/90">
              {{
                base === 'vendedor'
                  ? 'Registra y controla los pedidos de clientes presenciales.'
                  : 'Supervisa y administra los pedidos de todo el sistema.'
              }}
            </p>
            <p class="mt-1 text-[11px] text-amber-100/90">
              Usa los filtros para encontrar pedidos rápidamente por folio, estado o asignación.
            </p>
          </div>
        </div>

        <div class="flex flex-col items-end gap-2">
          <Link
            :href="`/${base}/dashboard`"
            class="inline-flex items-center gap-1 text-xs font-medium text-amber-50/90 hover:text-white hover:underline"
          >
            ← Volver al panel
          </Link>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/10 to-slate-50">
      <div class="mx-auto max-w-7xl px-4 py-6 space-y-5 lg:px-6">

        <!-- CARD DE FILTROS -->
        <div
          class="rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-amber-100 flex flex-wrap items-center justify-between gap-3"
        >
          <div>
            <p class="text-sm font-semibold text-slate-900 flex items-center gap-2">
              <span class="text-lg">🔎</span>
              Filtros de pedidos
            </p>
            <p class="text-xs text-slate-500">
              Busca por folio, estado o si están asignados a un repartidor.
            </p>
          </div>

          <form
            ref="formRef"
            :action="formAction"
            method="GET"
            class="flex flex-wrap items-center gap-3"
          >
            <!-- Buscar -->
            <div class="relative">
              <span
                class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-amber-400 text-sm"
              >
                🔍
              </span>
              <input
                name="q"
                v-model="buscador"
                type="search"
                inputmode="search"
                placeholder="Buscar por folio u observaciones…"
                class="w-[220px] md:w-[260px] lg:w-[320px] rounded-2xl border border-amber-300 bg-amber-50/60 pl-9 pr-3 py-2 text-sm text-slate-900 shadow-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                @input="autoSubmit"
                @keydown.enter.prevent="autoSubmit"
                aria-label="Buscar por folio u observaciones"
              />
            </div>

            <!-- Estado -->
            <div class="relative">
              <select
                name="estado"
                v-model="filtroEstado"
                class="w-[200px] appearance-none rounded-2xl border border-amber-200 bg-white px-3 py-2 pr-9 text-sm text-slate-800 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                @change="autoSubmit"
                aria-label="Filtrar por estado"
              >
                <option value="">Todos los estados</option>
                <option v-for="e in estados" :key="e" :value="e">
                  {{ e.replace('_', ' ') }}
                </option>
              </select>
              <svg
                class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-amber-500"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>

            <!-- Asignación -->
            <div class="relative">
              <select
                name="asignado"
                v-model="filtroAsignado"
                class="w-[200px] appearance-none rounded-2xl border border-amber-200 bg-white px-3 py-2 pr-9 text-sm text-slate-800 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                @change="autoSubmit"
                aria-label="Filtrar por asignación"
              >
                <option value="">Asignados: todos</option>
                <option value="none">Sin asignar</option>
                <option value="any">Asignados</option>
              </select>
              <svg
                class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-amber-500"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>

            <button
              type="submit"
              class="rounded-2xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600"
            >
              Filtrar
            </button>
          </form>
        </div>

        <!-- LISTA / TABLA -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-amber-100">
          <!-- barra superior -->
          <div class="border-b border-amber-100 bg-gradient-to-r from-amber-50 via-amber-50/80 to-white px-4 py-2.5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-amber-900">
                <span class="h-6 w-6 flex items-center justify-center rounded-full bg-white/90 text-[13px] shadow-sm">
                  📋
                </span>
                <span>Listado de pedidos</span>
              </div>
              <p class="hidden text-[11px] text-amber-900/80 md:block">
                Revisa estado, asignación y total de cada pedido.
              </p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0 text-sm">
              <thead>
                <tr class="bg-slate-900 text-[11px] uppercase tracking-wide text-slate-100">
                  <th class="px-3 py-2 text-left font-semibold first:rounded-tl-2xl">#</th>
                  <th class="px-3 py-2 text-left font-semibold">Folio</th>
                  <th class="px-3 py-2 text-left font-semibold">Estado</th>
                  <th class="px-3 py-2 text-left font-semibold">Tipo</th>
                  <th class="px-3 py-2 text-left font-semibold">Items</th>
                  <th class="px-3 py-2 text-left font-semibold">Asignado a</th>
                  <th class="px-3 py-2 text-left font-semibold">Total</th>
                  <th class="px-3 py-2 text-right font-semibold last:rounded-tr-2xl">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(p, idx) in pedidos.data"
                  :key="p.id"
                  class="align-middle text-[13px] text-slate-800 transition-colors hover:bg-amber-50/80 border-b border-slate-100 last:border-b-0"
                  :class="idx % 2 === 0 ? 'bg-white' : 'bg-amber-50/40'"
                >
                  <td class="px-3 py-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200">
                      <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
                      <span>{{ p.id }}</span>
                    </span>
                  </td>

                  <td class="px-3 py-2 font-semibold text-slate-900">
                    {{ p.folio ?? '—' }}
                  </td>

                  <td class="px-3 py-2">
                    <span
                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium capitalize ring-1"
                      :class="{
                        'bg-rose-50 text-rose-800 ring-rose-200': p.estado === 'cancelado',
                        'bg-emerald-50 text-emerald-800 ring-emerald-200': p.estado === 'entregado',
                        'bg-amber-50 text-amber-800 ring-amber-200': p.estado === 'preparando' || p.estado === 'listo' || p.estado === 'pendiente',
                        'bg-indigo-50 text-indigo-800 ring-indigo-200': p.estado === 'en_camino',
                        'bg-slate-50 text-slate-700 ring-slate-200': !['cancelado','entregado','preparando','listo','en_camino','pendiente'].includes(p.estado),
                      }"
                    >
                      {{ p.estado.replace('_', ' ') }}
                    </span>
                  </td>

                  <td class="px-3 py-2 capitalize text-slate-700">
                    {{ p.tipo?.replace('_', ' ') }}
                  </td>

                  <td class="px-3 py-2 text-slate-700">
                    {{ p.items }}
                  </td>

                  <td class="px-3 py-2">
                    <span
                      v-if="p.repartidor"
                      class="inline-flex rounded-full bg-indigo-50 text-indigo-800 text-[11px] px-2.5 py-0.5 ring-1 ring-indigo-200"
                    >
                      {{ p.repartidor }}
                    </span>
                    <span
                      v-else
                      class="inline-flex rounded-full bg-slate-50 text-slate-500 text-[11px] px-2.5 py-0.5 ring-1 ring-slate-200"
                    >
                      Sin asignar
                    </span>
                  </td>

                  <td class="px-3 py-2 font-semibold text-emerald-700">
                    {{ money(p.total) }}
                  </td>

                  <td class="px-3 py-2 text-right whitespace-nowrap">
                    <Link
                      :href="`/${base}/pedidos/${p.id}`"
                      class="inline-flex items-center gap-1 rounded-full bg-amber-500/90 px-3 py-1 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-600"
                    >
                      Ver detalle
                      <span>→</span>
                    </Link>
                  </td>
                </tr>

                <tr v-if="!pedidos.data?.length">
                  <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500 bg-white">
                    Sin resultados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- PAGINACIÓN -->
        <div v-if="pedidos.links?.length" class="flex justify-end gap-2">
          <Link
            v-for="l in pedidos.links"
            :key="l.url + l.label"
            :href="l.url || '#'"
            v-html="l.label"
            :class="[
              'min-w-9 select-none rounded-full border px-3 py-1.5 text-center text-xs font-semibold transition',
              l.active
                ? 'border-amber-600 bg-amber-600 text-white shadow-sm shadow-amber-300/80'
                : 'border-amber-100 bg-white text-slate-700 hover:bg-amber-50',
              !l.url && 'pointer-events-none opacity-40',
            ]"
            preserve-scroll
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
