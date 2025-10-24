<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'

// props seguras con default
const props = defineProps({
  cards: {
    type: Object,
    default: () => ({ productos: 0, pedidos: 0, usuarios: 0 })
  }
})
</script>

<template>
  <AdminLayout>
    <template #header>
      <h1 class="text-2xl font-bold">Panel de administración</h1>
    </template>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div class="rounded-xl border p-5 bg-white">
        <p class="text-neutral-500">Productos</p>
        <p class="mt-1 text-4xl font-extrabold">{{ props.cards.productos }}</p>
      </div>
      <div class="rounded-xl border p-5 bg-white">
        <p class="text-neutral-500">Pedidos</p>
        <p class="mt-1 text-4xl font-extrabold">{{ props.cards.pedidos }}</p>
      </div>
      <div class="rounded-xl border p-5 bg-white">
        <p class="text-neutral-500">Usuarios</p>
        <p class="mt-1 text-4xl font-extrabold">{{ props.cards.usuarios }}</p>
      </div>
    </section>
  </AdminLayout>
</template>
