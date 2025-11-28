<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  roles: { type: Array, default: () => [] },
})

const form = useForm({
  name: '',
  email: '',
  role_id: props.roles?.[0]?.id ?? 4,
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(route('admin.usuarios.store'), { preserveScroll: true })
}
</script>

<template>
  <Head title="Crear usuario" />

  <AuthenticatedLayout>
    <!-- ===== HERO / HEADER estilo dashboard ===== -->
    <template #header>
      <div class="mx-auto max-w-5xl px-4 lg:px-6">
        <div
          class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 px-6 py-5 shadow-lg"
        >
          <!-- decor -->
          <div class="pointer-events-none absolute -left-16 -top-16 h-32 w-32 rounded-full bg-white/15 blur-3xl" />
          <div class="pointer-events-none absolute -right-10 bottom-0 h-32 w-32 rounded-full bg-black/15 blur-3xl" />

          <div class="relative flex items-start justify-between gap-4">
            <div class="space-y-2">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
              >
                <span class="text-lg">👥</span>
                <span>Nuevo usuario — Pollería Pepe</span>
              </div>

              <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white">
                  Crear usuario
                </h1>
                <p class="mt-1 text-sm text-amber-50/90">
                  Registra cuentas para el personal y asigna el rol adecuado desde un solo lugar.
                </p>
              </div>

              <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-amber-50/90">
                <span
                  class="inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 font-semibold text-amber-900 shadow-sm"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                  Alta rápida de usuarios
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-black/20 px-2.5 py-1">
                  Roles como administrador, vendedor o repartidor.
                </span>
              </div>
            </div>

            <div class="hidden sm:flex flex-col items-end gap-2">
              <Link
                :href="route('admin.usuarios.index')"
                class="inline-flex items-center gap-2 rounded-2xl bg-black/25 px-3 py-2 text-xs font-medium text-amber-50 shadow-sm hover:bg-black/35"
              >
                ← Volver al listado
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/20 to-slate-50">
      <div class="mx-auto max-w-5xl px-4 py-8 lg:px-6">
        <form
          @submit.prevent="submit"
          class="space-y-6 overflow-hidden rounded-3xl bg-white/95 p-6 shadow-lg ring-1 ring-amber-100"
        >
          <!-- Encabezado interno -->
          <div
            class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-amber-50/70 px-4 py-3 ring-1 ring-amber-100/70"
          >
            <div>
              <p class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-amber-700">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-[13px] shadow-sm">
                  🧾
                </span>
                <span>Datos del usuario</span>
              </p>
              <p class="mt-1 text-xs text-amber-900/80">
                Completa la información básica y la contraseña inicial.
              </p>
            </div>

            <div class="flex gap-2">
              <Link
                :href="route('admin.usuarios.index')"
                class="rounded-xl border border-amber-200 bg-white px-4 py-2 text-xs font-medium text-amber-800 hover:bg-amber-50"
              >
                Cancelar
              </Link>

              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-xl bg-amber-600 px-5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60"
              >
                {{ form.processing ? 'Creando…' : 'Crear usuario' }}
              </button>
            </div>
          </div>

          <!-- Campos -->
          <div class="space-y-5">
            <!-- Nombre -->
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                Nombre
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full rounded-2xl border border-amber-200 bg-amber-50/40 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
              <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Email -->
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                Email
              </label>
              <input
                v-model="form.email"
                type="email"
                class="w-full rounded-2xl border border-amber-200 bg-amber-50/40 px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
              <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">
                {{ form.errors.email }}
              </p>
            </div>

            <!-- Rol -->
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                Rol
              </label>
              <select
                v-model.number="form.role_id"
                class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              >
                <option
                  v-for="r in props.roles"
                  :key="r.id"
                  :value="r.id"
                >
                  {{ r.nombre }}
                </option>
              </select>
              <p v-if="form.errors.role_id" class="mt-1 text-xs text-rose-600">
                {{ form.errors.role_id }}
              </p>
            </div>

            <!-- Contraseña + Confirmación -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                  Contraseña
                </label>
                <input
                  v-model="form.password"
                  type="password"
                  class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">
                  {{ form.errors.password }}
                </p>
              </div>

              <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                  Confirmar contraseña
                </label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                />
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
