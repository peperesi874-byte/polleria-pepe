<script setup>
import VendedorLayout from '@/Layouts/VendedorLayout.vue'
</script>

<template>
  <VendedorLayout>
    <template #header>
      <h1 class="text-2xl font-semibold text-gray-900">Reportes operativos</h1>
    </template>

    <div class="mx-auto max-w-7xl p-6">
      <div class="rounded-xl border bg-white p-6">
        <p class="text-gray-700">Aquí irán los reportes básicos del vendedor (ventas del día, pedidos atendidos, etc.).</p>
      </div>
    </div>
  </VendedorLayout>
</template>
