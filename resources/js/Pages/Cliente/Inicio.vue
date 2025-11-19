<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import ClienteHeader from '@/Components/ClienteHeader.vue'

const page = usePage()

// 👤 Usuario autenticado
const user = computed(() => page.props?.auth?.user)

// 🛒 Datos de carrito que mandas en props
const cartCount = computed(() => Number(page.props?.cart_count ?? 0))
const cartSubtotal = computed(() => Number(page.props?.cart_subtotal ?? 0))

// 📦 Pedidos recientes (opcional)
const recientes = computed(() => page.props?.recientes ?? [])

// Último pedido (primer elemento de recientes)
const ultimoPedido = computed(() => {
  const list = recientes.value || []
  return list.length ? list[0] : null
})

// Helpers
const fmtMoney = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })
    .format(Number(n ?? 0))

const fmtFecha = (f) =>
  f
    ? new Date(f).toLocaleString('es-MX', {
        dateStyle: 'medium',
        timeStyle: 'short',
      })
    : ''

const estadoClase = (estado) => {
  const e = String(estado || '').toLowerCase()

  return {
    pendiente: 'bg-amber-100 text-amber-900 border-amber-300',
    confirmado: 'bg-sky-100 text-sky-800 border-sky-300',
    preparando: 'bg-sky-100 text-sky-800 border-sky-300',
    listo: 'bg-indigo-100 text-indigo-800 border-indigo-300',
    en_camino: 'bg-indigo-100 text-indigo-800 border-indigo-300',
    entregado: 'bg-emerald-100 text-emerald-800 border-emerald-300',
    cancelado: 'bg-rose-100 text-rose-800 border-rose-300',
  }[e] || 'bg-slate-100 text-slate-700 border-slate-300'
}
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- Encabezado global -->
    <ClienteHeader />

    <!-- 🔹 Tarjeta de bienvenida (reemplaza el texto de "Sin pedidos todavía") -->
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2">
          <p class="text-xs font-semibold tracking-[0.18em] text-amber-600 uppercase">
            Bienvenido(a) a Pollería Pepe
          </p>
          <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
            Hola
            
          </h2>
          <p class="text-sm text-slate-600 max-w-xl text-justify">
  Este panel ha sido diseñado para ofrecerte una experiencia cómoda y organizada. 
  Desde aquí puedes realizar tus compras de manera segura, revisar el estado detallado 
  de tus pedidos, acceder a tu historial completo y mantener actualizada tu información 
  personal. Nuestro objetivo es brindarte un espacio centralizado donde puedas gestionar 
  todo lo relacionado con tus compras de forma clara, eficiente y accesible desde 
  cualquier dispositivo.
</p>


          <div class="flex flex-wrap gap-2 pt-1">
            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-medium text-amber-700 border border-amber-100">
              🛒 Compras en línea
            </span>
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-medium text-emerald-700 border border-emerald-100">
              🚚 Entrega a domicilio
            </span>
            <span class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600 border border-slate-100">
              📦 Seguimiento de pedidos
            </span>
          </div>
        </div>

        <div class="mt-4 sm:mt-0 flex sm:flex-col items-start sm:items-end gap-2">
          <Link
            :href="route('catalogo.index')"
            class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-600 active:scale-95 transition"
          >
            🛍 Comenzar a comprar
          </Link>
          <Link
            :href="route('cliente.pedidos.index')"
            class="text-[11px] text-slate-500 hover:text-slate-700 hover:underline"
          >
            Ver mis pedidos
          </Link>
        </div>
      </div>
    </section>

    <!-- 🔹 Layout principal -->
    <section class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] gap-6">
      <!-- Izquierda: accesos principales -->
      <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-base font-semibold text-slate-900">
            Accesos principales
          </h2>
          <p class="mt-1 text-sm text-slate-500">
            Entra directo a las secciones más importantes de tu cuenta.
          </p>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <Link
              :href="route('catalogo.index')"
              class="group rounded-xl border border-slate-200 bg-white px-4 py-3 hover:border-amber-300 hover:bg-amber-50/60 hover:shadow-sm transition flex flex-col gap-1"
            >
              <span class="text-lg">🛍</span>
              <span class="text-sm font-semibold text-slate-900">Catálogo</span>
              <span class="text-xs text-slate-500">
                Explora productos y agrégalos a tu carrito.
              </span>
            </Link>

            <Link
              :href="route('cliente.pedidos.index')"
              class="group rounded-xl border border-slate-200 bg-white px-4 py-3 hover:border-amber-300 hover:bg-amber-50/60 hover:shadow-sm transition flex flex-col gap-1"
            >
              <span class="text-lg">📦</span>
              <span class="text-sm font-semibold text-slate-900">Mis pedidos</span>
              <span class="text-xs text-slate-500">
                Revisa estados, fechas y totales de tus compras.
              </span>
            </Link>

            <Link
              :href="route('cliente.perfil.edit')"
              class="group rounded-xl border border-slate-200 bg-white px-4 py-3 hover:border-amber-300 hover:bg-amber-50/60 hover:shadow-sm transition flex flex-col gap-1"
            >
              <span class="text-lg">👤</span>
              <span class="text-sm font-semibold text-slate-900">Mi perfil</span>
              <span class="text-xs text-slate-500">
                Actualiza tu nombre, teléfono y dirección de entrega.
              </span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Derecha: resumen de carrito tipo widget -->
      <aside
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-fit lg:sticky lg:top-6 space-y-4"
      >
        <!-- Encabezado con icono -->
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2">
            <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-700">
              🧺
            </div>
            <div>
              <h2 class="text-sm font-semibold text-slate-900">
                Resumen de carrito
              </h2>
              <p class="text-[11px] text-slate-500">
                Vista rápida de tu compra actual.
              </p>
            </div>
          </div>

          <span
            class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600 border border-slate-200"
          >
            {{ cartCount }} ítem(s)
          </span>
        </div>

        <!-- Totales -->
        <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3 space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-slate-600">Subtotal actual</span>
            <span class="font-semibold text-slate-900">
              {{ fmtMoney(cartSubtotal) }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-slate-600">Envío estimado</span>
            <span class="text-slate-500 text-xs">
              Se calcula al confirmar tu pedido
            </span>
          </div>
        </div>

        <!-- Estado del carrito -->
        <div class="text-xs space-y-2">
          <div class="flex items-center gap-2">
            <span
              class="inline-flex h-2 w-2 rounded-full"
              :class="cartCount > 0 ? 'bg-emerald-500' : 'bg-slate-300'"
            ></span>
            <p class="text-slate-600">
              <span class="font-semibold">
                {{ cartCount > 0 ? 'Tienes productos en el carrito.' : 'Tu carrito está vacío.' }}
              </span>
            </p>
          </div>

          <p class="text-[11px] text-slate-500 leading-relaxed">
            Puedes revisar o ajustar tu carrito desde la sección
            <span class="font-semibold text-slate-700">Carrito</span> del menú superior
            antes de confirmar tu pedido.
          </p>
        </div>

        <!-- Enlace sutil -->
        <div class="pt-1 border-t border-slate-100 mt-1">
          <Link
            :href="route('cliente.carrito.index')"
            class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 hover:underline"
          >
            Ver detalles del carrito →
          </Link>
        </div>
      </aside>
    </section>
  </div>
</template>
