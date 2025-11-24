<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  token: {
    type: String,
    required: true,
  },
  email: {
    type: String,
    default: '',
  },
})

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Nueva contraseña" />

  <div class="min-h-screen flex items-center justify-center bg-slate-100">
    <div
      class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 space-y-6 border border-slate-200"
    >
      <!-- Logo -->
      <div class="flex flex-col items-center gap-2">
        <svg
          class="w-10 h-10 text-amber-500"
          viewBox="0 0 24 24"
          fill="none"
        >
          <path
            fill="currentColor"
            d="M4 8.25 12 3l8 5.25v7.5L12 21l-8-5.25z"
          />
        </svg>
        <h1 class="text-xl font-semibold text-slate-800">
          Nueva contraseña
        </h1>
        <p class="text-sm text-slate-500 text-center">
          Ingresa tu nueva contraseña para la cuenta asociada a este correo.
        </p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <!-- Email (solo lectura) -->
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-slate-700">
            Correo electrónico
          </label>
          <input
            type="email"
            v-model="form.email"
            readonly
            class="block w-full rounded-lg border-slate-300 bg-slate-50 text-slate-600 text-sm
                   focus:ring-amber-500 focus:border-amber-500"
          />
        </div>

        <!-- Nueva contraseña -->
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-slate-700">
            Nueva contraseña
          </label>
          <input
            type="password"
            v-model="form.password"
            autocomplete="new-password"
            class="block w-full rounded-lg border-slate-300 text-sm
                   focus:ring-amber-500 focus:border-amber-500"
          />
          <p v-if="form.errors.password" class="text-xs text-red-500 mt-1">
            {{ form.errors.password }}
          </p>
        </div>

        <!-- Confirmar contraseña -->
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-slate-700">
            Confirmar contraseña
          </label>
          <input
            type="password"
            v-model="form.password_confirmation"
            autocomplete="new-password"
            class="block w-full rounded-lg border-slate-300 text-sm
                   focus:ring-amber-500 focus:border-amber-500"
          />
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-between pt-2">
          <a
            :href="route('login')"
            class="text-xs text-slate-500 hover:text-amber-600 underline-offset-2 hover:underline"
          >
            Volver al inicio de sesión
          </a>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold
                   text-white bg-amber-500 hover:bg-amber-600 disabled:opacity-60
                   disabled:cursor-not-allowed shadow-sm"
          >
            Guardar nueva contraseña
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
