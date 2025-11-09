<!-- resources/js/Pages/Vendedor/Pedidos/Create.vue -->
<script setup>
import VendedorLayout from '@/Layouts/VendedorLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  clientes:  { type: Array,  required: true }, // [{id, nombre, telefono, email}]
  productos: { type: Array,  required: true }, // [{id, nombre, precio, stock}]
})

/* ---------- Estado del form ---------- */
const cliente_id    = ref('')
const tipo_entrega  = ref('mostrador') // mostrador|domicilio
const observaciones = ref('')
const items         = ref([]) // [{producto_id, cantidad}]

/* ---------- Agregar/Eliminar items ---------- */
const productoSel = ref('')
const cantidadSel = ref(1)

function addItem() {
  const pid = Number(productoSel.value)
  const qty = Number(cantidadSel.value)
  if (!pid || qty < 1) return

  const existe = items.value.find(i => Number(i.producto_id) === pid)
  if (existe) {
    existe.cantidad += qty
  } else {
    items.value.push({ producto_id: pid, cantidad: qty })
  }
  productoSel.value = ''
  cantidadSel.value = 1
}

function removeItem(pid) {
  items.value = items.value.filter(i => Number(i.producto_id) !== Number(pid))
}

const mapaProductos = computed(() => {
  const m = new Map()
  for (const p of props.productos) m.set(Number(p.id), p)
  return m
})

const detalle = computed(() => {
  return items.value.map(i => {
    const p = mapaProductos.value.get(Number(i.producto_id))
    const precio   = p ? Number(p.precio) : 0
    const subtotal = precio * Number(i.cantidad)
    return {
      ...i,
      nombre: p?.nombre ?? `#${i.producto_id}`,
      precio, subtotal, stock: p?.stock ?? 0
    }
  })
})

const total = computed(() => detalle.value.reduce((s, r) => s + r.subtotal, 0))

/* ---------- Submit ---------- */
const processing = ref(false)
function submit() {
  if (!cliente_id.value) return alert('Selecciona un cliente.')
  if (!items.value.length) return alert('Agrega al menos un producto.')

  processing.value = true
  router.post(route('vendedor.pedidos.store'), {
    cliente_id:   Number(cliente_id.value),
    tipo_entrega: tipo_entrega.value,
    observaciones: observaciones.value || null,
    items: items.value.map(i => ({
      producto_id: Number(i.producto_id),
      cantidad:    Number(i.cantidad),
    })),
  }, { onFinish: () => (processing.value = false) })
}

const money = n => new Intl.NumberFormat('es-MX', {style:'currency',currency:'MXN'}).format(n ?? 0)

/* ---------- Modal: Alta rápida de cliente ---------- */
const showCliente = ref(false)
const nuevo = ref({ nombre:'', telefono:'', email:'' })
async function guardarCliente() {
  if (!nuevo.value.nombre.trim()) { alert('Nombre es requerido'); return }
  const res = await fetch(route('vendedor.clientes.rapido.store'), {
    method: 'POST',
    headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content },
    body: JSON.stringify(nuevo.value),
  })
  const json = await res.json()
  if (!json?.ok) { alert('No se pudo guardar'); return }
  // Agregar al combo y seleccionar
  props.clientes.push(json.cliente)
  cliente_id.value = json.cliente.id
  showCliente.value = false
  nuevo.value = { nombre:'', telefono:'', email:'' }
}
</script>

<template>
  <VendedorLayout>
    <template #header>
      <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold text-gray-900">Nuevo pedido (mostrador)</h1>
        <Link :href="route('vendedor.pedidos.index')" class="text-indigo-600 hover:text-indigo-700">← Volver a pedidos</Link>
      </div>
      <p class="mt-1 text-sm text-gray-500">El stock se valida ahora, pero se descuenta cuando el pedido pasa a <em>listo</em> o <em>entregado</em>.</p>
    </template>

    <div class="mx-auto max-w-6xl p-6 space-y-8">
      <!-- Cliente y tipo -->
      <section class="rounded-2xl border bg-white p-5 space-y-4">
        <div class="flex flex-wrap items-end gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Cliente</label>
            <select v-model="cliente_id" class="w-72 rounded-lg border px-3 py-2 text-sm">
              <option value="">— Selecciona —</option>
              <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }} <span v-if="c.telefono">({{ c.telefono }})</span></option>
            </select>
          </div>

          <button type="button" class="rounded-lg border px-3 py-2 text-sm hover:bg-gray-50"
                  @click="showCliente = true">+ Agregar cliente rápido</button>

          <div class="ml-auto">
            <label class="block text-sm text-gray-600 mb-1">Tipo de entrega</label>
            <select v-model="tipo_entrega" class="rounded-lg border px-3 py-2 text-sm">
              <option value="mostrador">Mostrador</option>
              <option value="domicilio">Domicilio</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Observaciones</label>
          <input v-model="observaciones" type="text" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Notas del pedido (opcional)">
        </div>
      </section>

      <!-- Items -->
      <section class="rounded-2xl border bg-white p-5 space-y-4">
        <div class="flex flex-wrap items-end gap-3">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Producto</label>
            <select v-model="productoSel" class="w-80 rounded-lg border px-3 py-2 text-sm">
              <option value="">— Selecciona —</option>
              <option v-for="p in productos" :key="p.id" :value="p.id">
                {{ p.nombre }} — {{ money(p.precio) }} (Stock: {{ p.stock }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Cantidad</label>
            <input v-model.number="cantidadSel" type="number" min="1" class="w-24 rounded-lg border px-3 py-2 text-sm">
          </div>
          <button type="button" class="h-9 rounded-lg bg-indigo-600 px-4 text-white hover:bg-indigo-700"
                  @click="addItem">Agregar</button>
        </div>

        <div class="overflow-hidden rounded-xl border">
          <table class="w-full text-sm">
            <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
              <tr>
                <th class="px-4 py-3 text-left">Producto</th>
                <th class="px-4 py-3 text-left">Cantidad</th>
                <th class="px-4 py-3 text-left">Precio</th>
                <th class="px-4 py-3 text-left">Subtotal</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in detalle" :key="r.producto_id" class="hover:bg-gray-50">
                <td class="px-4 py-3">{{ r.nombre }}</td>
                <td class="px-4 py-3">
                  <input v-model.number="r.cantidad" type="number" min="1" class="w-24 rounded-lg border px-2 py-1 text-sm">
                  <span class="ml-2 text-xs text-gray-500">Stock: {{ r.stock }}</span>
                </td>
                <td class="px-4 py-3">{{ money(r.precio) }}</td>
                <td class="px-4 py-3 font-medium">{{ money(r.subtotal) }}</td>
                <td class="px-4 py-3 text-right">
                  <button class="text-rose-600 hover:text-rose-700" @click="removeItem(r.producto_id)">Quitar</button>
                </td>
              </tr>
              <tr v-if="!detalle.length">
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin productos.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex items-center justify-end gap-4">
          <div class="text-right">
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-semibold">{{ money(total) }}</p>
          </div>
          <button :disabled="processing" @click="submit"
                  class="h-10 rounded-lg bg-green-600 px-5 text-white hover:bg-green-700 disabled:opacity-50">
            Guardar pedido
          </button>
        </div>
      </section>
    </div>

    <!-- Modal: Cliente rápido -->
    <div v-if="showCliente" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-5">
        <h3 class="text-lg font-semibold mb-3">Nuevo cliente</h3>

        <div class="space-y-3">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Nombre *</label>
            <input v-model="nuevo.nombre" type="text" class="w-full rounded-lg border px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Teléfono</label>
            <input v-model="nuevo.telefono" type="text" class="w-full rounded-lg border px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Email</label>
            <input v-model="nuevo.email" type="email" class="w-full rounded-lg border px-3 py-2 text-sm">
          </div>
        </div>

        <div class="mt-5 flex justify-end gap-2">
          <button class="rounded-lg border px-4 py-2 text-sm" @click="showCliente=false">Cancelar</button>
          <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white" @click="guardarCliente">Guardar</button>
        </div>
      </div>
    </div>
  </VendedorLayout>
</template>
