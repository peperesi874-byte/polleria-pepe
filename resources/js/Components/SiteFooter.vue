<template>
  <footer class="mt-16 bg-neutral-900 text-neutral-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
      <div>
        <div class="flex items-center gap-3">
          <img :src="'/logo-white.jpg'" alt="Pollería Pepe" class="h-8 w-auto" />
          <span class="font-semibold text-lg">Pollería Pepe</span>
        </div>
        <p class="mt-3 text-sm text-neutral-400">
          Pollo fresco todos los días. Calidad y servicio para tu mesa.
        </p>
      </div>

      <div>
        <h4 class="font-semibold text-sm uppercase tracking-wider text-neutral-300">Contacto</h4>
        <ul class="mt-3 space-y-2 text-sm">
          <li>Tel: (###) ### ####</li>
          <li>WhatsApp: (###) ### ####</li>
          <li>25 Calle Oriente No. 15, Col. Centro Tapachula, Chiapas</li>
          <li>Horario: Lun–Sáb 8:00–18:00</li>
        </ul>
      </div>

      <div>
        <h4 class="font-semibold text-sm uppercase tracking-wider text-neutral-300">Legal</h4>
        <ul class="mt-3 space-y-2 text-sm">
          <li><a href="#" class="hover:underline">Términos y condiciones</a></li>
          <li><a href="#" class="hover:underline">Aviso de privacidad</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-neutral-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-neutral-500 flex items-center justify-between">
        <span>© {{ new Date().getFullYear() }} Pollería Pepe. Todos los derechos reservados.</span>
        <span>Hecho con ❤️</span>
      </div>
    </div>
  </footer>
</template>
