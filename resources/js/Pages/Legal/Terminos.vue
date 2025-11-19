<template>
  <div class="max-w-3xl mx-auto py-10 px-4 text-sm leading-relaxed text-neutral-700">
    <h1 class="text-2xl font-bold mb-4">Términos y Condiciones</h1>

    <p>
      El sistema de <strong>Pollería Pepe</strong> está destinado exclusivamente para
      la realización de pedidos y uso interno del negocio.
    </p>

    <h2 class="font-semibold mt-6 mb-2">Uso del sistema</h2>
    <ul class="list-disc ml-6 space-y-1">
      <li>Los datos proporcionados deben ser reales.</li>
      <li>El cliente es responsable de revisar su pedido antes de confirmarlo.</li>
      <li>El vendedor puede cancelar o modificar pedidos si hay errores.</li>
    </ul>

    <h2 class="font-semibold mt-6 mb-2">Responsabilidad</h2>
    <p>
      Pollería Pepe no se hace responsable por información incorrecta proporcionada por el usuario,
      así como tampoco por fallas técnicas ajenas al negocio (internet, electricidad, etc.).
    </p>

    <p class="mt-8">
      Última actualización: {{ new Date().toLocaleDateString('es-MX') }}
    </p>
  </div>
</template>
