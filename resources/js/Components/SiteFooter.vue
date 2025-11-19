<template>
  <footer class="mt-16 bg-neutral-900 text-neutral-200 border-t border-amber-500/30">
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12
             grid grid-cols-1 md:grid-cols-4 gap-10"
    >
      <!-- 🐔 MARCA -->
      <div class="md:col-span-2">
        <div class="flex items-center gap-3">
          <img src="/logo-white.jpg" alt="Pollería Pepe" class="h-10 w-auto drop-shadow-md" />
          <div class="flex flex-col">
            <span class="font-bold text-xl tracking-wide text-white">Pollería Pepe</span>
            <span class="text-xs uppercase tracking-[0.2em] text-amber-400/90">
              Calidad y frescura real
            </span>
          </div>
        </div>

        <p class="mt-4 text-sm text-neutral-400 leading-relaxed max-w-md">
          Pollo fresco todos los días. Con la calidad y el servicio que tu mesa se merece.
        </p>
      </div>

      <!-- 📞 CONTACTO -->
      <div>
        <h4 class="font-semibold text-sm uppercase tracking-wider text-amber-400/90">
          Contacto
        </h4>
        <ul class="mt-4 space-y-2 text-sm">
          <li class="flex items-center gap-2">
            📞 
            <a href="tel:+529625555555" class="hover:text-white transition">
              (962) 555 55 55
            </a>
          </li>
          <li class="flex items-start gap-2">
            📍 
            25 Calle Oriente No. 15, Col. Centro, Tapachula, Chiapas
          </li>
          <li class="flex items-center gap-2">
            🗓️ 
            Horario: Lun–Sáb 8:00–18:00
          </li>
        </ul>
      </div>

      <!-- ⚖ LEGAL -->
      <div>
        <h4 class="font-semibold text-sm uppercase tracking-wider text-amber-400/90">
          Legal
        </h4>
        <ul class="mt-4 space-y-2 text-sm">
          <li>
            <Link
              :href="route('legal.terminos')"
              class="hover:text-white hover:underline transition"
            >
              Términos y condiciones
            </Link>
          </li>
          <li>
            <Link
              :href="route('legal.privacidad')"
              class="hover:text-white hover:underline transition"
            >
              Aviso de privacidad
            </Link>
          </li>
        </ul>
      </div>
    </div>

    <!-- COPYRIGHT -->
    <div class="border-t border-neutral-800/70">
      <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4
               text-[13px] text-neutral-500
               flex flex-col md:flex-row items-center justify-between gap-2"
      >
        <span>
          © {{ new Date().getFullYear() }} Pollería Pepe. Todos los derechos reservados.
        </span>

        <span class="flex items-center gap-1">
          Hecho con <span class="animate-pulse text-amber-400">❤️</span> en Tapachula, Chiapas
        </span>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
