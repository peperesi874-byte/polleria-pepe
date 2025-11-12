<script setup>
import VendedorLayout from '@/Layouts/VendedorLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  clientes:  { type: Array,  default: () => [] },
  productos: { type: Array,  default: () => [] },
})

const clientes  = ref([...props.clientes])
const productos = ref([...props.productos])

const cliente_id    = ref('')
const tipo_entrega  = ref('mostrador') // mostrador | domicilio
const observaciones = ref('')

// bloque de domicilio
const dom = ref({
  nombre: '',
  telefono: '',
  direccion: '',
  colonia: '',
  ciudad: 'Tapachula, Chiapas',
  referencias: '',
})

// items
const items = ref([])
const money = n => new Intl.NumberFormat('es-MX',{ style:'currency', currency:'MXN' }).format(n ?? 0)

function addItem(p) {
  const ya = items.value.find(i => i.producto_id === p.id)
  if (ya) {
    if (ya.cantidad + 1 > p.stock) return alert('Stock insuficiente.')
    ya.cantidad += 1
  } else {
    if (p.stock < 1) return alert('Sin stock.')
    items.value.push({ producto_id: p.id, nombre: p.nombre, precio: p.precio, cantidad: 1, stock: p.stock })
  }
}
function setQty(it, qty) {
  const q = parseInt(qty || 0, 10)
  if (Number.isNaN(q) || q < 1) it.cantidad = 1
  else if (q > it.stock) it.cantidad = it.stock
  else it.cantidad = q
}
function removeItem(idx) { items.value.splice(idx, 1) }
const total = computed(() => items.value.reduce((a, it) => a + (it.precio * it.cantidad), 0))

async function altaRapida() {
  const nombre = prompt('Nombre del cliente:')
  if (!nombre) return
  const email = prompt('Correo (opcional):') || ''
  const telefono = prompt('Teléfono (opcional):') || ''

  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const res = await fetch('/vendedor/clientes/rapido', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token, 'X-Requested-With':'XMLHttpRequest' },
      body: JSON.stringify({ nombre, email, telefono }),
    })
    const data = await res.json()
    if (!data?.ok) throw new Error('No se pudo crear el cliente.')
    clientes.value.push(data.cliente)
    cliente_id.value = data.cliente.id
    alert('Cliente creado y seleccionado.')
  } catch (e) { console.error(e); alert('Error al crear el cliente.') }
}

function guardar() {
  if (!cliente_id.value) return alert('Selecciona un cliente.')
  if (items.value.length === 0) return alert('Agrega al menos un producto.')

  const payload = {
    cliente_id: Number(cliente_id.value),
    tipo_entrega: tipo_entrega.value,
    observaciones: observaciones.value || null,
    items: items.value.map(it => ({ producto_id: it.producto_id, cantidad: it.cantidad })),
  }

  if (tipo_entrega.value === 'domicilio') {
    if (!dom.value.nombre || !dom.value.telefono || !dom.value.direccion || !dom.value.colonia || !dom.value.ciudad) {
      return alert('Completa los datos de envío a domicilio.')
    }
    payload.dom = { ...dom.value }
  }

  router.post(route('vendedor.pedidos.store'), payload, { preserveState: true })
}
</script>

<template>
  <VendedorLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Nuevo pedido</h1>
          <p class="text-sm text-gray-500">Captura en mostrador o domicilio</p>
        </div>
        <Link :href="route('vendedor.pedidos.index')" class="text-indigo-600 hover:underline">
          ← Volver a pedidos
        </Link>
      </div>
    </template>

    <div class="mx-auto max-w-7xl p-6 grid gap-6 md:grid-cols-3">
      <section class="md:col-span-2 space-y-6">
        <div class="rounded-xl border bg-white p-4">
          <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-gray-600">Cliente</p>
            <button @click="altaRapida" class="text-sm text-indigo-700 hover:text-indigo-800">+ Alta rápida</button>
          </div>
          <select v-model="cliente_id" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">— Selecciona —</option>
            <option v-for="c in clientes" :key="c.id" :value="c.id">
              {{ c.nombre }} <span v-if="c.email"> — {{ c.email }}</span>
            </option>
          </select>
        </div>

        <div class="rounded-xl border bg-white p-4 grid gap-4">
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600 mb-1">Tipo de entrega</p>
              <select v-model="tipo_entrega" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="mostrador">Mostrador</option>
                <option value="domicilio">Domicilio</option>
              </select>
            </div>
            <div>
              <p class="text-sm text-gray-600 mb-1">Observaciones</p>
              <input v-model="observaciones" type="text" maxlength="255" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Opcional" />
            </div>
          </div>

          <div v-if="tipo_entrega === 'domicilio'" class="grid md:grid-cols-2 gap-4 pt-2 border-t">
            <div>
              <p class="text-sm text-gray-600 mb-1">Destinatario</p>
              <input v-model="dom.nombre" type="text" class="w-full rounded-lg border px-3 py-2 text-sm" />
            </div>
            <div>
              <p class="text-sm text-gray-600 mb-1">Teléfono</p>
              <input v-model="dom.telefono" type="text" class="w-full rounded-lg border px-3 py-2 text-sm" />
            </div>
            <div class="md:col-span-2">
              <p class="text-sm text-gray-600 mb-1">Dirección</p>
              <input v-model="dom.direccion" type="text" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Calle, número, entre calles" />
            </div>
            <div>
              <p class="text-sm text-gray-600 mb-1">Colonia</p>
              <input v-model="dom.colonia" type="text" class="w-full rounded-lg border px-3 py-2 text-sm" />
            </div>
            <div>
              <p class="text-sm text-gray-600 mb-1">Ciudad</p>
              <input v-model="dom.ciudad" type="text" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Tapachula, Chiapas" />
            </div>
            <div class="md:col-span-2">
              <p class="text-sm text-gray-600 mb-1">Referencias (opcional)</p>
              <input v-model="dom.referencias" type="text" class="w-full rounded-lg border px-3 py-2 text-sm" />
            </div>
          </div>
        </div>

        <div class="rounded-xl border bg-white p-4">
          <p class="mb-3 text-sm text-gray-600">Productos</p>
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
            <button
              v-for="p in productos" :key="p.id"
              class="rounded-lg border px-3 py-2 text-left hover:bg-gray-50"
              :disabled="p.stock === 0" @click="addItem(p)" :title="p.stock === 0 ? 'Sin stock' : 'Agregar'">
              <div class="font-medium text-gray-900">{{ p.nombre }}</div>
              <div class="text-xs text-gray-500">Precio: {{ money(p.precio) }}</div>
              <div class="text-xs" :class="p.stock ? 'text-gray-600' : 'text-rose-600'">Stock: {{ p.stock }}</div>
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase">
                <tr>
                  <th class="px-3 py-2 text-left">Producto</th>
                  <th class="px-3 py-2 text-left">Precio</th>
                  <th class="px-3 py-2 text-left">Cantidad</th>
                  <th class="px-3 py-2 text-left">Subtotal</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(it, idx) in items" :key="it.producto_id" class="hover:bg-gray-50">
                  <td class="px-3 py-2">{{ it.nombre }}</td>
                  <td class="px-3 py-2">{{ money(it.precio) }}</td>
                  <td class="px-3 py-2">
                    <input :value="it.cantidad" type="number" min="1" :max="it.stock" class="w-24 rounded border px-2 py-1" @input="setQty(it, $event.target.value)" />
                    <span class="ml-2 text-xs text-gray-500">/ {{ it.stock }}</span>
                  </td>
                  <td class="px-3 py-2 font-medium">{{ money(it.precio * it.cantidad) }}</td>
                  <td class="px-3 py-2 text-right">
                    <button class="text-rose-600 hover:text-rose-700" @click="removeItem(idx)">Quitar</button>
                  </td>
                </tr>

                <tr v-if="items.length === 0">
                  <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                    Agrega productos desde el catálogo superior.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <aside class="space-y-4">
        <div class="rounded-xl border bg-white p-4">
          <p class="text-sm text-gray-600">Resumen</p>
          <p class="mt-2 text-2xl font-semibold text-gray-900">{{ money(total) }}</p>
          <p class="text-xs text-gray-500">Total estimado</p>
          <button class="mt-4 w-full rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700" @click="guardar">
            Guardar pedido
          </button>
        </div>
      </aside>
    </div>
  </VendedorLayout>
</template>
