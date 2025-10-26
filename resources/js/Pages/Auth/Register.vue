<!-- resources/js/Pages/Auth/Register.vue -->
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Crear cuenta" />

  <div class="min-h-screen grid grid-cols-1 md:grid-cols-2 bg-neutral-50">
    <!-- LADO IZQUIERDO: imagen grande -->
    <div class="relative hidden md:block">
      <img
        src="/login.jpg"
        alt="Pollos frescos - Pollería Pepe"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <!-- veladura para bajar saturación -->
      <div class="absolute inset-100 bg-white/30 backdrop-blur-[1px]"></div>
      <!-- branding sobre la imagen -->
      <div class="absolute inset-x-0 bottom-1/4 p-8 text-neutral-900">
        <h2 class="text-4xl font-extrabold leading-tight">
          ¡Regístrate y gestiona tus pedidos!
        </h2>
        <p class="mt-3 text-base text-neutral -700 max-w-md">
          Crea tu cuenta para administrar productos, realizar pedidos
          y consultar tu historial fácilmente.
        </p>
      </div>
    </div>

    <!-- LADO DERECHO: tarjeta de registro -->
    <div class="flex items-center justify-center p-6 sm:p-10">
      <div class="w-full max-w-md">
        <!-- Logo / nombre -->
        <div class="mb-8 text-center">
          <Link :href="route('catalogo.index')" class="inline-flex items-center gap-2">
            <img src="/logo.jpg" alt="Pollería Pepe" class="h-12 w-12 rounded-xl object-cover border" />
            <span class="text-2xl font-bold tracking-tight text-neutral-900">
              Pollería <span class="text-amber-600">Pepe</span>
            </span>
          </Link>
          <p class="mt-2 text-sm text-neutral-500">Crear nueva cuenta</p>
        </div>

        <form @submit.prevent="submit"
              class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm space-y-4">
          <!-- Nombre -->
          <div>
            <label for="name" class="block text-sm font-medium text-neutral-700">Nombre</label>
            <input id="name" type="text" v-model="form.name" autocomplete="name"
                   class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400" />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-neutral-700">Correo</label>
            <input id="email" type="email" v-model="form.email" autocomplete="email"
                   class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400" />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-neutral-700">Contraseña</label>
            <input id="password" type="password" v-model="form.password" autocomplete="new-password"
                   class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400" />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <!-- Confirmación -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-neutral-700">
              Confirmar contraseña
            </label>
            <input id="password_confirmation" type="password" v-model="form.password_confirmation" autocomplete="new-password"
                   class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-400" />
          </div>

          <!-- Botón -->
          <div class="pt-2">
            <button type="submit" :disabled="form.processing"
                    class="w-full rounded-xl bg-amber-500 px-4 py-2.5 font-semibold text-white shadow hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400">
              {{ form.processing ? 'Creando cuenta…' : 'Crear cuenta' }}
            </button>
          </div>
        </form>

        <!-- Enlaces secundarios -->
        <p class="mt-6 text-center text-sm text-neutral-600">
          ¿Ya tienes cuenta?
          <Link :href="route('login')" class="font-semibold text-amber-600 hover:text-amber-700">Inicia sesión</Link>
        </p>
      </div>
    </div>
  </div>
</template>
