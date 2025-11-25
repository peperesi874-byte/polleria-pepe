<!-- resources/js/Pages/Auth/ForgotPassword.vue -->
<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  status: {
    type: String,
    default: '',
  },
})

const form = useForm({
  email: '',
})

const submit = () => {
  form.post(route('password.email'), {
    onFinish: () => form.reset('email'),
  })
}
</script>

<template>
  <Head title="Recuperar contraseña" />

  <div class="min-h-screen bg-gradient-to-br from-amber-50 via-white to-indigo-50 flex items-center justify-center px-4 py-8">
    <div
      class="w-full max-w-5xl bg-white/80 backdrop-blur rounded-3xl shadow-xl overflow-hidden grid grid-cols-1 lg:grid-cols-2"
    >
      <!-- LADO IZQUIERDO: FORMULARIO -->
      <div class="px-8 sm:px-10 py-8 sm:py-10">
        <!-- Logo + título -->
        <div class="flex items-center gap-3 mb-6">
          <img
            src="/logo.jpg"
            alt="Pollería Pepe"
            class="h-12 w-12 rounded-2xl shadow-sm ring-2 ring-amber-200 object-cover"
          />
          <div class="flex flex-col">
            <span class="text-xs font-semibold tracking-[0.18em] text-amber-500 uppercase">
              Pollería Pepe
            </span>
            <span class="text-sm text-neutral-500">
              Área de recuperación de acceso
            </span>
          </div>
        </div>

        <!-- MENSAJE DE ÉXITO -->
        <div
          v-if="props.status"
          class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-start gap-3"
        >
          <span class="mt-0.5">✅</span>
          <div>
            <p class="font-semibold text-emerald-900">
              ¡Correo enviado!
            </p>
            <p class="text-xs sm:text-sm mt-0.5">
              {{ props.status }}
            </p>
            <p class="text-xs text-emerald-700 mt-1">
              Revisa tu bandeja de entrada o la carpeta de spam con el enlace para restablecer tu contraseña.
            </p>
          </div>
        </div>

        <!-- Títulos -->
        <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900">
          ¿Olvidaste tu contraseña?
        </h1>
        <p class="mt-2 text-sm text-neutral-600 leading-relaxed">
          No pasa nada. Escribe el correo que usas para iniciar sesión y te
          enviaremos un enlace para que puedas crear una nueva contraseña.
        </p>

        <!-- FORM -->
        <form class="mt-6 space-y-5" @submit.prevent="submit">
          <!-- Email -->
          <div>
            <label
              for="email"
              class="block text-sm font-medium text-neutral-800 mb-2"
            >
              Correo electrónico
            </label>

            <div
              class="relative flex items-center rounded-2xl border bg-white shadow-sm
                     focus-within:ring-2 focus-within:ring-amber-400 focus-within:border-amber-400
                     transition overflow-hidden"
              :class="form.errors.email ? 'border-rose-300 ring-rose-200' : 'border-neutral-200'"
            >
              <div class="pl-3 pr-2 text-neutral-400">
                📧
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                autocomplete="email"
                placeholder="tucorreo@ejemplo.com"
                class="flex-1 border-0 bg-transparent py-3 pr-4 text-sm text-neutral-800 placeholder-neutral-400 focus:outline-none"
              />
            </div>

            <p
              v-if="form.errors.email"
              class="mt-1 text-xs text-rose-600"
            >
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Botón -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl
                   bg-amber-500 px-4 py-3 text-sm font-semibold text-white
                   shadow-md shadow-amber-300/50 hover:bg-amber-600
                   disabled:opacity-60 disabled:cursor-not-allowed
                   transition active:scale-[0.99]"
          >
            <span v-if="!form.processing">Enviar enlace de recuperación</span>
            <span v-else>Enviando enlace...</span>
          </button>

          <!-- Volver -->
          <div class="text-center">
            <a
              :href="route('login')"
              class="inline-flex items-center gap-1 text-xs sm:text-sm text-neutral-500 hover:text-amber-600 font-medium"
            >
              ← Volver al inicio de sesión
            </a>
          </div>
        </form>
      </div>

      <!-- LADO DERECHO: INFO / GRADIENTE -->
      <div
        class="hidden lg:flex flex-col justify-between p-8 text-white
               bg-gradient-to-br from-amber-400 via-orange-400 to-indigo-500"
      >
        <div class="space-y-4">
          <div
            class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1 text-xs font-medium backdrop-blur"
          >
            <span class="text-yellow-200">🔒</span>
            <span>Seguridad para tu cuenta</span>
          </div>

          <h2 class="text-2xl font-bold leading-snug">
            Mantén tu acceso siempre seguro
          </h2>
          <p class="text-sm text-amber-50/90 leading-relaxed">
            El enlace de recuperación es válido por un tiempo limitado.
            Si no fuiste tú quien pidió el cambio, simplemente ignora el correo.
          </p>
        </div>

        <div class="mt-6 space-y-2 text-xs text-amber-50/95">
          <p class="font-semibold tracking-wide uppercase text-amber-100/90">
            Consejos rápidos:
          </p>
          <ul class="space-y-1 list-disc list-inside">
            <li>Usa una contraseña única para tu cuenta.</li>
            <li>No la compartas con nadie más.</li>
            <li>Evita usar fechas de nacimiento o datos fáciles de adivinar.</li>
          </ul>
        </div>

        <p class="mt-6 text-[11px] text-amber-100/80">
          © 2025 Pollería Pepe — Sistema de gestión de pedidos.
        </p>
      </div>
    </div>
  </div>
</template>
