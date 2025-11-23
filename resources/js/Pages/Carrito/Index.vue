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
  if (!confirm('¿Deseas vaciar el carrito por completo?')) return
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
  <div class="bg-slate-50/70">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
      <!-- 🔹 Encabezado global del cliente -->
      <ClienteHeader />

      <!-- 🔸 Contenido específico del carrito -->
      <section
        class="bg-white/90 border border-slate-100 shadow-sm rounded-3xl px-4 sm:px-6 lg:px-8 py-6 sm:py-7 space-y-6 sm:space-y-7"
      >
        <!-- Encabezado del carrito -->
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-2xl">🧺</span>
              <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                Carrito de compras
              </h1>
            </div>
            <p class="mt-1 text-sm text-slate-500 max-w-xl">
              Revisa los artículos seleccionados antes de continuar con la confirmación de tu pedido.
            </p>
          </div>

          <div class="flex flex-wrap gap-2 justify-end">
            <button
              type="button"
              @click="irCatalogo"
              class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs sm:text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-1"
            >
              ← Seguir comprando
            </button>

            <button
              v-if="hayItems"
              type="button"
              @click="clearCart"
              class="inline-flex items-center justify-center rounded-full border border-red-100 bg-red-50 px-4 py-2 text-xs sm:text-sm font-medium text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-1"
            >
              Vaciar carrito
            </button>
          </div>
        </header>

        <!-- Carrito vacío -->
        <div
          v-if="!hayItems"
          class="flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-10 text-center"
        >
          <p class="text-base sm:text-lg font-medium text-slate-700">
            Aún no has agregado productos a tu carrito.
          </p>
          <p class="text-sm text-slate-500 max-w-md">
            Explora el catálogo para seleccionar piezas de pollo y productos complementarios
            que desees adquirir.
          </p>
          <button
            type="button"
            @click="irCatalogo"
            class="mt-2 inline-flex items-center justify-center rounded-full bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-1"
          >
            Ver catálogo de productos
          </button>
        </div>

        <!-- Carrito con elementos -->
        <div v-else class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
          <!-- Lista de productos -->
          <div class="space-y-3 sm:space-y-4">
            <article
              v-for="it in props.items"
              :key="it.id"
              class="flex items-center gap-4 sm:gap-5 rounded-2xl border border-slate-100 bg-white px-3 sm:px-4 py-3 sm:py-4 shadow-sm/50 hover:shadow-md/60 transition-shadow"
            >
              <div class="flex-shrink-0">
                <div class="h-16 w-16 sm:h-20 sm:w-20 overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                  <img
                    :src="imgUrl(it)"
                    alt=""
                    class="h-full w-full object-cover"
                  >
                </div>
              </div>

              <div class="flex-1 min-w-0">
                <h2 class="text-sm sm:text-base font-semibold text-slate-900 truncate">
                  {{ it.name }}
                </h2>
                <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                  {{ fmt(it.price) }} c/u
                </p>
              </div>

              <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex items-center">
                  <input
                    type="number"
                    min="1"
                    max="999"
                    :value="it.qty"
                    @change="e => updateQty(it.id, e.target.value)"
                    class="w-20 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-center text-sm text-slate-800 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-300"
                  >
                </div>

                <div class="w-24 sm:w-28 text-right">
                  <p class="text-sm sm:text-base font-semibold text-slate-900">
                    {{ fmt(it.price * it.qty) }}
                  </p>
                </div>

                <button
                  type="button"
                  @click="removeItem(it.id)"
                  class="hidden sm:inline-flex items-center justify-center rounded-full border border-red-100 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-1"
                >
                  Quitar
                </button>
              </div>

              <!-- Botón quitar en móvil -->
              <div class="sm:hidden mt-2 w-full flex justify-end">
                <button
                  type="button"
                  @click="removeItem(it.id)"
                  class="inline-flex items-center justify-center rounded-full border border-red-100 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-1"
                >
                  Quitar
                </button>
              </div>
            </article>
          </div>

          <!-- Resumen -->
          <aside
            class="h-fit rounded-2xl border border-slate-100 bg-slate-50/80 px-4 sm:px-5 py-4 sm:py-5 shadow-sm lg:sticky lg:top-6"
          >
            <h2 class="text-base sm:text-lg font-semibold text-slate-900">
              Resumen del pedido
            </h2>

            <!-- Lista compacta de detalles -->
            <div
              v-if="props.items.length"
              class="mt-3 mb-4 max-h-48 overflow-auto rounded-xl border border-slate-200 bg-white"
            >
              <div
                v-for="it in props.items"
                :key="it.id"
                class="flex items-center justify-between gap-3 px-3 py-2 border-b last:border-b-0 text-xs sm:text-sm"
              >
                <span class="text-slate-700 truncate">
                  {{ it.qty }} × {{ it.name }}
                </span>
                <span class="font-medium text-slate-900">
                  {{ fmt(it.price * it.qty) }}
                </span>
              </div>
            </div>

            <dl class="space-y-1 text-sm">
              <div class="flex items-center justify-between text-slate-600">
                <dt>Artículos</dt>
                <dd class="font-medium text-slate-800">{{ itemsCount }}</dd>
              </div>
              <div class="flex items-center justify-between text-slate-600">
                <dt>Subtotal</dt>
                <dd class="font-medium text-slate-800">{{ fmt(props.subtotal) }}</dd>
              </div>
              <div class="flex items-center justify-between text-slate-600">
                <dt>Envío</dt>
                <dd class="font-medium text-slate-800">{{ fmt(props.envio) }}</dd>
              </div>
            </dl>

            <div class="mt-4 border-t border-slate-200 pt-3">
              <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-900">
                  Total a pagar
                </span>
                <span class="text-lg sm:text-xl font-bold text-amber-600">
                  {{ fmt(props.total) }}
                </span>
              </div>
            </div>

            <button
              type="button"
              @click="irCheckout"
              :disabled="!hayItems"
              class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-60"
            >
              Proceder al checkout
            </button>

            <p class="mt-2 text-[11px] text-slate-400">
              La confirmación del pedido y los datos de entrega se revisarán en el siguiente paso.
            </p>
          </aside>
        </div>

        <!-- 🔙 Botón Volver -->
        <div class="pt-4 border-t border-slate-100">
          <button
            id="btn-volver"
            type="button"
            @click="volver"
            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs sm:text-sm font-medium text-slate-600 hover:bg-slate-50 hover:shadow-sm transition-all duration-150 ease-out active:scale-95 active:opacity-80 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
          >
            ← Volver
          </button>
        </div>
      </section>
    </div>
  </div>
</template>
