<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import ClienteHeader from '@/Components/ClienteHeader.vue'

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
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- 🔹 Encabezado global del cliente -->
    <ClienteHeader />

    <!-- 🔸 Contenido: Perfil -->
    <section class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] gap-6">
      <!-- FORMULARIO -->
      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-7">
        <header class="mb-6">
          <h1 class="text-xl font-semibold text-slate-900">
            Mi perfil
          </h1>
          <p class="mt-1 text-sm text-slate-500">
            Mantén tus datos actualizados para facilitar tus pedidos y entregas.
          </p>
        </header>

        <form @submit.prevent="save" class="space-y-6">
          <!-- Nombre y apellido -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                Nombre
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
              />
              <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">
                {{ form.errors.name }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                Apellido
              </label>
              <input
                v-model="form.apellido"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
              />
              <p v-if="form.errors.apellido" class="mt-1 text-xs text-red-600">
                {{ form.errors.apellido }}
              </p>
            </div>
          </div>

          <!-- Correo y teléfono -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                Correo electrónico
              </label>
              <input
                v-model="form.email"
                type="email"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 cursor-not-allowed shadow-inner"
              />
              <p class="mt-1 text-xs text-slate-500 italic">
                El correo no se puede modificar.
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                Teléfono
              </label>
              <input
                v-model="form.telefono"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                @input="soloNumeros"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
              />
              <p v-if="form.errors.telefono" class="mt-1 text-xs text-red-600">
                {{ form.errors.telefono }}
              </p>
            </div>
          </div>

          <!-- Dirección -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Dirección
            </label>
            <input
              v-model="form.direccion"
              type="text"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
              placeholder="Calle, número, colonia"
            />
            <p v-if="form.errors.direccion" class="mt-1 text-xs text-red-600">
              {{ form.errors.direccion }}
            </p>
          </div>

          <!-- Ciudad -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">
                Ciudad
              </label>
              <select
                v-model="form.ciudad"
                disabled
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 cursor-not-allowed shadow-inner"
              >
                <option value="Tapachula, Chiapas">Tapachula, Chiapas</option>
              </select>
              <p class="mt-1 text-xs text-slate-500 italic">
                El servicio actualmente solo está disponible en
                <span class="font-semibold">Tapachula, Chiapas</span>.
              </p>
            </div>
          </div>

          <!-- Botón y mensaje -->
          <div class="pt-2 flex items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
              Los datos se utilizan únicamente para gestionar tus pedidos y entregas.
            </p>

            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-amber-600 active:scale-95 transition disabled:opacity-60"
              :disabled="form.processing"
            >
              Guardar cambios
            </button>
          </div>

          <p
            v-if="form.recentlySuccessful"
            class="mt-2 text-sm text-emerald-600 text-right"
          >
            Perfil actualizado correctamente.
          </p>
        </form>
      </div>

      <!-- COLUMNA DERECHA: info / ayuda -->
      <aside class="space-y-4">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
          <h2 class="text-sm font-semibold text-slate-900">
            Consejos para un perfil completo
          </h2>
          <ul class="mt-3 space-y-2 text-sm text-slate-600 list-disc list-inside">
            <li>Usa tu nombre real para que el repartidor pueda identificarte.</li>
            <li>Verifica que tu teléfono esté actualizado para cualquier aclaración.</li>
            <li>
              Escribe una dirección clara (calle, número, referencias) para evitar retrasos en la entrega.
            </li>
          </ul>
        </div>

        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 text-sm text-amber-900">
          <h3 class="font-semibold mb-1">
            ¿Por qué es importante tu información?
          </h3>
          <p>
            Tus datos se usan únicamente para procesar tus pedidos, coordinar entregas y mantenerte informado
            del estado de tus compras.
          </p>
        </div>
      </aside>
    </section>
  </div>
</template>
