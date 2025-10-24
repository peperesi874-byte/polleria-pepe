<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  user:  { type: Object, required: true },
  role: { type: Array,  default: () => [] },
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role_id: props.user.role_id,
  password: '',
  password_confirmation: '',
})

function submit() {
  form.put(route('admin.usuarios.update', props.user.id))
}
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Editar usuario</h1>
      <Link :href="route('admin.usuarios.index')" class="text-amber-600 hover:underline">Volver</Link>
    </div>

    <div class="rounded-xl border bg-white p-6">
      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Nombre</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border px-3 py-2" />
          <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-lg border px-3 py-2" />
          <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Rol</label>
          <select v-model.number="form.role_id" class="w-full rounded-lg border px-3 py-2">
            <option v-for="r in props.roles" :key="r.id" :value="r.id">
              {{ r.nombre }}
            </option>
          </select>
          <p v-if="form.errors.role_id" class="text-sm text-red-600 mt-1">{{ form.errors.role_id }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Nueva contraseña (opcional)</label>
            <input v-model="form.password" type="password" class="w-full rounded-lg border px-3 py-2" />
            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Confirmar contraseña</label>
            <input v-model="form.password_confirmation" type="password" class="w-full rounded-lg border px-3 py-2" />
          </div>
        </div>

        <div class="mt-4">
          <button
            :disabled="form.processing"
            @click="submit"
            class="rounded-lg bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700 disabled:opacity-50"
          >
            Guardar cambios
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
