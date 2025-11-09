<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps({
  productos: Object,  // paginator { data, links, from, ... }
  filters:   Object,  // { q, filtro }
})

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

const money = (n) =>
  new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n ?? 0)

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
    'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-300',
    variant === 'solid'
      ? 'bg-indigo-600 text-white hover:bg-indigo-700'
      : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
  ].join(' ')
</script>

<template>
  <Head title="Inventario" />

  <AuthenticatedLayout>
    <!-- ===== Header unificado (barra bonita) ===== -->
    <template #header>
      <div
        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <div class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
            📦
          </div>
          <div>
            <h2 class="text-2xl font-semibold text-gray-800">Inventario</h2>
            <p class="mt-0.5 text-sm text-gray-500">Control de existencias y mínimos por producto.</p>
          </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="hidden md:flex items-center gap-2">
          <a :href="route('admin.inventario.export.csv')" :class="btn('outline')">CSV</a>
          <a :href="route('admin.inventario.export.pdf')" target="_blank" :class="btn('solid')">PDF</a>
          <Link :href="route('admin.dashboard')" class="text-sm text-indigo-600 hover:underline">
            ← Volver al panel
          </Link>
        </div>
      </div>
    </template>

    <!-- ===== Contenido ===== -->
    <div class="max-w-7xl mx-auto p-6">
      <!-- Avisos -->
      <div v-if="flash?.success" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-green-800">
        {{ flash.success }}
      </div>
      <div v-if="flash?.error" class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-rose-800">
        {{ flash.error }}
      </div>

      <!-- Filtros -->
      <div class="mb-3 flex flex-wrap items-center gap-3">
        <input
          v-model="q"
          type="text"
          placeholder="🔎 Buscar producto…"
          class="w-full md:w-80 rounded-lg border-gray-300 px-4 py-2 focus:ring-indigo-400"
        />

        <select v-model="filtro" class="rounded-lg border-gray-300 px-3 py-2 focus:ring-indigo-400">
          <option value="">Todos</option>
          <option value="bajo_minimo">Sólo bajo mínimo</option>
        </select>
      </div>

      <!-- Acciones de exportación (para móviles / refuerzo) -->
      <div class="mb-5 flex flex-wrap items-center gap-2 md:hidden">
        <a :href="route('admin.inventario.export.pdf')" target="_blank" :class="btn('solid')">PDF</a>
        <a :href="route('admin.inventario.export.csv')" :class="btn('outline')">CSV</a>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">#</th>
              <th class="px-4 py-3 text-left">Producto</th>
              <th class="px-4 py-3 text-left">Precio</th>
              <th class="px-4 py-3 text-left">Stock actual</th>
              <th class="px-4 py-3 text-left">Stock mínimo</th>
              <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(p, i) in props.productos.data"
              :key="p.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-4 py-3 text-gray-600">{{ (props.productos.from ?? 1) + i }}</td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ p.nombre }}</td>
              <td class="px-4 py-3">{{ money(p.precio) }}</td>

              <!-- Stock actual con alerta si <= mínimo -->
              <td class="px-4 py-3">
                <span
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-semibold',
                    (p.stock_actual ?? 0) <= (p.stock_minimo ?? 0)
                      ? 'bg-rose-100 text-rose-700'
                      : 'bg-emerald-100 text-emerald-700'
                  ]"
                >{{ p.stock_actual ?? 0 }}</span>
                <span
                  v-if="(p.stock_actual ?? 0) <= (p.stock_minimo ?? 0)"
                  class="ml-1 text-xs text-rose-600"
                >
                  ⚠ Bajo
                </span>
              </td>

              <!-- Stock mínimo (inline edit) -->
              <td class="px-4 py-3">
                <div v-if="editandoId === p.id" class="flex items-center gap-2">
                  <input
                    v-model.number="editMin.stock_minimo"
                    type="number"
                    min="0"
                    class="w-24 rounded-md border-gray-300 focus:ring-indigo-300"
                  />
                  <button
                    class="px-2 py-1 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 text-xs"
                    :disabled="editMin.processing"
                    @click="saveEditMin(p)"
                  >Guardar</button>
                  <button class="px-2 py-1 rounded-md border text-xs" @click="cancelEditMin">Cancelar</button>
                  <div v-if="editMin.errors.stock_minimo" class="text-xs text-rose-600">
                    {{ editMin.errors.stock_minimo }}
                  </div>
                </div>
                <div v-else class="flex items-center gap-2">
                  <span class="text-gray-700">{{ p.stock_minimo ?? 0 }}</span>
                  <button class="text-indigo-600 hover:underline text-xs" @click="startEditMin(p)">Editar</button>
                </div>
              </td>

              <!-- Acciones -->
              <td class="px-4 py-3 text-right">
                <button
                  class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-700"
                  @click="abrirMovimiento(p)"
                >
                  + Movimiento
                </button>

                <Link
                  :href="route('admin.inventario.movimientos', p.id)"
                  class="ml-2 inline-flex items-center gap-2 rounded-md px-3 py-1.5 border text-indigo-700 hover:bg-indigo-50"
                >
                  Historial
                </Link>
              </td>
            </tr>

            <tr v-if="props.productos.data.length === 0">
              <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                No hay productos para mostrar.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-5 flex justify-end gap-2">
        <Link
          v-for="(lnk, i) in props.productos.links"
          :key="i"
          :href="lnk.url || '#'"
          v-html="lnk.label"
          :class="[
            'px-3 py-1 rounded-md border text-sm',
            lnk.active
              ? 'bg-indigo-600 text-white border-indigo-600'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-indigo-50',
            !lnk.url && 'pointer-events-none opacity-40'
          ]"
        />
      </div>
    </div>

    <!-- Modal Movimiento -->
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      @keydown.esc="cerrar"
    >
      <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-5 py-3">
          <h3 class="text-lg font-semibold text-gray-800">Registrar movimiento</h3>
          <button class="rounded-full p-1 text-gray-500 hover:bg-gray-100" @click="cerrar">✕</button>
        </div>

        <div class="px-5 py-4 space-y-3">
          <div class="text-sm text-gray-600">
            Producto: <span class="font-semibold text-gray-800">{{ seleccionado?.nombre }}</span>
            <span class="ml-2 text-xs text-gray-500">Stock actual: {{ seleccionado?.stock_actual ?? 0 }}</span>
          </div>

          <div>
            <label class="text-sm text-gray-600 mb-1 block">Tipo de movimiento</label>
            <select v-model="formMov.tipo" class="w-full rounded-md border-gray-300 focus:ring-indigo-300">
              <option value="entrada">Entrada</option>
              <option value="salida">Salida</option>
              <option value="ajuste">Ajuste (fija nuevo stock)</option>
            </select>
            <p v-if="formMov.errors.tipo" class="text-sm text-rose-600 mt-1">{{ formMov.errors.tipo }}</p>
          </div>

          <div>
            <label class="text-sm text-gray-600 mb-1 block">
              Cantidad <span class="text-xs text-gray-400" v-if="formMov.tipo==='ajuste'">(nuevo stock)</span>
            </label>
            <input
              v-model.number="formMov.cantidad"
              type="number"
              min="0"
              class="w-full rounded-md border-gray-300 focus:ring-indigo-300"
            />
            <p v-if="formMov.errors.cantidad" class="text-sm text-rose-600 mt-1">{{ formMov.errors.cantidad }}</p>
          </div>

          <div>
            <label class="text-sm text-gray-600 mb-1 block">Motivo (opcional)</label>
            <input
              v-model="formMov.motivo"
              type="text"
              maxlength="255"
              class="w-full rounded-md border-gray-300 focus:ring-indigo-300"
              placeholder="Compra, merma, ajuste por conteo, etc."
            />
            <p v-if="formMov.errors.motivo" class="text-sm text-rose-600 mt-1">{{ formMov.errors.motivo }}</p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t px-5 py-3">
          <button class="px-4 py-2 rounded-md border" @click="cerrar">Cancelar</button>
          <button
            class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60"
            :disabled="formMov.processing"
            @click="guardarMovimiento"
          >
            {{ formMov.processing ? 'Guardando...' : 'Registrar' }}
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
