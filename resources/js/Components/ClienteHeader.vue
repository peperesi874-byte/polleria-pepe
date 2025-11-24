<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()
const user = computed(() => page.props?.auth?.user)

const saludo = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 19) return 'Buenas tardes'
  return 'Buenas noches'
})

/* 🌍 Configuración global del sistema */
const cfg = computed(() => page.props?.config_global ?? null)

const horarioTexto = computed(() => {
  if (!cfg.value) return ''
  const a = (cfg.value.horario_apertura || '').slice(0, 5)
  const c = (cfg.value.horario_cierre   || '').slice(0, 5)
  return `${a}–${c} hrs`
})

const estadoTienda = computed(() => {
  if (!cfg.value) return ''
  return cfg.value.is_open_now ? 'Abierto' : 'Cerrado'
})

const estadoClase = computed(() => {
  if (!cfg.value) return ''
  return cfg.value.is_open_now
    ? 'bg-emerald-100 text-emerald-700 ring-emerald-200'
    : 'bg-rose-100 text-rose-700 ring-rose-200'
})

/* 🔔 Notificaciones del cliente para la campanita (vienen de notifications_cliente en HandleInertiaRequests) */
const notificationsRaw = computed(() => page.props?.notifications_cliente ?? null)

const notificationsList = computed(() => notificationsRaw.value?.items ?? [])

const unreadCount = computed(() =>
  Number(notificationsRaw.value?.unread ?? 0)
)

/* Estado del dropdown */
const showDropdown = ref(false)

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

/* Cerrar dropdown al hacer clic fuera */
const handleClickOutside = (event) => {
  const dropdown = document.getElementById('cliente-notifications-dropdown')
  const button = document.getElementById('cliente-notifications-button')

  if (!dropdown || !button) return

  if (!dropdown.contains(event.target) && !button.contains(event.target)) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

/* Formatear fecha de cada notificación */
const formatDate = (value) => {
  if (!value) return ''
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return String(value)
  return d.toLocaleString('es-MX', {
    dateStyle: 'short',
    timeStyle: 'short',
  })
}
</script>

<template>
  <header class="flex flex-col gap-6">
    <!-- Parte de arriba -->
    <div
      class="relative z-20 flex items-center justify-between gap-4 rounded-2xl bg-white/80 backdrop-blur-md shadow-md px-6 py-4 border border-neutral-200"
    >
      <div>
        <p class="text-xs font-semibold tracking-[0.2em] text-amber-600 uppercase">
          Panel del cliente
        </p>

        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-neutral-900 flex items-center gap-2">
          <span>{{ saludo }},</span>
          <span class="text-amber-600">{{ user?.name || 'Cliente' }}</span>
        </h1>

        <p class="mt-2 text-sm text-neutral-600">
          Gestiona tu compra, pedidos y datos desde aquí.
        </p>

        <!-- 🌍 Estado de la tienda (Abierto / Cerrado) -->
        <div v-if="cfg" class="mt-3 flex items-center gap-2 text-xs text-slate-500">
          <span>Horario:</span>
          <span class="font-medium text-slate-700">{{ horarioTexto }}</span>

          <span
            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1"
            :class="estadoClase"
          >
            <span
              class="h-1.5 w-1.5 rounded-full"
              :class="cfg.is_open_now ? 'bg-emerald-500' : 'bg-rose-500'"
            />
            {{ estadoTienda }}
          </span>
        </div>

        <!-- DEBUG: BORRAR LUEGO -->
        <p class="text-[10px] text-slate-400">
          notifications_cliente: {{ $page.props.notifications_cliente ? 'OK' : 'null' }}
        </p>
      </div>

      <!-- 🔥 Campanita + Cerrar Sesión -->
      <div class="flex items-center gap-3">
        <!-- 🔔 Campanita notificaciones -->
        <div class="relative">
          <button
            id="cliente-notifications-button"
            type="button"
            @click.stop="toggleDropdown"
            class="relative inline-flex items-center justify-center rounded-full p-2 hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-300"
            aria-label="Notificaciones"
          >
            <span class="text-2xl">🔔</span>

            <!-- Badge de no leídas -->
            <span
              v-if="unreadCount > 0"
              class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <!-- Dropdown -->
          <div
            v-if="showDropdown"
            id="cliente-notifications-dropdown"
            class="absolute right-0 z-40 mt-2 w-80 rounded-xl border border-amber-100 bg-white shadow-xl"
          >
            <div class="flex items-center justify-between border-b px-4 py-3">
              <p class="text-sm font-semibold text-amber-800">
                Notificaciones
              </p>
              <span
                v-if="unreadCount > 0"
                class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700"
              >
                {{ unreadCount }} nuevas
              </span>
            </div>

            <!-- Lista -->
            <div v-if="notificationsList.length" class="max-h-72 overflow-y-auto">
              <button
                v-for="n in notificationsList"
                :key="n.id"
                type="button"
                class="block w-full border-b border-amber-50 px-4 py-3 text-left text-sm hover:bg-amber-50"
              >
                <div class="flex items-start gap-2">
                  <span class="mt-1 text-xs text-amber-500">●</span>
                  <div class="flex-1">
                    <div class="text-[13px] font-semibold text-slate-800">
                      {{ n.titulo ?? 'Actualización de tu pedido' }}
                    </div>
                    <div class="mt-0.5 text-[12px] text-slate-600 line-clamp-2">
                      {{ n.mensaje }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-400">
                      {{ formatDate(n.created_at) }}
                    </div>
                  </div>
                </div>
              </button>
            </div>

            <!-- Sin notificaciones -->
            <div v-else class="px-4 py-4 text-xs text-slate-500">
              No tienes notificaciones nuevas por ahora.
            </div>

            <!-- Footer -->
            <div class="border-t px-4 py-2 text-right">
              <Link
                :href="route('cliente.notificaciones.index')"
                class="text-xs font-semibold text-amber-700 hover:underline"
                @click="showDropdown = false"
              >
                Ver todas las notificaciones
              </Link>
            </div>
          </div>
        </div>

        <!-- Botón Cerrar Sesión (igual que lo tenías) -->
        <form method="post" action="/logout">
          <input type="hidden" name="_token" :value="$page.props.csrf_token" />
          <button
            type="button"
            class="rounded-xl border border-rose-500 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 active:scale-95 transition"
            @click.prevent="$inertia.post(route('logout'))"
          >
            Cerrar sesión
          </button>
        </form>
      </div>
    </div>

    <!-- Tabs -->
    <nav class="w-full text-sm">
      <div class="grid grid-cols-6 gap-3 w-full">
        <!-- Inicio -->
        <Link
          :href="route('cliente.inicio')"
          class="tab-led group inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-300 bg-white text-sm font-medium text-slate-700 shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
          :class="{ 'bg-amber-50 border-amber-300 text-amber-700 shadow-md': route().current('cliente.inicio') }"
        >
          🏠 Inicio
        </Link>

        <Link
          :href="route('catalogo.index')"
          class="tab-led group inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>🛍️</span>
          <span>Catálogo</span>
        </Link>

        <Link
          :href="route('cliente.pedidos.index')"
          class="tab-led group inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>📦</span>
          <span>Mis pedidos</span>
        </Link>

        <Link
          :href="route('cliente.perfil.edit')"
          class="tab-led group inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>👤</span>
          <span>Mis datos</span>
        </Link>

        <Link
          :href="route('cliente.carrito.index')"
          class="tab-led group inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-300 bg-white text-neutral-700 font-medium shadow-sm hover:bg-amber-50 hover:border-amber-300 active:scale-95 transition whitespace-nowrap"
        >
          <span>🧺</span>
          <span>Carrito</span>
        </Link>

        <Link
          :href="route('cliente.notificaciones.index')"
          class="tab-led group inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium border
                bg-white border-slate-200 text-slate-700 hover:bg-slate-50 whitespace-nowrap"
          :class="{
            'bg-amber-50 border-amber-300 text-amber-700':
              $page.component === 'Cliente/Notificaciones/Index',
          }"
        >
          <span>🔔</span>
          <span>Notificaciones</span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<style scoped>
.tab-led {
  position: relative;
  overflow: hidden;
}

/* Línea LED inferior */
.tab-led::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, #f97316, #facc15, #f97316, #facc15);
  background-size: 200% 100%;
  transform: translateY(100%);
  opacity: 0;
  transition: opacity 0.2s ease, transform 0.2s ease;
}

/* Al pasar el mouse / foco, se enciende y se anima como luces */
.tab-led:hover::after,
.tab-led:focus-visible::after {
  opacity: 1;
  transform: translateY(0);
  animation: ledFlow 1.5s linear infinite;
}

@keyframes ledFlow {
  0% {
    background-position: 0% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
