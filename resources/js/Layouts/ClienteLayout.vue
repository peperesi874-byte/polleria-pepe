<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()
const user = computed(() => page.props?.auth?.user ?? null)

// Saludo según la hora
const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

// Datos del carrito
const cartCount = computed(() => Number(page.props?.cart_count ?? 0))
const cartSubtotal = computed(() => Number(page.props?.cart_subtotal ?? 0))

const money = (n) =>
  new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(Number(n ?? 0))

// Resaltar ruta activa (con Ziggy)
const isCurrent = patterns => {
  const current = route().current()
  return patterns.some(p => current?.startsWith(p))
}

// logout seguro
function logout() {
  router.post(route('logout'))
}
</script>

<template>
  <div class="min-h-screen bg-neutral-50 flex flex-col">

    <!-- 🔶 HEADER PRINCIPAL -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-amber-200 shadow-sm">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">

        <!-- 🔸 LOGO + NOMBRE -->
        <Link :href="route('cliente.inicio')" class="flex items-center gap-3">
          <img
            src="/logo.jpg"
            class="h-10 w-10 rounded-full ring-2 ring-amber-500 object-cover shadow"
          />

          <div class="leading-tight">
            <p class="text-xs uppercase text-neutral-500">Panel del cliente</p>
            <p class="font-bold text-neutral-800">Pollería Pepe</p>
          </div>
        </Link>

        <!-- 🔸 NAV DESKTOP -->
        <nav class="hidden md:flex items-center gap-1 text-sm font-medium">

          <Link
            :href="route('cliente.inicio')"
            :class="[
              'px-3 py-2 rounded-full transition',
              isCurrent(['cliente.inicio'])
                ? 'bg-amber-100 text-amber-700'
                : 'text-neutral-600 hover:bg-neutral-100'
            ]"
          >
            Inicio
          </Link>

          <Link
            :href="route('cliente.catalogo.index')"
            :class="[
              'px-3 py-2 rounded-full transition',
              isCurrent(['cliente.catalogo'])
                ? 'bg-amber-100 text-amber-700'
                : 'text-neutral-600 hover:bg-neutral-100'
            ]"
          >
            Catálogo
          </Link>

          <Link
            :href="route('cliente.pedidos.index')"
            :class="[
              'px-3 py-2 rounded-full transition',
              isCurrent(['cliente.pedidos'])
                ? 'bg-amber-100 text-amber-700'
                : 'text-neutral-600 hover:bg-neutral-100'
            ]"
          >
            Mis pedidos
          </Link>

          <Link
            :href="route('cliente.carrito.index')"
            :class="[
              'px-3 py-2 rounded-full transition flex items-center gap-2',
              isCurrent(['cliente.carrito'])
                ? 'bg-amber-100 text-amber-700'
                : 'text-neutral-600 hover:bg-neutral-100'
            ]"
          >
            Carrito
            <span
              v-if="cartCount > 0"
              class="min-w-[1.4rem] h-6 text-xs font-bold bg-rose-600 text-white rounded-full flex items-center justify-center"
            >
              {{ cartCount }}
            </span>
          </Link>
        </nav>

        <!-- 🔸 USUARIO -->
        <div class="hidden sm:flex flex-col text-right mr-4">
          <span class="text-xs text-neutral-500">{{ saludo }}</span>
          <strong class="text-neutral-800 text-sm">{{ user?.name }}</strong>
        </div>

        <!-- 🔸 BOTÓN SALIR -->
        <button
          class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-full text-xs font-semibold"
          @click="logout"
        >
          Salir
        </button>

      </div>

      <!-- 🔸 NAV MÓVIL -->
      <div class="md:hidden border-t border-amber-100 bg-white">
        <div class="flex items-center justify-between px-4 py-2 text-xs">

          <Link
            :href="route('cliente.inicio')"
            :class="[ isCurrent(['cliente.inicio']) ? 'text-amber-700 font-semibold' : 'text-neutral-600' ]"
          >
            Inicio
          </Link>

          <Link
            :href="route('cliente.catalogo.index')"
            :class="[ isCurrent(['cliente.catalogo']) ? 'text-amber-700 font-semibold' : 'text-neutral-600' ]"
          >
            Catálogo
          </Link>

          <Link
            :href="route('cliente.pedidos.index')"
            :class="[ isCurrent(['cliente.pedidos']) ? 'text-amber-700 font-semibold' : 'text-neutral-600' ]"
          >
            Pedidos
          </Link>

          <Link
            :href="route('cliente.carrito.index')"
            :class="[ isCurrent(['cliente.carrito']) ? 'text-amber-700 font-semibold' : 'text-neutral-600' ]"
          >
            🛒 {{ cartCount }}
          </Link>

        </div>
      </div>
    </header>

    <!-- 🔶 CONTENIDO -->
    <main class="flex-1">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6">
        <slot />
      </div>
    </main>

    <!-- 🔶 FOOTER -->
    <footer class="border-t bg-white">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 text-[11px] text-neutral-500 flex justify-between">
        <span>Pollería Pepe — Panel del Cliente</span>
        <span>Sistema de pedidos</span>
      </div>
    </footer>

  </div>
</template>
