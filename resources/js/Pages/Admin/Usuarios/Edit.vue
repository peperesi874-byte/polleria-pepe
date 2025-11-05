<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  user:  { type: Object, required: true }, // { id, name, email, role_id }
  roles: { type: Array,  default: () => [] }, // [{id,nombre}]
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role_id: props.user.role_id ?? 4, // cliente por si acaso
  password: '',
  password_confirmation: '',
})

function submit() {
  form.put(route('admin.usuarios.update', props.user.id))
}
</script>

<template>
  <Head title="Editar usuario" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Editar usuario</h2>
    </template>

    <div class="max-w-3xl mx-auto p-6">
      <div class="rounded-2xl border bg-white p-6 shadow-sm space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre</label>
          <input v-model="form.name" type="text" class="mt-1 w-full rounded-md border-gray-300" />
          <div v-if="form.errors.name" class="text-sm text-rose-600 mt-1">{{ form.errors.name }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" class="mt-1 w-full rounded-md border-gray-300" />
          <div v-if="form.errors.email" class="text-sm text-rose-600 mt-1">{{ form.errors.email }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Rol</label>
          <select v-model.number="form.role_id" class="mt-1 w-full rounded-md border-gray-300">
            <option v-for="r in roles" :key="r.id" :value="r.id">
              {{ r.nombre }}
            </option>
          </select>
          <div v-if="form.errors.role_id" class="text-sm text-rose-600 mt-1">{{ form.errors.role_id }}</div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nueva contraseña (opcional)</label>
            <input v-model="form.password" type="password" class="mt-1 w-full rounded-md border-gray-300" />
            <div v-if="form.errors.password" class="text-sm text-rose-600 mt-1">{{ form.errors.password }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Confirmación</label>
            <input v-model="form.password_confirmation" type="password" class="mt-1 w-full rounded-md border-gray-300" />
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button
            :disabled="form.processing"
            @click="submit"
            class="rounded-lg bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700 disabled:opacity-50"
          >
            Guardar cambios
          </button>
          <Link :href="route('admin.usuarios.index')" class="text-gray-600 hover:underline">Cancelar</Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
