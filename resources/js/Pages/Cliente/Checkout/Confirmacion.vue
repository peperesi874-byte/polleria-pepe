<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

// ✅ Recibimos solo el objeto pedido desde Inertia
const props = defineProps({
  pedido: {
    type: Object,
    default: () => ({}),
  },
})

// 🧠 Helpers reactivas
const pedido = computed(() => props.pedido ?? {})
const direccion = computed(() => pedido.value.direccion ?? null)
const items = computed(() => pedido.value.items ?? [])
const total = computed(() => Number(pedido.value.total ?? 0))
</script>

<template>
  <div class="min-h-screen bg-neutral-50">
    <div class="max-w-5xl mx-auto py-10 px-4 space-y-8">
      <!-- Hero de confirmación -->
      <section
        class="relative overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 via-amber-100 to-rose-100 px-5 py-4 text-sm text-amber-900 shadow-md"
      >
        <!-- Brillos decorativos -->
        <div
          class="pointer-events-none absolute inset-0 opacity-50"
          style="
            background:
              radial-gradient(circle at top left, rgba(253, 224, 171, 0.9), transparent 60%),
              radial-gradient(circle at bottom right, rgba(248, 113, 113, 0.65), transparent 55%);
          "
        ></div>

        <div class="relative flex items-start gap-4">
          <div
            class="flex h-11 w-11 items-center justify-center rounded-full bg-white/80 shadow-md border border-amber-200"
          >
            <span class="text-xl">✅</span>
          </div>

          <div class="space-y-1">
            <p class="text-sm font-semibold tracking-tight text-amber-950">
              ¡Gracias por tu pedido!
            </p>
            <p class="text-xs text-amber-900/90">
              Tu pedido se registró correctamente. En unos momentos podrás consultar su avance
              en la sección <span class="font-semibold">“Mis pedidos”</span>.
            </p>
            <p class="text-xs text-amber-900/80 mt-1">
              <span class="font-semibold">Folio:</span>
              <span class="ml-1">{{ pedido.folio }}</span>
            </p>
          </div>
        </div>
      </section>

      <!-- Layout principal: datos + resumen -->
      <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)] items-start">
        <!-- Columna izquierda: información del pedido + dirección -->
        <div class="space-y-4">
          <!-- Información del pedido -->
          <section
            class="relative rounded-2xl border bg-white p-4 shadow-sm text-sm overflow-hidden"
          >
            <div
              class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-rose-300 to-amber-300"
            ></div>

            <div class="grid gap-4 md:grid-cols-2 mt-1">
              <div class="space-y-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
                  Folio
                </div>
                <div class="font-semibold text-neutral-900">
                  {{ pedido.folio }}
                </div>

                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500 mt-3">
                  Estado
                </div>
                <div
                  class="inline-flex items-center gap-1 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800"
                >
                  <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                  <span class="capitalize">{{ pedido.estado }}</span>
                </div>
              </div>

              <div class="space-y-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
                  Fecha y hora
                </div>
                <div class="text-neutral-900">
                  {{ pedido.created_at }}
                </div>

                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500 mt-3">
                  Tipo de entrega
                </div>
                <div class="text-neutral-900">
                  {{
                    pedido.tipo_entrega === 'domicilio'
                      ? 'Domicilio'
                      : pedido.tipo_entrega === 'mostrador'
                        ? 'Mostrador'
                        : 'Recoger'
                  }}
                </div>
              </div>
            </div>
          </section>

          <!-- Dirección SI aplica -->
          <section
            v-if="direccion"
            class="rounded-2xl border bg-white p-4 shadow-sm text-sm flex gap-3"
          >
            <div
              class="mt-1 flex h-8 w-8 items-center justify-center rounded-full bg-amber-50 border border-amber-200 text-lg"
            >
              📍
            </div>
            <div>
              <h3 class="font-semibold mb-1 text-neutral-900">
                Dirección de entrega
              </h3>
              <p class="text-neutral-800 leading-snug">
                {{ direccion.calle }} {{ direccion.numero }},
                {{ direccion.colonia }}, CP {{ direccion.cp }},
                {{ direccion.ciudad }}
              </p>
            </div>
          </section>
        </div>

        <!-- Columna derecha: resumen del pedido -->
        <aside class="space-y-4">
          <section
            class="relative rounded-2xl border border-amber-200 bg-gradient-to-b from-white via-amber-50/70 to-rose-50/70 p-4 shadow-md text-sm overflow-hidden"
          >
            <!-- decoración -->
            <div
              class="pointer-events-none absolute inset-0 opacity-40"
              style="
                background:
                  radial-gradient(circle at top right, rgba(252, 211, 77, 0.55), transparent 60%),
                  radial-gradient(circle at bottom left, rgba(248, 113, 113, 0.5), transparent 55%);
              "
            ></div>

            <div class="relative flex items-center justify-between mb-3">
              <div>
                <h3 class="font-semibold text-neutral-900">
                  Resumen del pedido
                </h3>
                <p class="text-[11px] text-neutral-600">
                  Detalle de los productos incluidos en este pedido.
                </p>
              </div>
              <div
                class="flex h-9 w-9 items-center justify-center rounded-full bg-white/80 border border-amber-200 shadow-sm"
              >
                <span class="text-lg">🧾</span>
              </div>
            </div>

            <!-- Lista de productos -->
            <div v-if="items.length" class="relative space-y-3 max-h-56 overflow-y-auto pr-1">
              <div
                v-for="(it, i) in items"
                :key="i"
                class="flex justify-between border-b border-amber-100 pb-2"
              >
                <div>
                  <div class="font-medium text-neutral-900">
                    {{ it.nombre }}
                  </div>
                  <div class="text-xs text-neutral-600">
                    {{ Number(it.cantidad ?? 0) }} ×
                    ${{ Number(it.precio ?? 0).toFixed(2) }}
                  </div>
                </div>
                <div class="font-semibold text-neutral-900">
                  ${{ Number(it.subtotal ?? 0).toFixed(2) }}
                </div>
              </div>
            </div>

            <!-- Si por alguna razón no llegan items -->
            <div v-else class="relative text-sm text-neutral-500">
              No se encontraron los productos del pedido.
            </div>

            <div class="relative mt-4 border-t border-amber-200 pt-3 flex justify-between items-center">
              <span class="text-xs font-semibold uppercase tracking-wide text-neutral-600">
                Total
              </span>
              <span class="text-lg font-bold text-rose-700 drop-shadow-sm">
                ${{ total.toFixed(2) }}
              </span>
            </div>
          </section>
        </aside>
      </div>

      <!-- Acciones -->
      <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
        <Link
          :href="`/cliente/pedidos/${pedido.id}`"
          class="inline-flex items-center justify-center rounded-lg border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 bg-white hover:bg-neutral-50 shadow-sm"
        >
          Ver pedido
        </Link>
        <Link
          href="/cliente/pedidos"
          class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-rose-700"
        >
          Mis pedidos
        </Link>
      </div>
    </div>
  </div>
</template>
