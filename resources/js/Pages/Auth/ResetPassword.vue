<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  email: {
    type: String,
    default: '',
  },
  token: {
    type: String,
    required: true,
  },
})

const form = useForm({
  token: props.token,
  email: props.email ?? '',
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

  <div
    class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-violet-50
           flex items-center justify-center px-4 py-10"
  >
    <div
      class="w-full max-w-4xl rounded-3xl bg-white/90 shadow-2xl shadow-amber-200/50
             ring-1 ring-amber-100 overflow-hidden grid md:grid-cols-2"
    >
      <!-- Columna izquierda: formulario -->
      <div class="px-8 sm:px-10 py-8 sm:py-10 flex flex-col gap-6">
        <!-- Logo + título -->
        <div class="flex items-center gap-3">
          <div
            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white
                   ring-2 ring-amber-300/70 shadow-md overflow-hidden"
          >
            <img
              src="/logo.jpg"
              alt="Pollería Pepe"
              class="h-12 w-12 object-cover"
            />
          </div>
          <div class="flex flex-col">
            <span class="text-xs font-semibold tracking-[0.18em] text-amber-600 uppercase">
              Pollería Pepe
            </span>
            <span class="text-sm text-neutral-500">
              Restablecimiento de contraseña
            </span>
          </div>
        </div>

        <header class="space-y-2">
          <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 leading-tight">
            Nueva contraseña
          </h1>
          <p class="text-sm text-neutral-600 max-w-md">
            Ingresa la nueva contraseña para la cuenta asociada a este correo.
            Procura elegir una que sea segura y fácil de recordar para ti.
          </p>
        </header>

        <!-- Formulario -->
        <form class="space-y-4" @submit.prevent="submit">
          <!-- Email (solo lectura) -->
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-neutral-600 uppercase tracking-wide">
              Correo electrónico
            </label>
            <div
              class="flex items-center gap-2 rounded-xl border bg-neutral-50 px-3 py-2.5
                     text-sm text-neutral-700"
            >
              <span class="text-neutral-400 text-base">📧</span>
              <input
                v-model="form.email"
                type="email"
                readonly
                class="w-full bg-transparent outline-none text-sm"
              >
            </div>
            <p
              v-if="form.errors.email"
              class="text-xs text-rose-600 mt-1"
            >
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Nueva contraseña -->
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-neutral-600 uppercase tracking-wide">
              Nueva contraseña
            </label>
            <div
              class="flex items-center gap-2 rounded-xl border px-3 py-2.5 bg-white
                     focus-within:ring-2 focus-within:ring-amber-400/70 focus-within:border-amber-400"
            >
              <span class="text-neutral-400 text-base">🔒</span>
              <input
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                placeholder="Escribe tu nueva contraseña"
                class="w-full bg-transparent outline-none text-sm"
                required
              >
            </div>
            <p
              v-if="form.errors.password"
              class="text-xs text-rose-600 mt-1"
            >
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Confirmación -->
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-neutral-600 uppercase tracking-wide">
              Confirmar contraseña
            </label>
            <div
              class="flex items-center gap-2 rounded-xl border px-3 py-2.5 bg-white
                     focus-within:ring-2 focus-within:ring-amber-400/70 focus-within:border-amber-400"
            >
              <span class="text-neutral-400 text-base">✅</span>
              <input
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                placeholder="Vuelve a escribir la contraseña"
                class="w-full bg-transparent outline-none text-sm"
                required
              >
            </div>
          </div>

          <!-- Botones -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
            <Link
              :href="route('login')"
              class="inline-flex items-center gap-1 text-xs sm:text-sm font-medium text-neutral-500 hover:text-neutral-700"
            >
              ← Volver al inicio de sesión
            </Link>

            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full px-6 py-2.5
                     text-sm font-semibold text-white
                     bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600
                     shadow-lg shadow-amber-300/40
                     hover:from-amber-600 hover:via-orange-500 hover:to-amber-700
                     active:scale-[0.98]
                     disabled:opacity-60 disabled:cursor-not-allowed
                     transition"
            >
              <span v-if="!form.processing">Guardar nueva contraseña</span>
              <span v-else>Guardando...</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Columna derecha: info / tips -->
      <div
        class="hidden md:flex flex-col justify-between bg-gradient-to-br
               from-violet-600 via-indigo-600 to-sky-500 text-white px-8 py-10 relative"
      >
        <!-- brillo decorativo -->
        <div class="pointer-events-none absolute inset-0 opacity-40">
          <div class="absolute -top-10 -left-10 w-32 h-32 bg-amber-300 rounded-full blur-3xl" />
          <div class="absolute bottom-0 right-0 w-40 h-40 bg-white/30 rounded-full blur-3xl" />
        </div>

        <div class="relative space-y-4">
          <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">
            <span>🛡️</span>
            <span>Seguridad para tu cuenta</span>
          </div>

          <h2 class="text-xl font-semibold leading-relaxed">
            Mantén tu cuenta de Pollería Pepe siempre protegida.
          </h2>

          <p class="text-sm text-indigo-100/90">
            Usa una contraseña única para este sistema y evita reutilizarla en otras aplicaciones.
            Si no fuiste tú quien solicitó el cambio, puedes ignorar el correo.
          </p>
        </div>

        <div class="relative mt-6">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-100/80 mb-2">
            Consejos rápidos
          </p>
          <ul class="space-y-1.5 text-[13px] text-indigo-100/90">
            <li>• Combina mayúsculas, minúsculas, números y símbolos.</li>
            <li>• Evita usar fechas de nacimiento o datos fáciles de adivinar.</li>
            <li>• No compartas tu contraseña con nadie.</li>
            <li>• Cambia tu contraseña de forma periódica.</li>
          </ul>

          <p class="mt-6 text-[11px] text-indigo-100/70">
            © 2025 Pollería Pepe — Sistema de gestión de pedidos.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
