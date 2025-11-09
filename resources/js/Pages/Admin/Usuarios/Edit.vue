<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  user:  { type: Object, required: true }, // { id, name, email, role_id }
  roles: { type: Array,  default: () => [] }, // [{id,nombre}]
})

const form = useForm({
  name: props.user.name ?? '',
  email: props.user.email ?? '',
  role_id: props.user.role_id ?? (props.roles?.[0]?.id ?? null),
  password: '',
  password_confirmation: '',
})

function submit() {
  form.put(route('admin.usuarios.update', props.user.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Editar usuario" />
  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto px-6 py-8">

      <!-- Cabecera -->
      <div
        class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-center gap-3">
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
              Editar usuario
            </h1>
            <p class="text-xs text-gray-500">
              Actualiza nombre, correo, rol y (opcional) contraseña.
            </p>
          </div>
        </div>

        <Link
          :href="route('admin.usuarios.index')"
          class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-indigo-700 shadow-sm transition hover:bg-indigo-50"
        >
          ← Volver al listado
        </Link>
      </div>

      <!-- Card del formulario -->
      <form
        @submit.prevent="submit"
        class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
      >
        <!-- Nombre -->
        <div>
          <label class="mb-1 block text-sm text-gray-600" for="name">Nombre</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</p>
        </div>

        <!-- Email -->
        <div>
          <label class="mb-1 block text-sm text-gray-600" for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          />
          <p v-if="form.errors.email" class="mt-1 text-sm text-rose-600">{{ form.errors.email }}</p>
        </div>

        <!-- Rol -->
        <div>
          <label class="mb-1 block text-sm text-gray-600" for="role">Rol</label>
          <select
            id="role"
            v-model.number="form.role_id"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
          >
            <option v-for="r in roles" :key="r.id" :value="r.id">
              {{ r.nombre }}
            </option>
          </select>
          <p v-if="form.errors.role_id" class="mt-1 text-sm text-rose-600">{{ form.errors.role_id }}</p>
        </div>

        <!-- Contraseña (opcional) -->
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm text-gray-600" for="password">Nueva contraseña (opcional)</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-rose-600">{{ form.errors.password }}</p>
          </div>
          <div>
            <label class="mb-1 block text-sm text-gray-600" for="password_confirmation">Confirmación</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
            />
          </div>
        </div>

        <!-- Acciones -->
        <div class="flex items-center justify-end gap-2 pt-2">
          <Link
            :href="route('admin.usuarios.index')"
            class="rounded-md border px-4 py-2 text-gray-700 hover:bg-gray-50"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-md bg-indigo-600 px-5 py-2 font-medium text-white transition hover:bg-indigo-700 disabled:opacity-60"
          >
            {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
