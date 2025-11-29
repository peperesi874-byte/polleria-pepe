<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps({
  productos: Object,  // paginator { data, links, from, ... }
  filters:   Object,  // { q, filtro }
})

/* ---------------- Datos base ---------------- */
const filas = computed(() => props.productos?.data ?? [])

/* ---------------- Stats tipo “tarjetas del panel” ---------------- */
const stats = computed(() => {
  let total = 0
  let bajoMin = 0
  let sinStock = 0
  let valor = 0

  filas.value.forEach(p => {
    total++
    const actual = Number(p.stock_actual ?? 0)
    const minimo = Number(p.stock_minimo ?? 0)
    const precio = Number(p.precio ?? 0)

    if (actual <= minimo) bajoMin++
    if (actual === 0) sinStock++
    valor += actual * precio
  })

  return { total, bajoMin, sinStock, valor }
})

const money = (n) =>
  new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n ?? 0)

/* ---------------- Filtros ---------------- */
const q      = ref(props.filters?.q ?? '')
const filtro = ref(props.filters?.filtro ?? '') // '', 'bajo_minimo'

let t = null
watch([q, filtro], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('admin.inventario.index'),
      { q: q.value, filtro: filtro.value },
      { preserveState: true, replace: true }
    )
  }, 300)
})

/* ---------------- Modal Movimiento ---------------- */
const show = ref(false)
const seleccionado = ref(null) // { id, nombre, stock_actual, stock_minimo }

const formMov = useForm({
  producto_id: null,
  tipo: 'entrada',                // 'entrada' | 'salida' | 'ajuste'
  cantidad: 0,                    // en 'ajuste' = nuevo stock (absoluto)
  motivo: '',
})

function abrirMovimiento(row) {
  seleccionado.value = row
  formMov.reset()
  formMov.clearErrors()
  formMov.producto_id = row.id
  formMov.tipo = 'entrada'
  formMov.cantidad = 0
  formMov.motivo = ''
  show.value = true
}

function cerrar() { show.value = false }

function guardarMovimiento() {
  formMov.post(route('admin.inventario.movimiento'), {
    preserveScroll: true,
    onSuccess: () => {
      show.value = false
      router.reload({ only: ['productos'] })
    }
  })
}

/* ---------------- Edición de stock mínimo (inline) ---------------- */
const editMin = useForm({ stock_minimo: 0 })
const editandoId = ref(null)

function startEditMin(row) {
  editandoId.value = row.id
  editMin.stock_minimo = Number(row.stock_minimo ?? 0)
}

function cancelEditMin() {
  editandoId.value = null
  editMin.reset()
  editMin.clearErrors()
}

function saveEditMin(row) {
  editMin.transform(d => ({ ...d, _method: 'put' }))
  editMin.post(route('admin.inventario.minimo', row.id), {
    preserveScroll: true,
    onSuccess: () => {
      editandoId.value = null
      router.reload({ only: ['productos'] })
    },
    onFinish: () => {
      editMin.transform(d => d)
    }
  })
}

/* ---------------- Flash ---------------- */
const page  = usePage()
const flash = computed(() => page.props.flash ?? {})

/* ---------------- UI helpers ---------------- */
const btn = (variant = 'outline') =>
  [
    'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold transition focus:outline-none focus:ring-2 focus:ring-amber-300',
    variant === 'solid'
      ? 'bg-amber-600 text-white hover:bg-amber-700 shadow-sm shadow-amber-300/70'
      : 'border border-amber-200 bg-white text-amber-800 hover:bg-amber-50'
  ].join(' ')
</script>

<template>
  <Head title="Inventario" />

  <AuthenticatedLayout>
    <!-- ===== HERO / HEADER NARANJA TIPO PANEL PRINCIPAL ===== -->
    <template #header>
  <div class="pt-4 pb-6">
    <div
      class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
    >
      <!-- blobs decorativos -->
      <div class="pointer-events-none absolute -left-20 -top-24 h-40 w-40 rounded-full bg-white/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -right-10 bottom-0 h-40 w-44 rounded-full bg-black/20 blur-3xl"></div>

      <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <!-- IZQUIERDA -->
        <div class="space-y-3">
          <!-- etiqueta superior -->
          <div
            class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
          >
            <span class="text-lg">📦</span>
            <span>Panel de inventario — Pollería Pepe</span>
          </div>

          <!-- título + descripción -->
          <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-white drop-shadow">
              Controla tu inventario en tiempo real
            </h2>
            <p class="mt-1 text-sm text-amber-50/90">
              Revisa existencias, ajusta mínimos y registra movimientos para no perder ninguna venta.
            </p>
          </div>

          <!-- chips -->
          <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-amber-50/95">
            <span
              class="inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 font-semibold text-amber-900 shadow-sm"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
              Panel actualizado
            </span>

            <span class="inline-flex items-center gap-1 rounded-full bg-black/15 px-2.5 py-1">
              Entrada · salida · ajuste en un solo lugar
            </span>
          </div>
        </div>

        <!-- DERECHA -->
        <div class="flex flex-col items-end gap-3">
          <!-- Accesos rápidos -->
          <div class="hidden md:flex gap-2">
            <a
              :href="route('admin.inventario.export.csv')"
              class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold text-white backdrop-blur hover:bg-white/20"
            >
              CSV
            </a>
            <a
              :href="route('admin.inventario.export.pdf')"
              target="_blank"
              class="inline-flex items-center gap-2 rounded-xl bg-white/90 px-4 py-2 text-xs font-semibold text-amber-900 shadow-sm hover:bg-white"
            >
              PDF
            </a>
          </div>

          <!-- volver -->
          <Link
            :href="route('admin.dashboard')"
            class="inline-flex items-center gap-1 rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-amber-50 hover:bg-black/30"
          >
            ← Volver al panel
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>


    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/10 to-slate-50">
      <div class="mx-auto max-w-7xl px-4 py-7 space-y-6 lg:px-6">

        <!-- Avisos -->
        <div
          v-if="flash?.success"
          class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs text-emerald-900 shadow-sm"
        >
          <span>✅</span>
          <span>{{ flash.success }}</span>
        </div>
        <div
          v-if="flash?.error"
          class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-900 shadow-sm"
        >
          <span>⚠️</span>
          <span>{{ flash.error }}</span>
        </div>

        <!-- Tarjetas resumen tipo panel -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            class="rounded-3xl border border-yellow-100 bg-gradient-to-b from-yellow-50 to-amber-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-[11px] font-semibold text-amber-700 uppercase tracking-wide">
                Productos
              </p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-amber-400 text-white shadow-md"
              >
                🧺
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">
              {{ stats.total }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Registrados en catálogo.
            </p>
          </div>

          <div
            class="rounded-3xl border border-rose-100 bg-gradient-to-b from-rose-50 to-pink-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-[11px] font-semibold text-rose-700 uppercase tracking-wide">
                Bajo mínimo
              </p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-rose-400 text-white shadow-md"
              >
                ⚠️
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">
              {{ stats.bajoMin }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Productos que necesitan atención.
            </p>
          </div>

          <div
            class="rounded-3xl border border-sky-100 bg-gradient-to-b from-sky-50 to-blue-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-[11px] font-semibold text-sky-700 uppercase tracking-wide">
                Sin stock
              </p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-sky-400 text-white shadow-md"
              >
                🧊
              </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900">
              {{ stats.sinStock }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Productos agotados.
            </p>
          </div>

          <div
            class="rounded-3xl border border-emerald-100 bg-gradient-to-b from-emerald-50 to-emerald-50 px-4 py-4 shadow-sm"
          >
            <div class="flex items-center justify-between">
              <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">
                Valor estimado
              </p>
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-emerald-400 text-white shadow-md"
              >
                💲
              </div>
            </div>
            <p class="mt-3 text-xl font-bold text-slate-900">
              {{ money(stats.valor) }}
            </p>
            <p class="mt-1 text-xs text-slate-600">
              Con base en stock actual x precio.
            </p>
          </div>
        </div>

        <!-- Filtros -->
        <div
          class="flex flex-col gap-3 rounded-2xl bg-white/95 px-4 py-3 shadow-sm ring-1 ring-amber-100 md:flex-row md:items-center md:justify-between"
        >
          <div>
            <p class="flex items-center gap-2 text-sm font-semibold text-slate-900">
              <span class="text-lg">🔎</span>
              Filtros de inventario
            </p>
            <p class="text-xs text-slate-500">
              Busca por nombre y enfócate sólo en productos bajo mínimo si lo necesitas.
            </p>
          </div>

          <div class="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-center">
            <div class="relative w-full md:w-72">
              <input
                v-model="q"
                type="text"
                placeholder="Buscar producto…"
                class="w-full rounded-2xl border border-amber-200 bg-amber-50/40 px-4 py-2 pr-9 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
              <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-amber-400 text-sm">
                🔍
              </span>
            </div>

            <select
              v-model="filtro"
              class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80 md:w-48"
            >
              <option value="">Todos los productos</option>
              <option value="bajo_minimo">Sólo bajo mínimo</option>
            </select>
          </div>
        </div>

        <!-- Acciones para móvil -->
        <div class="flex flex-wrap items-center gap-2 md:hidden">
          <a :href="route('admin.inventario.export.pdf')" target="_blank" :class="btn('solid')">PDF</a>
          <a :href="route('admin.inventario.export.csv')" :class="btn('outline')">CSV</a>
        </div>

        <!-- CONTENEDOR PASTEL + TABLA ESTILO “ÚLTIMOS PEDIDOS” -->
        <div
          class="overflow-hidden rounded-[28px] border border-amber-100 bg-gradient-to-b from-amber-50 via-rose-50/40 to-orange-50 shadow-[0_20px_45px_rgba(15,23,42,0.08)]"
        >
          <!-- Encabezado bloque -->
          <div class="flex items-center justify-between px-5 pt-4 pb-3">
            <div class="flex items-center gap-2">
              <div
                class="grid h-8 w-8 place-items-center rounded-full bg-amber-400 text-white shadow-md"
              >
                📊
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-900">
                  Estado del inventario
                </p>
                <p class="text-[11px] text-slate-600">
                  Seguimiento de existencias por producto.
                </p>
              </div>
            </div>

            <div class="text-right text-[11px] text-slate-500">
              <p class="font-semibold text-slate-800">
                {{ stats.total }} producto{{ stats.total === 1 ? '' : 's' }}
              </p>
              <p v-if="filtro === 'bajo_minimo'" class="text-[10px] text-rose-500 font-semibold">
                Mostrando sólo bajo mínimo
              </p>
            </div>
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
              <thead>
                <tr class="bg-slate-950 text-[11px] uppercase tracking-wide text-white">
                  <th class="px-3 py-2 text-left w-[60px]">#</th>
                  <th class="px-3 py-2 text-left">Producto</th>
                  <th class="px-3 py-2 text-left">Precio</th>
                  <th class="px-3 py-2 text-center">Stock actual</th>
                  <th class="px-3 py-2 text-center">Stock mínimo</th>
                  <th class="px-3 py-2 text-right w-[210px]">Acciones</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(p, i) in filas"
                  :key="p.id"
                  class="group align-middle text-[13px] text-slate-800 transition-all duration-150 ease-out border-t border-amber-100"
                  :class="[
                    i % 2 === 0 ? 'bg-white' : 'bg-amber-50/60',
                    'hover:bg-amber-50/95 hover:shadow-md hover:-translate-y-[1px]'
                  ]"
                >
                  <!-- # -->
                  <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-500">
                    <span
                      class="inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 ring-1 ring-slate-200 group-hover:ring-amber-400"
                    >
                      <span
                        class="h-1.5 w-1.5 rounded-full transition-colors"
                        :class="(p.stock_actual ?? 0) <= (p.stock_minimo ?? 0) ? 'bg-rose-500' : 'bg-emerald-500'"
                      />
                      <span>{{ (props.productos.from ?? 1) + i }}</span>
                    </span>
                  </td>

                  <!-- Producto -->
                  <td class="px-3 py-2">
                    <p class="font-semibold text-slate-900">
                      {{ p.nombre }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-slate-500">
                      {{ p.categoria_nombre ?? p.categoria ?? 'Sin categoría' }}
                    </p>
                  </td>

                  <!-- Precio -->
                  <td class="px-3 py-2">
                    <span
                      class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200"
                    >
                      💲 {{ money(p.precio) }}
                    </span>
                  </td>

                  <!-- Stock actual -->
                  <td class="px-3 py-2 text-center">
                    <span
                      class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 transition-colors"
                      :class="(p.stock_actual ?? 0) <= (p.stock_minimo ?? 0)
                        ? 'bg-rose-50 text-rose-700 ring-rose-200'
                        : 'bg-emerald-50 text-emerald-700 ring-emerald-200'"
                    >
                      {{ p.stock_actual ?? 0 }}
                    </span>
                    <span
                      v-if="(p.stock_actual ?? 0) <= (p.stock_minimo ?? 0)"
                      class="ml-1 text-[11px] text-rose-600"
                    >
                      ⚠ Bajo
                    </span>
                  </td>

                  <!-- Stock mínimo (inline edit) -->
                  <td class="px-3 py-2 text-center">
                    <div v-if="editandoId === p.id" class="flex items-center justify-center gap-2">
                      <input
                        v-model.number="editMin.stock_minimo"
                        type="number"
                        min="0"
                        class="w-20 rounded-lg border border-amber-200 bg-white px-2 py-1 text-xs text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                      />
                      <button
                        class="rounded-full bg-emerald-600 px-3 py-1 text-[11px] font-semibold text-white hover:bg-emerald-700 disabled:opacity-60"
                        :disabled="editMin.processing"
                        @click="saveEditMin(p)"
                      >
                        Guardar
                      </button>
                      <button
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                        @click="cancelEditMin"
                      >
                        Cancelar
                      </button>
                    </div>

                    <div v-else class="flex items-center justify-center gap-2">
                      <span
                        class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200"
                      >
                        {{ p.stock_minimo ?? 0 }}
                      </span>
                      <button
                        class="text-[11px] font-semibold text-amber-700 hover:underline"
                        @click="startEditMin(p)"
                      >
                        Editar
                      </button>
                    </div>

                    <div v-if="editMin.errors.stock_minimo" class="mt-1 text-[11px] text-rose-600">
                      {{ editMin.errors.stock_minimo }}
                    </div>
                  </td>

                  <!-- Acciones -->
                  <td class="px-3 py-2 text-right whitespace-nowrap">
                    <button
                      class="inline-flex items-center gap-1 rounded-full bg-amber-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm hover:bg-amber-700"
                      @click="abrirMovimiento(p)"
                    >
                      <span>＋</span>
                      <span>Movimiento</span>
                    </button>

                    <Link
                      :href="route('admin.inventario.movimientos', p.id)"
                      class="ml-2 inline-flex items-center gap-1 rounded-full border border-amber-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-amber-800 hover:bg-amber-50"
                    >
                      📜 <span>Historial</span>
                    </Link>
                  </td>
                </tr>

                <tr v-if="filas.length === 0">
                  <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                    No hay productos para mostrar.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginación -->
        <div class="flex justify-end gap-2">
          <Link
            v-for="(lnk, i) in props.productos.links"
            :key="i"
            :href="lnk.url || '#'"
            v-html="lnk.label"
            :class="[
              'min-w-9 select-none rounded-full border px-3 py-1.5 text-center text-xs font-semibold transition',
              lnk.active
                ? 'border-slate-900 bg-slate-900 text-amber-200 shadow-sm'
                : 'border-amber-100 bg-white text-slate-700 hover:border-slate-900 hover:bg-amber-50',
              !lnk.url && 'pointer-events-none opacity-40',
            ]"
          />
        </div>
      </div>
    </div>

    <!-- Modal Movimiento -->
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @keydown.esc="cerrar"
    >
      <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-amber-100">
        <!-- Header modal -->
        <div class="border-b border-amber-100 bg-gradient-to-r from-amber-500 to-rose-500 px-5 py-3 text-amber-50">
          <div class="flex items-center justify-between gap-2">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-100">
                Movimiento de inventario
              </p>
              <h3 class="text-sm font-semibold text-white">
                {{ seleccionado?.nombre }}
              </h3>
            </div>
            <button
              class="rounded-full bg-black/20 p-1 text-amber-50 hover:bg-black/30"
              @click="cerrar"
            >
              ✕
            </button>
          </div>
        </div>

        <div class="space-y-3 px-5 py-4 text-sm">
          <div class="flex items-center justify-between text-[12px] text-slate-600">
            <span>
              Stock actual:
              <span class="font-semibold text-slate-900">{{ seleccionado?.stock_actual ?? 0 }}</span>
            </span>
            <span class="text-[11px] text-slate-500">
              Mínimo: {{ seleccionado?.stock_minimo ?? 0 }}
            </span>
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
              Tipo de movimiento
            </label>
            <select
              v-model="formMov.tipo"
              class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
            >
              <option value="entrada">Entrada</option>
              <option value="salida">Salida</option>
              <option value="ajuste">Ajuste (nuevo stock)</option>
            </select>
            <p v-if="formMov.errors.tipo" class="mt-1 text-xs text-rose-600">{{ formMov.errors.tipo }}</p>
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
              Cantidad
              <span v-if="formMov.tipo === 'ajuste'" class="text-[11px] font-normal text-amber-500">
                (nuevo stock total)
              </span>
            </label>
            <input
              v-model.number="formMov.cantidad"
              type="number"
              min="0"
              class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
            />
            <p v-if="formMov.errors.cantidad" class="mt-1 text-xs text-rose-600">{{ formMov.errors.cantidad }}</p>
          </div>

          <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
              Motivo (opcional)
            </label>
            <input
              v-model="formMov.motivo"
              type="text"
              maxlength="255"
              class="w-full rounded-2xl border border-amber-200 bg-amber-50/40 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              placeholder="Compra, merma, ajuste por conteo, etc."
            />
            <p v-if="formMov.errors.motivo" class="mt-1 text-xs text-rose-600">{{ formMov.errors.motivo }}</p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-amber-100 bg-amber-50/60 px-5 py-3">
          <button
            class="rounded-full border border-amber-200 bg-white px-4 py-1.5 text-xs font-medium text-amber-800 hover:bg-amber-50"
            @click="cerrar"
          >
            Cancelar
          </button>
          <button
            class="rounded-full bg-amber-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700 disabled:opacity-60"
            :disabled="formMov.processing"
            @click="guardarMovimiento"
          >
            {{ formMov.processing ? 'Guardando…' : 'Registrar movimiento' }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
