<script setup>
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import ClienteHeader from '@/Components/ClienteHeader.vue' // 👈 agregado

const props = defineProps({
  items: { type: Array,  default: () => [] },
  subtotal: { type: Number, default: 0 },
  envio: { type: Number, default: 0 },
  total: { type: Number, default: 0 },
})

const fmt = n => new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n)
const hayItems = computed(() => props.items.length > 0)

// ➕ Conteo de artículos para mostrar en Resumen
const itemsCount = computed(() =>
  props.items.reduce((acc, it) => acc + Number(it.qty || 0), 0)
)

// 🔙 Volver con animación suave
function volver() {
  const btn = document.getElementById('btn-volver')
  if (btn) {
    btn.classList.add('scale-95', 'opacity-70')
    setTimeout(() => {
      btn.classList.remove('scale-95', 'opacity-70')
      window.history.back()
    }, 150)
  } else {
    window.history.back()
  }
}

// Normaliza imagen
function imgUrl(it) {
  const raw = it.image_url || it.image
  if (!raw) return '/logo.jpg'

  let r = String(raw).trim()
  if (r.startsWith('http://') || r.startsWith('https://')) return r
  r = r.replace(/^\/+/, '')
  if (r.startsWith('public/')) return '/storage/' + r.slice(7)
  if (r.startsWith('storage/')) return '/' + r
  return '/' + r
}

function updateQty(id, qty) {
  const fixed = Math.max(1, Math.min(999, Number(qty || 1)))
  router.post(route('cliente.carrito.update'), {
    items: [{ id, qty: fixed }]
  }, { preserveScroll: true })
}

function removeItem(id) {
  router.post(route('cliente.carrito.remove'), { id }, { preserveScroll: true })
}

function clearCart() {
  if (!confirm('¿Vaciar carrito completo?')) return
  router.post(route('cliente.carrito.clear'), {}, { preserveScroll: true })
}

function irCatalogo() {
  router.visit(route('catalogo.index'))
}

// ✅ Ir al checkout
function irCheckout() {
  if (!hayItems.value) return
  router.visit(route('cliente.checkout.create'))
}
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- 🔹 Encabezado global del cliente -->
    <ClienteHeader />

    <!-- 🔸 Contenido específico del carrito -->
    <div class="space-y-6">
      <!-- Encabezado del carrito -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <h1
            class="text-3xl font-extrabold text-amber-600 drop-shadow-sm tracking-tight flex items-center gap-2"
          >
            🧺 Carrito
          </h1>
        </div>

        <div class="flex gap-2">
          <button @click="irCatalogo" class="px-3 py-2 rounded border hover:bg-gray-50">
            ← Seguir comprando
          </button>
          <button
            v-if="hayItems"
            @click="clearCart"
            class="px-3 py-2 rounded border text-red-600 hover:bg-red-50"
          >
            Vaciar carrito
          </button>
        </div>
      </div>

      <div v-if="!hayItems" class="p-8 text-center text-gray-500 border rounded-lg">
        Tu carrito está vacío. Agrega productos desde el catálogo.
      </div>

      <div v-else class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-3">
          <div
            v-for="it in props.items"
            :key="it.id"
            class="flex items-center gap-4 p-3 border rounded-lg"
          >
            <img
              :src="imgUrl(it)"
              alt=""
              class="w-16 h-16 object-cover rounded border bg-white"
            />

            <div class="flex-1">
              <div class="font-medium">{{ it.name }}</div>
              <div class="text-sm text-gray-500">{{ fmt(it.price) }} c/u</div>
            </div>

            <div class="flex items-center gap-2">
              <input
                type="number"
                min="1"
                max="999"
                :value="it.qty"
                @change="e => updateQty(it.id, e.target.value)"
                class="w-20 border rounded px-2 py-1 text-center"
              >
            </div>

            <div class="w-28 text-right font-medium">
              {{ fmt(it.price * it.qty) }}
            </div>

            <button
              @click="removeItem(it.id)"
              class="px-2 py-1 text-red-600 hover:bg-red-50 rounded border"
            >
              Quitar
            </button>
          </div>
        </div>

        <!-- Totales -->
        <aside class="p-4 border rounded-lg h-fit sticky top-6">
          <h2 class="font-semibold mb-3">Resumen</h2>

          <!-- 🧾 Lista compacta de productos -->
          <div v-if="props.items.length" class="mb-3 max-h-48 overflow-auto border rounded">
            <div
              v-for="it in props.items"
              :key="it.id"
              class="flex items-center justify-between gap-3 px-3 py-2 border-b last:border-b-0 text-sm"
            >
              <span class="text-gray-700 truncate">
                {{ it.qty }} × {{ it.name }}
              </span>
              <span class="font-medium">
                {{ fmt(it.price * it.qty) }}
              </span>
            </div>
          </div>

          <div class="flex justify-between text-xs text-gray-600 mb-1">
            <span>Artículos</span><span>{{ itemsCount }}</span>
          </div>
          <div class="flex justify-between text-sm mb-1">
            <span>Subtotal</span><span>{{ fmt(props.subtotal) }}</span>
          </div>
          <div class="flex justify-between text-sm mb-3">
            <span>Envío</span><span>{{ fmt(props.envio) }}</span>
          </div>
          <div class="flex justify-between text-lg font-bold mb-4">
            <span>Total</span><span>{{ fmt(props.total) }}</span>
          </div>

          <!-- ✅ Botón al checkout -->
          <button
            @click="irCheckout"
            :disabled="!hayItems"
            class="w-full bg-amber-500 hover:bg-amber-600 text-white rounded px-4 py-2 disabled:opacity-50"
          >
            Proceder al checkout
          </button>

          <p class="mt-2 text-xs text-gray-500">* Checkout por implementar.</p>
        </aside>
      </div>

      <!-- 🔙 Botón Volver con efecto -->
      <div class="pt-6">
        <button
          id="btn-volver"
          @click="volver"
          class="border rounded px-4 py-2 text-sm hover:bg-gray-50 hover:shadow-md transition-all duration-200 ease-out active:scale-95 active:opacity-70"
        >
          ← Volver
        </button>
      </div>
    </div>
  </div>
</template>
