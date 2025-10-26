<!-- resources/js/Pages/Auth/Login.vue -->
<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const page = usePage()
const status = page.props?.status ?? null

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPwd = ref(false)

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Iniciar sesión" />

  <div class="min-h-screen grid lg:grid-cols-2 bg-neutral-50">
    <!-- Panel visual (izquierda) -->
    <div class="hidden lg:flex relative overflow-hidden">
       <img
        src="/login.jpg"
         alt="Fondo"
        class="absolute inset-0 h-full w-full object-cover"
      />
      <div class="absolute inset-0 bg-amber-600/70 mix-blend-multiply" />
      <div class="relative z-10 p-10 flex flex-col justify-between text-white">
        <header class="flex items-center gap-3">
          <img src="/logo.jpg" alt="Logo" class="h-10 w-10 rounded-lg bg-white/90 p-1" />
          <span class="text-xl font-semibold">Pollería Pepe</span>
        </header>

        <div class="max-w-md">
          <h2 class="text-4xl font-extrabold leading-tight">¡Bienvenido de vuelta!</h2>
          <p class="mt-3 text-white/90">
            Administra productos, usuarios y pedidos desde un panel moderno y sencillo.
          </p>
        </div>

        <footer class="text-sm text-white/80">
          © {{ new Date().getFullYear() }} Pollería Pepe. Todos los derechos reservados.
        </footer>
      </div>
    </div>

    <!-- Formulario (derecha) -->
    <div class="flex items-center justify-center px-6 py-10">
      <div class="w-full max-w-md">
        <!-- Logo móvil -->
        <div class="mb-6 flex items-center gap-3 lg:hidden">
          <img src="/logo.jpg" alt="Logo" class="h-10 w-10 rounded-lg" />
          <span class="text-lg font-semibold text-neutral-800">Pollería Pepe</span>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white shadow-sm">
          <div class="px-6 py-6 sm:px-8 sm:py-8">
            <h1 class="text-2xl font-bold text-neutral-900">Iniciar sesión</h1>
            <p class="mt-1 text-sm text-neutral-600">
              Accede con tu correo y contraseña.
            </p>

            <!-- Estado (por ejemplo, verificación de email) -->
            <div v-if="status" class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
              {{ status }}
            </div>

            <!-- Errores globales -->
            <div v-if="Object.keys(form.errors).length" class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
              Revisa los campos marcados e inténtalo de nuevo.
            </div>

            <form @submit.prevent="submit" class="mt-6 space-y-5">
              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-neutral-700">Correo</label>
                <input
                  id="email"
                  type="email"
                  v-model="form.email"
                  required
                  autocomplete="username"
                  class="mt-1 w-full rounded-lg border border-neutral-300 px-3 py-2 text-neutral-900 placeholder-neutral-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                  placeholder="tucorreo@ejemplo.com"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
              </div>

              <!-- Password -->
              <div>
                <div class="flex items-center justify-between">
                  <label for="password" class="block text-sm font-medium text-neutral-700">Contraseña</label>
                  <Link
                    v-if="route().has('password.request')"
                    :href="route('password.request')"
                    class="text-sm font-medium text-amber-700 hover:text-amber-800"
                  >
                    ¿Olvidaste tu contraseña?
                  </Link>
                </div>

                <div class="mt-1 relative">
                  <input
                    :type="showPwd ? 'text' : 'password'"
                    id="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 pr-10 text-neutral-900 placeholder-neutral-400 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    class="absolute inset-y-0 right-0 px-3 text-neutral-500 hover:text-neutral-700"
                    @click="showPwd=!showPwd"
                    aria-label="Mostrar/Ocultar contraseña"
                  >
                    <span v-if="!showPwd">👁️</span>
                    <span v-else>🙈</span>
                  </button>
                </div>
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
              </div>

              <!-- Remember me -->
              <div class="flex items-center gap-2">
                <input
                  id="remember"
                  type="checkbox"
                  v-model="form.remember"
                  class="h-4 w-4 rounded border-neutral-300 text-amber-600 focus:ring-amber-500"
                />
                <label for="remember" class="text-sm text-neutral-700">Recordarme</label>
              </div>

              <!-- Botón -->
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-lg bg-amber-600 px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-400 disabled:opacity-60"
              >
                {{ form.processing ? 'Entrando…' : 'Entrar' }}
              </button>
            </form>

            <!-- Registro -->
            <p class="mt-6 text-center text-sm text-neutral-600">
              ¿No tienes cuenta?
              <Link
                v-if="route().has('register')"
                :href="route('register')"
                class="font-medium text-amber-700 hover:text-amber-800"
              >
                Crear una cuenta
              </Link>
            </p>
          </div>
        </div>

        <!-- Mini footer -->
        <p class="mt-6 text-center text-xs text-neutral-500">
          Protegido con buenas prácticas de seguridad · Laravel + Inertia
        </p>
      </div>
    </div>
  </div>
</template>
