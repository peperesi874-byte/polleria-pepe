<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

const tipo_entrega = ref('mostrador') // 'mostrador' | 'domicilio'
const id_cliente   = ref('')
const total        = ref(0)
const observaciones= ref('')

// Campos de domicilio
const entrega_nombre      = ref('')
const entrega_telefono    = ref('')
const entrega_calle       = ref('')
const entrega_numero      = ref('')
const entrega_colonia     = ref('')
const entrega_municipio   = ref('')
const entrega_referencias = ref('')

const esDomicilio = computed(() => tipo_entrega.value === 'domicilio')

function submit() {
  const payload = {
    id_cliente: id_cliente.value || null,
    tipo_entrega: tipo_entrega.value,
    total: Number(total.value ?? 0),
    observaciones: observaciones.value || null,
  }

  if (esDomicilio.value) {
    Object.assign(payload, {
      entrega_nombre: entrega_nombre.value || null,
      entrega_telefono: entrega_telefono.value || null,
      entrega_calle: entrega_calle.value || null,
      entrega_numero: entrega_numero.value || null,
      entrega_colonia: entrega_colonia.value || null,
      entrega_municipio: entrega_municipio.value || null,
      entrega_referencias: entrega_referencias.value || null,
      // JSON compacto opcional
      dom: {
        nombre: entrega_nombre.value,
        telefono: entrega_telefono.value,
        direccion: `${entrega_calle.value} #${entrega_numero.value}`.trim(),
        colonia: entrega_colonia.value,
        ciudad: entrega_municipio.value,
        referencias: entrega_referencias.value,
      },
    })
  }

  router.post(route('admin.pedidos.store'), payload)
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="max-w-3xl mx-auto p-6 space-y-6">
      <h1 class="text-2xl font-semibold">Crear pedido</h1>

      <div class="grid gap-4">
        <div class="grid grid-cols-2 gap-4">
          <label class="space-y-1">
            <span class="text-sm text-neutral-600">Tipo de entrega</span>
            <select v-model="tipo_entrega" class="w-full border rounded p-2">
              <option value="mostrador">Mostrador</option>
              <option value="domicilio">Domicilio</option>
            </select>
          </label>

          <label class="space-y-1">
            <span class="text-sm text-neutral-600">Cliente (opcional, id)</span>
            <input v-model="id_cliente" type="number" class="w-full border rounded p-2" placeholder="1" />
          </label>
        </div>

        <label class="space-y-1">
          <span class="text-sm text-neutral-600">Total</span>
          <input v-model="total" type="number" step="0.01" min="0" class="w-full border rounded p-2" />
        </label>

        <label class="space-y-1">
          <span class="text-sm text-neutral-600">Observaciones</span>
          <textarea v-model="observaciones" class="w-full border rounded p-2" rows="3"></textarea>
        </label>

        <div v-if="esDomicilio" class="border rounded p-4 space-y-3">
          <p class="font-medium">Datos de entrega a domicilio</p>

          <div class="grid grid-cols-2 gap-4">
            <label class="space-y-1">
              <span class="text-sm text-neutral-600">Nombre</span>
              <input v-model="entrega_nombre" class="w-full border rounded p-2" />
            </label>
            <label class="space-y-1">
              <span class="text-sm text-neutral-600">Teléfono</span>
              <input v-model="entrega_telefono" class="w-full border rounded p-2" />
            </label>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <label class="space-y-1">
              <span class="text-sm text-neutral-600">Calle</span>
              <input v-model="entrega_calle" class="w-full border rounded p-2" />
            </label>
            <label class="space-y-1">
              <span class="text-sm text-neutral-600">Número</span>
              <input v-model="entrega_numero" class="w-full border rounded p-2" />
            </label>
            <label class="space-y-1">
              <span class="text-sm text-neutral-600">Colonia</span>
              <input v-model="entrega_colonia" class="w-full border rounded p-2" />
            </label>
          </div>

          <label class="space-y-1">
            <span class="text-sm text-neutral-600">Municipio / Ciudad</span>
            <input v-model="entrega_municipio" class="w-full border rounded p-2" />
          </label>

          <label class="space-y-1">
            <span class="text-sm text-neutral-600">Referencias</span>
            <input v-model="entrega_referencias" class="w-full border rounded p-2" />
          </label>
        </div>

        <div class="flex justify-end">
          <button @click="submit" type="button" class="px-4 py-2 rounded bg-blue-600 text-white">
            Guardar pedido
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
