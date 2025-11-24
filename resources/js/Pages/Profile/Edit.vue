<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  user: { type: Object, required: true },
})

const page = usePage()

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
})

function submit() {
  form.put(route('profile.update'), {
    preserveScroll: true,
  })
}

const initials = (str) => {
  const s = (str || '').trim()
  if (!s) return 'U'
  const parts = s.split(/\s+/).slice(0, 2)
  return parts.map((p) => p[0]?.toUpperCase() ?? '').join('') || 'U'
}
</script>

<template>
  <AuthenticatedLayout>
    <!-- HEADER estilo panel -->
    <template #header>
      <div
        class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-50 via-orange-50 to-rose-50 px-6 py-5 ring-1 ring-amber-100 shadow-sm"
      >
        <!-- decoraciones -->
        <div
          class="pointer-events-none absolute -right-16 -top-10 h-32 w-32 rounded-full bg-amber-200/40 blur-2xl"
        />
        <div
          class="pointer-events-none absolute -left-10 bottom-0 h-28 w-28 rounded-full bg-rose-200/30 blur-2xl"
        />

        <div
          class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
          <div class="flex items-start gap-3">
            <div
              class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-200 text-lg font-bold"
            >
              {{ initials(props.user.name || props.user.email) }}
            </div>
            <div>
              <p
                class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700/80"
              >
                Perfil de usuario
              </p>
              <h1
                class="mt-1 flex items-center gap-2 text-2xl md:text-3xl font-bold text-neutral-900"
              >
                Hola, {{ props.user.name ?? 'Usuario' }} 👋
              </h1>
              <p class="mt-1 text-sm text-neutral-600 max-w-xl">
                Administra tus datos personales y actualiza tu contraseña cuando lo
                necesites.
              </p>
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <div
              class="rounded-xl bg-white/80 px-3 py-2 text-right shadow-sm border border-amber-50"
            >
              <p class="text-[11px] uppercase tracking-[0.16em] text-neutral-400">
                Sesión actual
              </p>
              <p class="text-sm font-medium text-neutral-800">
                {{ props.user.email }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div
      class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-0 pb-10 pt-6 space-y-6 bg-gradient-to-b from-slate-50 via-white to-amber-50/40 rounded-3xl"
    >
      <!-- Mensaje de éxito -->
      <div
        v-if="form.recentlySuccessful"
        class="rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-700 flex items-center gap-2"
      >
        ✅
        <span>Los cambios en tu perfil se guardaron correctamente.</span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- COLUMNA IZQUIERDA: Info principal -->
        <section class="lg:col-span-2 space-y-6">
          <!-- Datos personales -->
          <div class="rounded-2xl border border-neutral-100 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h2 class="text-base font-semibold text-neutral-900">
                  Información personal
                </h2>
                <p class="text-sm text-neutral-500">
                  Actualiza tu nombre y correo electrónico.
                </p>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Nombre -->
              <div>
                <label class="block text-sm font-medium text-neutral-800 mb-1">
                  Nombre completo
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50/70 px-3 py-2.5 text-sm text-neutral-900 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-200 outline-none transition"
                  placeholder="Escribe tu nombre"
                />
                <p
                  v-if="form.errors.name"
                  class="text-[13px] text-rose-600 mt-1"
                >
                  {{ form.errors.name }}
                </p>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-neutral-800 mb-1">
                  Correo electrónico
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50/70 px-3 py-2.5 text-sm text-neutral-900 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-200 outline-none transition"
                  placeholder="tucorreo@ejemplo.com"
                />
                <p
                  v-if="form.errors.email"
                  class="text-[13px] text-rose-600 mt-1"
                >
                  {{ form.errors.email }}
                </p>
              </div>
            </div>
          </div>

          <!-- Contraseña -->
          <div class="rounded-2xl border border-neutral-100 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div>
                <h2 class="text-base font-semibold text-neutral-900">
                  Seguridad y contraseña
                </h2>
                <p class="text-sm text-neutral-500">
                  Si no deseas cambiar tu contraseña, deja estos campos vacíos.
                </p>
              </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-neutral-800 mb-1">
                  Nueva contraseña
                  <span class="text-xs text-neutral-400">(opcional)</span>
                </label>
                <input
                  v-model="form.password"
                  type="password"
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50/70 px-3 py-2.5 text-sm text-neutral-900 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-200 outline-none transition"
                  placeholder="••••••••"
                />
                <p
                  v-if="form.errors.password"
                  class="text-[13px] text-rose-600 mt-1"
                >
                  {{ form.errors.password }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-neutral-800 mb-1">
                  Confirmar contraseña
                </label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50/70 px-3 py-2.5 text-sm text-neutral-900 shadow-sm focus:border-amber-400 focus:ring-2 focus:ring-amber-200 outline-none transition"
                  placeholder="Repite la nueva contraseña"
                />
              </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
              <button
                type="button"
                class="text-sm text-neutral-500 hover:text-neutral-700 transition"
                @click="form.reset('password', 'password_confirmation')"
              >
                Limpiar contraseña
              </button>

              <button
                type="button"
                :disabled="form.processing"
                @click="submit"
                class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-300 disabled:opacity-60 disabled:cursor-not-allowed transition"
              >
                <span v-if="form.processing">Guardando...</span>
                <span v-else>💾 Guardar cambios</span>
              </button>
            </div>
          </div>
        </section>

        <!-- COLUMNA DERECHA: Sesión -->
        <section class="space-y-6">
          <!-- Resumen -->
          <div class="rounded-2xl border border-neutral-100 bg-white/95 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-neutral-900">
              Resumen de la cuenta
            </h3>
            <p class="mt-1 text-sm text-neutral-500">
              Estos son los datos principales de tu usuario en el sistema.
            </p>

            <dl class="mt-4 space-y-2 text-sm">
              <div class="flex items-center justify-between gap-3">
                <dt class="text-neutral-500">Nombre</dt>
                <dd class="font-medium text-neutral-900 text-right">
                  {{ props.user.name }}
                </dd>
              </div>
              <div class="flex items-center justify-between gap-3">
                <dt class="text-neutral-500">Correo</dt>
                <dd class="font-medium text-neutral-900 text-right truncate max-w-[180px]">
                  {{ props.user.email }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Cerrar sesión -->
          <div
            class="rounded-2xl border border-rose-100 bg-gradient-to-r from-rose-50 via-white to-amber-50 p-5 shadow-sm"
          >
            <div class="flex items-start gap-3">
              <div
                class="mt-0.5 h-9 w-9 flex items-center justify-center rounded-xl bg-rose-500 text-white text-lg shadow-sm"
              >
                ⎋
              </div>
              <div class="flex-1">
                <h3 class="text-sm font-semibold text-neutral-900">
                  Cerrar sesión
                </h3>
                <p class="mt-1 text-xs text-neutral-500">
                  Termina tu sesión actual de forma segura. Podrás volver a entrar
                  con tu correo y contraseña.
                </p>
                <div class="mt-3">
                  <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="inline-flex items-center gap-1 rounded-full bg-neutral-900 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-neutral-800 transition"
                  >
                    Cerrar sesión
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
