<script setup>
import { Link, usePage } from '@inertiajs/vue3'
const user = usePage().props.auth?.user ?? null
</script>

<template>
  <div class="min-h-screen bg-neutral-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 shrink-0 bg-white border-r hidden md:block">
      <div class="h-16 flex items-center px-4 border-b">
        <Link :href="route('catalogo.index')" class="font-semibold">Pollería Pepe</Link>
      </div>

      <nav class="p-3 space-y-1">
        <Link :href="route('admin.dashboard')"
              class="block px-3 py-2 rounded-md hover:bg-neutral-100"
              :class="{ 'bg-neutral-100 font-medium': $page.url.startsWith('/admin/dashboard') }">
          Panel
        </Link>

        <Link :href="route('productos.index')"
              class="block px-3 py-2 rounded-md hover:bg-neutral-100"
              :class="{ 'bg-neutral-100 font-medium': $page.url.startsWith('/productos') }">
          Productos
        </Link>
<Link :href="route('admin.usuarios.index')"
      class="block px-3 py-2 rounded-md hover:bg-neutral-100"
      :class="{ 'bg-neutral-100 font-medium': $page.url.startsWith('/admin/usuarios') }">
  Usuarios
</Link>
        <Link :href="route('admin.usuarios.index')"
              class="block px-3 py-2 rounded-md hover:bg-neutral-100"
              :class="{ 'bg-neutral-100 font-medium': $page.url.startsWith('/admin/usuarios') }">
          Usuarios
        </Link>

        <Link :href="route('admin.reportes.index')"
              class="block px-3 py-2 rounded-md hover:bg-neutral-100"
              :class="{ 'bg-neutral-100 font-medium': $page.url.startsWith('/admin/reportes') }">
          Reportes
        </Link>
      </nav>
    </aside>

    <!-- Contenido -->
    <div class="flex-1 min-w-0 flex flex-col">
      <!-- Topbar -->
      <header class="h-16 bg-white border-b flex items-center justify-between px-4">
        <h1 class="text-lg font-semibold">
          <slot name="title">Administración</slot>
        </h1>
        <div class="text-sm text-neutral-600">Hola, {{ user?.name }}</div>
      </header>

      <!-- Page -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
