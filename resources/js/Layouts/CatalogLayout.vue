<script setup>
import SiteHeader from '../Components/SiteHeader.vue'
import SiteFooter from '../Components/SiteFooter.vue'
</script>

<template>
  <div class="flex min-h-screen flex-col bg-neutral-50">
    <!-- Header -->
    <SiteHeader />

    <!-- Contenido principal -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <SiteFooter />
  </div>
</template>