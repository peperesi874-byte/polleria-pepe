<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'
import ClienteHeader from '@/Components/ClienteHeader.vue'

const page = usePage()

// 👤 Usuario autenticado
const user = computed(() => page.props?.auth?.user)

// 🛒 Carrito enviado desde el backend
const cartResumen = computed(
  () =>
    page.props?.cartResumen ?? {
      items: [],
      subtotal: 0,
      count: 0,
    }
)

const cartCount = computed(() => Number(cartResumen.value.count ?? 0))
const cartSubtotal = computed(() => Number(cartResumen.value.subtotal ?? 0))

// 📦 Pedidos recientes (lista de últimos pedidos)
const recientes = computed(() => page.props?.recientes ?? [])

// 🧮 Resumen general de pedidos
const resumenPedidos = computed(
  () =>
    page.props?.resumenPedidos ?? {
      total: 0,
      pendientes: 0,
      entregados: 0,
    }
)

// Helpers
const fmtMoney = (n) =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(
    Number(n ?? 0)
  )

const fmtFecha = (f) =>
  f
    ? new Date(f).toLocaleString('es-MX', {
        dateStyle: 'medium',
        timeStyle: 'short',
      })
    : ''

const estadoClase = (estado) => {
  const e = String(estado || '').toLowerCase()

  return (
    {
      pendiente: 'bg-amber-100 text-amber-900 border-amber-300',
      confirmado: 'bg-sky-100 text-sky-800 border-sky-300',
      preparando: 'bg-sky-100 text-sky-800 border-sky-300',
      listo: 'bg-indigo-100 text-indigo-800 border-indigo-300',
      en_camino: 'bg-indigo-100 text-indigo-800 border-indigo-300',
      entregado: 'bg-emerald-100 text-emerald-800 border-emerald-300',
      cancelado: 'bg-rose-100 text-rose-800 border-rose-300',
    }[e] || 'bg-slate-100 text-slate-700 border-slate-300'
  )
}
</script>

<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- Encabezado global -->
    <ClienteHeader />

    <!-- 🔹 Tarjeta de bienvenida -->
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div
        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
      >
        <div class="space-y-2">
          <p
            class="text-xs font-semibold tracking-[0.18em] text-amber-600 uppercase"
          >
            Bienvenido(a) a Pollería Pepe
          </p>

          <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
            Hola {{ user?.name }}
          </h2>

          <p class="text-sm text-slate-600 max-w-xl text-justify">
            Este panel ha sido diseñado para ofrecerte una experiencia cómoda y
            organizada. Desde aquí puedes realizar tus compras de manera segura,
            revisar el estado detallado de tus pedidos, acceder a tu historial
            completo y mantener actualizada tu información personal.
          </p>

          <div class="flex flex-wrap gap-2 pt-1">
            <span
              class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-medium text-amber-700 border border-amber-100"
            >
              🛒 Compras en línea
            </span>
            <span
              class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-medium text-emerald-700 border border-emerald-100"
            >
              🚚 Entrega a domicilio
            </span>
            <span
              class="inline-flex items-center rounded-full bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600 border border-slate-100"
            >
              📦 Seguimiento de pedidos
            </span>
          </div>
        </div>

        <div
          class="mt-4 sm:mt-0 flex sm:flex-col items-start sm:items-end gap-2"
        >
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
    <section
      class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)] gap-6"
    >
      <!-- Izquierda: resumen + últimos 5 pedidos -->
      <div class="space-y-4">
        <div
          class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md"
        >
          <!-- Resumen de pedidos -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-semibold text-slate-900">
                Resumen de tus pedidos
              </h2>
              <p class="text-xs text-slate-500">
                Vista rápida de tu actividad reciente.
              </p>
            </div>

            <span
              class="px-3 py-1 text-[11px] rounded-full bg-slate-50 border border-slate-200 text-slate-600 font-medium"
            >
              Total: {{ resumenPedidos.total ?? 0 }}
            </span>
          </div>

          <div class="flex flex-wrap gap-2 mb-5 text-xs">
            <span
              class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-amber-800 font-medium"
            >
              ⏳ Pendientes:
              <strong>{{ resumenPedidos.pendientes ?? 0 }}</strong>
            </span>
            <span
              class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-emerald-800 font-medium"
            >
              ✅ Entregados:
              <strong>{{ resumenPedidos.entregados ?? 0 }}</strong>
            </span>
          </div>

          <div class="border-t border-slate-200 mb-4"></div>

          <!-- Últimos 5 pedidos (lista) -->
          <div>
            <h3
              class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2"
            >
              Últimos pedidos
            </h3>

            <div v-if="recientes.length" class="space-y-3">
              <div
                v-for="p in recientes"
                :key="p.id"
                class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 py-3 px-4 hover:bg-slate-100 transition"
              >
                <div>
                  <p class="font-semibold text-slate-900 text-sm">
                    PED-{{ p.id }}
                  </p>
                  <p class="text-xs text-slate-500">
                    {{ fmtFecha(p.created_at) }}
                  </p>
                </div>

                <span
                  class="px-3 py-1 text-[11px] rounded-full border font-medium"
                  :class="estadoClase(p.estado)"
                >
                  ● {{ p.estado }}
                </span>
              </div>
            </div>

            <p v-else class="text-xs text-slate-500">
              Aún no tienes pedidos registrados.
            </p>

            <div class="mt-4 text-right">
              <Link
                :href="route('cliente.pedidos.index')"
                class="text-[12px] font-semibold text-amber-700 hover:underline"
              >
                Ver todos los pedidos →
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Derecha: Resumen de carrito + acciones rápidas -->
      <aside
        class="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_20px_rgba(0,0,0,0.04)] 
               h-fit lg:sticky lg:top-6 transition-all duration-300 hover:shadow-[0_6px_26px_rgba(0,0,0,0.07)]"
      >
        <!-- Decoración suave arriba -->
        <div
          class="absolute inset-x-0 top-0 h-1 rounded-t-2xl 
                  bg-gradient-to-r from-amber-300 via-amber-400 to-rose-300"
        ></div>

        <!-- Header -->
        <div class="flex items-center justify-between mb-4 mt-1">
          <div class="flex items-center gap-3">
            <div
              class="h-10 w-10 flex items-center justify-center rounded-xl 
                      bg-amber-100 border border-amber-200 text-amber-700 text-lg shadow-inner"
            >
              🧺
            </div>
            <div>
              <h2 class="text-base font-bold text-slate-900">
                Resumen de carrito
              </h2>
              <p class="text-xs text-slate-500">Tus productos actuales</p>
            </div>
          </div>

          <span
            class="px-3 py-1 text-[11px] font-medium rounded-full bg-slate-100 border border-slate-200 text-slate-600"
          >
            {{ cartCount }} ítem(s)
          </span>
        </div>

        <!-- Estado -->
        <div class="flex items-center gap-2 mb-4 text-sm">
          <span
            class="h-2 w-2 rounded-full"
            :class="cartCount > 0 ? 'bg-emerald-500' : 'bg-slate-300'"
          ></span>
          <span class="text-slate-700 font-medium">
            {{
              cartCount > 0
                ? 'Tienes productos en el carrito'
                : 'Tu carrito está vacío'
            }}
          </span>
        </div>

        <!-- Separator -->
        <div class="border-t border-slate-200 my-3"></div>

        <!-- Totales -->
        <div class="space-y-3">
          <div
            class="p-3 rounded-xl bg-gradient-to-r from-slate-50 to-white border border-slate-200 
                    flex items-center justify-between shadow-sm"
          >
            <span class="text-slate-600 text-sm">Subtotal actual</span>
            <span class="font-semibold text-slate-900">
              {{ fmtMoney(cartSubtotal) }}
            </span>
          </div>

          <div
            class="p-3 rounded-xl bg-gradient-to-r from-slate-50 to-white border border-slate-200 
                    flex items-center justify-between shadow-sm"
          >
            <span class="text-slate-600 text-sm">Envío estimado</span>
            <span class="text-xs text-slate-500">
              Se calcula al confirmar el pedido
            </span>
          </div>
        </div>

        <!-- Separator -->
        <div class="border-t border-slate-200 my-4"></div>

        <!-- Acciones rápidas (debajo del carrito, en lista) -->
        <h3
          class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2"
        >
          Acciones rápidas
        </h3>

        <ul class="space-y-1">
          <li>
            <Link
              :href="route('catalogo.index')"
              class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-slate-50 transition"
            >
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-50 text-amber-700 text-lg"
                >
                  🛍
                </span>
                <div>
                  <p class="text-sm font-semibold text-slate-900">
                    Abrir catálogo
                  </p>
                  <p class="text-xs text-slate-500">
                    Explora productos y agrega al carrito.
                  </p>
                </div>
              </div>
              <span class="text-xs text-slate-400">Ir ▸</span>
            </Link>
          </li>

          <li>
            <Link
              :href="route('cliente.pedidos.index')"
              class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-slate-50 transition"
            >
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-sky-50 text-sky-700 text-lg"
                >
                  📦
                </span>
                <div>
                  <p class="text-sm font-semibold text-slate-900">
                    Ver mis pedidos
                  </p>
                  <p class="text-xs text-slate-500">
                    Revisa estados, fechas y totales.
                  </p>
                </div>
              </div>
              <span class="text-xs text-slate-400">Ir ▸</span>
            </Link>
          </li>

          <li>
            <Link
              :href="route('cliente.perfil.edit')"
              class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-slate-50 transition"
            >
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-purple-50 text-purple-700 text-lg"
                >
                  👤
                </span>
                <div>
                  <p class="text-sm font-semibold text-slate-900">
                    Editar mi perfil
                  </p>
                  <p class="text-xs text-slate-500">
                    Actualiza tus datos y dirección de entrega.
                  </p>
                </div>
              </div>
              <span class="text-xs text-slate-400">Ir ▸</span>
            </Link>
          </li>
        </ul>
      </aside>
    </section>
  </div>
</template>
