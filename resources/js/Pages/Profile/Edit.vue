<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  user: { type: Object, required: true }
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: ''
})

function submit() {
  form.put(route('profile.update'))
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Perfil</h2>
    </template>

    <div class="max-w-2xl mx-auto p-6 space-y-6">
      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Nombre</label>
            <input v-model="form.name" type="text" class="w-full rounded-lg border px-3 py-2" />
            <p v-if="form.errors.name" class="text-sm text-rose-600 mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input v-model="form.email" type="email" class="w-full rounded-lg border px-3 py-2" />
            <p v-if="form.errors.email" class="text-sm text-rose-600 mt-1">{{ form.errors.email }}</p>
          </div>

          <hr class="my-2" />

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Nueva contraseña (opcional)</label>
              <input v-model="form.password" type="password" class="w-full rounded-lg border px-3 py-2" />
              <p v-if="form.errors.password" class="text-sm text-rose-600 mt-1">{{ form.errors.password }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Confirmar contraseña</label>
              <input v-model="form.password_confirmation" type="password" class="w-full rounded-lg border px-3 py-2" />
            </div>
          </div>

          <div class="pt-2">
            <button
              :disabled="form.processing"
              @click="submit"
              class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
            >
              Guardar cambios
            </button>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-semibold">Cerrar sesión</h3>
            <p class="text-sm text-gray-500">Termina la sesión actual.</p>
          </div>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="rounded-lg bg-gray-700 px-4 py-2 font-semibold text-white hover:bg-gray-800"
          >
            Cerrar sesión
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
