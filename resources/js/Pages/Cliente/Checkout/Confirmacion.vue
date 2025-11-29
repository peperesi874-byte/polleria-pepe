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
  <div class="max-w-5xl mx-auto py-10 px-4 space-y-8">
    <!-- Mensaje -->
    <div class="p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
      ✔ Tu pedido se registró correctamente.
    </div>

    <!-- Información del pedido -->
    <div class="p-4 border rounded-lg bg-white grid md:grid-cols-2 gap-4">
      <div>
        <div><strong>Folio:</strong> {{ pedido.folio }}</div>
        <div><strong>Estado:</strong> {{ pedido.estado }}</div>
      </div>
      <div>
        <div><strong>Fecha y hora:</strong> {{ pedido.created_at }}</div>
        <div>
          <strong>Tipo de entrega:</strong>
          <span>
            {{
              pedido.tipo_entrega === 'domicilio'
                ? 'Domicilio'
                : pedido.tipo_entrega === 'mostrador'
                  ? 'Mostrador'
                  : 'Recoger'
            }}
          </span>
        </div>
      </div>
    </div>

    <!-- Dirección SI aplica -->
    <div v-if="direccion" class="p-4 border rounded-lg bg-white">
      <h3 class="font-semibold mb-2">Dirección de entrega</h3>
      <p>
        {{ direccion.calle }} {{ direccion.numero }},
        {{ direccion.colonia }}, CP {{ direccion.cp }},
        {{ direccion.ciudad }}
      </p>
    </div>

    <!-- Resumen -->
    <div class="p-4 border rounded-lg bg-white">
      <h3 class="font-semibold mb-3">Resumen del pedido</h3>

      <!-- Lista de productos -->
      <div v-if="items.length" class="space-y-3">
        <div
          v-for="(it, i) in items"
          :key="i"
          class="flex justify-between border-b pb-2"
        >
          <div>
            <div class="font-medium">
              {{ it.nombre }}
            </div>
            <div class="text-sm text-gray-500">
              {{ Number(it.cantidad ?? 0) }} × $
              {{ Number(it.precio ?? 0).toFixed(2) }}
            </div>
          </div>
          <div class="font-semibold">
            ${{ Number(it.subtotal ?? 0).toFixed(2) }}
          </div>
        </div>
      </div>

      <!-- Si por alguna razón no llegan items -->
      <div v-else class="text-gray-500">
        No se encontraron los productos del pedido.
      </div>

      <div class="text-right mt-4 text-lg font-bold">
        Total: ${{ total.toFixed(2) }}
      </div>
    </div>

    <!-- Acciones -->
    <div class="flex justify-end gap-3">
      <Link :href="`/cliente/pedidos/${pedido.id}`" class="px-4 py-2 border rounded">
        Ver pedido
      </Link>
      <Link
        href="/cliente/pedidos"
        class="px-4 py-2 bg-red-500 text-white rounded"
      >
        Mis pedidos
      </Link>
    </div>
  </div>
</template>
