<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()
const user = computed(() => page.props?.auth?.user)

const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

const cartCount = computed(() => Number(page.props?.cart_count ?? 0))
const cartSubtotal = computed(() => Number(page.props?.cart_subtotal ?? 0))
const fmt = n => new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(n)
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- Header -->
    <header class="flex items-start justify-between gap-4">
      <div>
        <p class="text-sm text-neutral-500">Panel del cliente</p>
        <h1 class="mt-1 text-3xl font-black tracking-tight text-neutral-900">
          {{ saludo }}, <span class="text-amber-600">{{ user?.name || 'Cliente' }}</span>
        </h1>
        <p class="mt-2 text-neutral-600">Gestiona tu compra, pedidos y datos desde aquí.</p>
      </div>

      <div class="flex items-center gap-2">
        <Link
          :href="route('catalogo.index')"
          class="hidden sm:inline-flex items-center gap-2 rounded-xl bg-neutral-900 px-4 py-2 text-white hover:bg-neutral-800"
        >
          🛒 Ver catálogo
        </Link>
        <form method="post" action="/logout">
          <input type="hidden" name="_token" :value="$page.props.csrf_token" />
         <button
              type="button"
              class="rounded-md bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
              @click.prevent="$inertia.post(route('logout'))"
            >
              Cerrar sesión
            </button>

        </form>
      </div>
    </header>

    <!-- KPIs -->
    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <Link
        :href="route('cliente.carrito.index')"
        class="group rounded-2xl border bg-white p-4 shadow-sm hover:shadow transition"
      >
        <div class="flex items-center justify-between">
          <span class="text-sm text-neutral-500">Carrito</span>
          <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
            {{ cartCount }}
          </span>
        </div>
        <div class="mt-2 text-2xl font-extrabold tracking-tight text-neutral-900">
          {{ fmt(cartSubtotal) }}
        </div>
        <div class="mt-1 text-xs text-neutral-500">Subtotal actual</div>
      </Link>

      <Link
        :href="route('cliente.pedidos.index')"
        class="group rounded-2xl border bg-white p-4 shadow-sm hover:shadow transition"
      >
        <div class="flex items-center justify-between">
          <span class="text-sm text-neutral-500">Pedidos</span>
          <span class="text-lg">📦</span>
        </div>
        <div class="mt-2 text-2xl font-extrabold tracking-tight text-neutral-900">
          Historial
        </div>
        <div class="mt-1 text-xs text-neutral-500">Consulta estados y detalles</div>
      </Link>

      <Link
        :href="route('cliente.perfil.edit')"
        class="group rounded-2xl border bg-white p-4 shadow-sm hover:shadow transition"
      >
        <div class="flex items-center justify-between">
          <span class="text-sm text-neutral-500">Perfil</span>
          <span class="text-lg">👤</span>
        </div>
        <div class="mt-2 text-2xl font-extrabold tracking-tight text-neutral-900">
          Mis datos
        </div>
        <div class="mt-1 text-xs text-neutral-500">Nombre, dirección y contacto</div>
      </Link>
    </section>

    <!-- Main layout -->
    <section class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-6">
      <!-- Acciones principales -->
      <div class="space-y-4">
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold tracking-tight text-neutral-900">Acciones rápidas</h2>
          <p class="mt-1 text-sm text-neutral-600">
            Elige por dónde comenzar.
          </p>

          <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Link
              :href="route('catalogo.index')"
              class="group rounded-xl border p-4 hover:shadow-sm hover:bg-neutral-50 transition"
            >
              <div class="flex items-center gap-3">
                <div class="text-2xl">🛍️</div>
                <div>
                  <div class="font-semibold text-neutral-900">Explorar catálogo</div>
                  <div class="text-sm text-neutral-600">Agrega productos al carrito</div>
                </div>
              </div>
            </Link>

            <Link
              :href="route('cliente.pedidos.index')"
              class="group rounded-xl border p-4 hover:shadow-sm hover:bg-neutral-50 transition"
            >
              <div class="flex items-center gap-3">
                <div class="text-2xl">📑</div>
                <div>
                  <div class="font-semibold text-neutral-900">Mis pedidos</div>
                  <div class="text-sm text-neutral-600">Estado, entregas y totales</div>
                </div>
              </div>
            </Link>

            <Link
              :href="route('cliente.perfil.edit')"
              class="group rounded-xl border p-4 hover:shadow-sm hover:bg-neutral-50 transition"
            >
              <div class="flex items-center gap-3">
                <div class="text-2xl">🧾</div>
                <div>
                  <div class="font-semibold text-neutral-900">Actualizar perfil</div>
                  <div class="text-sm text-neutral-600">Nombre, dirección, teléfono</div>
                </div>
              </div>
            </Link>

            <a
              href="#"
              class="group rounded-xl border p-4 hover:shadow-sm hover:bg-neutral-50 transition"
            >
              <div class="flex items-center gap-3">
                <div class="text-2xl">💬</div>
                <div>
                  <div class="font-semibold text-neutral-900">Soporte</div>
                  <div class="text-sm text-neutral-600">¿Necesitas ayuda con tu pedido?</div>
                </div>
              </div>
            </a>
          </div>
        </div>

        <!-- Tips -->
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <h3 class="text-sm font-semibold text-neutral-900">Sugerencia</h3>
          <p class="mt-2 text-sm text-neutral-600">
            Desde el carrito puedes modificar cantidades o quitar productos antes de confirmar tu pedido.
          </p>
        </div>
      </div>

      <!-- Widget Carrito -->
      <aside class="rounded-2xl border bg-white p-5 shadow-sm h-fit sticky top-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold tracking-tight text-neutral-900">Mi carrito</h2>
          <span v-if="cartCount>0" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
            {{ cartCount }} ítem(s)
          </span>
        </div>

        <div class="mt-4 space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-neutral-600">Subtotal</span>
            <span class="font-semibold text-neutral-900">{{ fmt(cartSubtotal) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-neutral-600">Envío</span>
            <span class="text-neutral-900">—</span>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-2">
          <Link
            :href="route('cliente.carrito.index')"
            class="w-full rounded-xl bg-amber-500 px-4 py-2 text-center font-semibold text-white hover:bg-amber-600"
          >
            Ver carrito
          </Link>
          <Link
            :href="route('catalogo.index')"
            class="w-full rounded-xl border px-4 py-2 text-center font-medium hover:bg-neutral-50"
          >
            Seguir comprando
          </Link>
        </div>

        <p class="mt-3 text-xs text-neutral-500">
          * El total final se calcula durante el checkout.
        </p>
      </aside>
    </section>
  </div>
</template>
