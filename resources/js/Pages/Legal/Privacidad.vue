<template>
  <div class="max-w-3xl mx-auto py-10 px-4 text-sm leading-relaxed text-neutral-700">
    <h1 class="text-2xl font-bold mb-4">Aviso de Privacidad</h1>

    <p>
      En <strong>Pollería Pepe</strong>, con domicilio en 
      <strong>25 Calle Oriente No. 15, Col. Centro, Tapachula, Chiapas</strong>,
      somos responsables del uso y protección de los datos personales que usted nos proporciona.
    </p>

    <h2 class="font-semibold mt-6 mb-2">Datos que recopilamos</h2>
    <ul class="list-disc ml-6 space-y-1">
      <li>Nombre completo</li>
      <li>Teléfono y WhatsApp</li>
      <li>Dirección de entrega</li>
      <li>Pedidos realizados</li>
    </ul>

    <h2 class="font-semibold mt-6 mb-2">¿Para qué usamos estos datos?</h2>
    <p>Los datos se utilizan con fines de:</p>
    <ul class="list-disc ml-6 space-y-1">
      <li>Registro de clientes</li>
      <li>Entrega de pedidos</li>
      <li>Factura en caso de solicitarla</li>
      <li>Mejorar el servicio y atención</li>
    </ul>

    <h2 class="font-semibold mt-6 mb-2">¿Se comparten con terceros?</h2>
    <p>
      No vendemos ni compartimos sus datos con terceros. Solo se utilizan dentro del sistema
      para la operación del negocio.
    </p>

    <h2 class="font-semibold mt-6 mb-2">¿Cómo solicitar la eliminación de mis datos?</h2>
    <p>
      Usted puede solicitar la modificación o eliminación de su información enviando un mensaje 
      a WhatsApp al <strong>+52 962 555 55 55</strong>.
    </p>

    <p class="mt-8">
      Última actualización: {{ new Date().toLocaleDateString('es-MX') }}
    </p>
  </div>
</template>
