<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h1 class="text-2xl font-semibold text-gray-900">Reportes operativos</h1>
      <p class="text-sm text-gray-500">Resumen del día seleccionado.</p>
    </template>

    <!-- 👇 AQUÍ DEJAS EXACTAMENTE IGUAL
         todo tu contenido actual:
         - tarjetas de pedidos hoy, pendientes, etc.
         - la tabla de "Pedidos sin asignar"
         - filtros, etc.
    -->
    <div class="mx-auto max-w-7xl p-6">
      <!-- TODO lo que ya tienes ahora mismo -->
    </div>
  </AuthenticatedLayout>
</template>
