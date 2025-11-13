<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  pedido: {
    type: Object,
    required: true,
  },
})

const p = computed(() => props.pedido ?? {})

const esDomicilio = computed(() => (p.value?.tipo_entrega ?? '') === 'domicilio')

const money = n =>
  new Intl.NumberFormat('es-MX', { style:'currency', currency:'MXN' }).format(Number(n ?? 0))
</script>

<template>
  <div class="min-h-screen bg-neutral-50">
    <!-- Header mínimo -->
    <header class="bg-white border-b">
      <div class="max-w-5xl mx-auto px-4 py-4">
        <h1 class="text-xl font-semibold">Confirmación de pedido</h1>
      </div>
    </header>

    <main class="max-w-5xl mx-auto p-6 space-y-6">
      <!-- Mensaje de confirmación -->
      <section class="rounded-xl border bg-white p-4">
        <p class="text-green-700 font-medium">
          ✅ Tu pedido se registró correctamente.
        </p>
      </section>

      <!-- Datos básicos del pedido -->
      <section class="rounded-xl border bg-white p-4">
        <div class="grid sm:grid-cols-2 gap-3 text-sm">
          <div>
            <span class="text-neutral-500">Folio:</span>
            <strong class="ml-2">{{ p.folio ?? ('#' + p.id) }}</strong>
          </div>
          <div>
            <span class="text-neutral-500">Fecha y hora:</span>
            <strong class="ml-2">{{ p.created_at }}</strong>
          </div>
          <div>
            <span class="text-neutral-500">Estado:</span>
            <strong class="ml-2 capitalize">{{ p.estado }}</strong>
          </div>
          <div>
            <span class="text-neutral-500">Tipo de entrega:</span>
            <strong class="ml-2 capitalize">{{ p.tipo_entrega }}</strong>
          </div>
        </div>
      </section>

      <!-- Dirección (solo si es domicilio) -->
      <section v-if="esDomicilio" class="rounded-xl border bg-white p-4">
        <h2 class="font-medium mb-2">Dirección de entrega</h2>
        <div class="text-sm text-neutral-800">
          <div>{{ p.direccion?.calle }} {{ p.direccion?.numero }}</div>
          <div>{{ p.direccion?.colonia }}</div>
          <div>CP {{ p.direccion?.cp }} · {{ p.direccion?.ciudad }}</div>
        </div>
      </section>

      <!-- Resumen de productos -->
      <section class="rounded-xl border bg-white p-4">
        <h2 class="font-medium mb-2">Resumen del pedido</h2>
        <ul class="divide-y">
          <li
            v-for="(it, i) in (p.items ?? [])"
            :key="i"
            class="flex justify-between py-2 text-sm"
          >
            <span>{{ it.nombre }} × {{ it.cantidad }}</span>
            <span>{{ money(it.subtotal ?? (it.precio * it.cantidad)) }}</span>
          </li>
        </ul>
        <div class="flex justify-end pt-3 text-base">
          <div>
            <span class="text-neutral-600 mr-2">Total:</span>
            <strong>{{ money(p.total) }}</strong>
          </div>
        </div>
      </section>

      <!-- Notas -->
      <section v-if="p.notas" class="rounded-xl border bg-white p-4">
        <h2 class="font-medium mb-2">Notas</h2>
        <p class="text-sm text-neutral-800 whitespace-pre-line">{{ p.notas }}</p>
      </section>

      <!-- Acciones mínimas -->
      <div class="flex flex-wrap gap-3 justify-end">
        <Link
          :href="route('cliente.pedidos.show', p.id)"
          class="px-4 py-2 rounded-lg border hover:bg-neutral-50 text-sm"
        >
          Ver pedido
        </Link>
        <Link
          :href="route('cliente.pedidos.index')"
          class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 text-sm"
        >
          Mis pedidos
        </Link>
      </div>
    </main>

    <footer class="border-t bg-white mt-10">
      <div class="max-w-5xl mx-auto px-4 py-3 text-xs text-neutral-500">
        Pollería Pepe · Confirmación
      </div>
    </footer>
  </div>
</template>
