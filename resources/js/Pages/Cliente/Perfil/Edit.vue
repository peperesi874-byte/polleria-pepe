<script setup>
import { useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const perfil = page.props.perfil

const form = useForm({
  name: perfil.name ?? '',
  apellido: perfil.apellido ?? '',
  email: perfil.email ?? '',
  telefono: perfil.telefono ?? '',
  direccion: perfil.direccion ?? '',
  ciudad: 'Tapachula, Chiapas',
})

function save() {
  form.put(route('cliente.perfil.update'))
}

function soloNumeros(e) {
  e.target.value = e.target.value.replace(/[^0-9]/g, '')
  form.telefono = e.target.value
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 font-[Poppins]"
  >
    <div
      class="w-full max-w-xl bg-white/80 backdrop-blur-md shadow-xl rounded-2xl p-8 border border-gray-200"
    >
      <h1 class="text-2xl font-semibold text-gray-800 text-center mb-6 tracking-wide">
        Mi perfil
      </h1>

      <form @submit.prevent="save" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-rose-400 focus:border-rose-400 transition"
          />
          <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Apellido</label>
          <input
            v-model="form.apellido"
            type="text"
            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-rose-400 focus:border-rose-400 transition"
          />
          <div v-if="form.errors.apellido" class="text-sm text-red-600">{{ form.errors.apellido }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
          <input
            v-model="form.email"
            type="email"
            disabled
            class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 text-gray-500 cursor-not-allowed font-medium"
          />
          <p class="text-xs text-gray-500 italic">El correo no se puede modificar.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Teléfono</label>
          <input
            v-model="form.telefono"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            @input="soloNumeros"
            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-rose-400 focus:border-rose-400 transition"
          />
          <div v-if="form.errors.telefono" class="text-sm text-red-600">{{ form.errors.telefono }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Dirección</label>
          <input
            v-model="form.direccion"
            type="text"
            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-rose-400 focus:border-rose-400 transition"
          />
          <div v-if="form.errors.direccion" class="text-sm text-red-600">{{ form.errors.direccion }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Ciudad</label>
          <select
            v-model="form.ciudad"
            disabled
            class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 text-gray-600 cursor-not-allowed font-medium"
          >
            <option value="Tapachula, Chiapas">Tapachula, Chiapas</option>
          </select>
          <p class="text-xs text-gray-500 mt-1 italic">
            El servicio actualmente solo está disponible en <b>Tapachula, Chiapas</b>.
          </p>
        </div>

        <div class="pt-4 flex justify-center">
          <button
            type="submit"
            class="px-6 py-2 bg-gradient-to-r from-rose-500 to-rose-700 text-white rounded-lg shadow hover:shadow-lg hover:brightness-110 transition-all font-semibold"
            :disabled="form.processing"
          >
            Guardar cambios
          </button>
        </div>

        <div v-if="form.recentlySuccessful" class="text-green-600 text-sm text-center mt-3">
          Perfil actualizado correctamente.
        </div>
      </form>
    </div>
  </div>
</template>
