<script setup>
defineProps({
  pedidos: { type: Array, default: () => [] }
})
</script>

<template>
  <div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">📦 Mis pedidos</h1>

    <div v-if="!pedidos.length" class="text-gray-600">
      Aún no tienes pedidos.
    </div>

    <table v-else class="w-full border rounded-lg overflow-hidden">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="p-3">#</th>
          <th class="p-3">Fecha</th>
          <th class="p-3">Estado</th>
          <th class="p-3">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in pedidos" :key="p.id" class="border-t">
          <td class="p-3">PED-{{ p.id }}</td>
          <td class="p-3">{{ new Date(p.created_at).toLocaleString() }}</td>
          <td class="p-3">{{ p.estado }}</td>
          <td class="p-3">${{ Number(p.total ?? 0).toFixed(2) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
