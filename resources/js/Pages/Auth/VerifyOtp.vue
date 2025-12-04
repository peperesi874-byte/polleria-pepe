<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  email: {
    type: String,
    default: '',
  },
})

const form = useForm({
  email: props.email || '',
  code: '',
})
</script>

<template>
  <GuestLayout>
    <Head title="Verificar código" />

    <div class="mb-4 text-sm text-gray-600">
      Te enviamos un código de verificación a tu correo. Escríbelo a
      continuación para confirmar tu identidad.
    </div>

    <div v-if="$page.props.flash?.status" class="mb-4 text-sm text-green-600">
      {{ $page.props.flash.status }}
    </div>

    <form
      @submit.prevent="form.post(route('password.otp.verify'))"
      class="space-y-6"
    >
      <!-- Email -->
      <div>
        <InputLabel for="email" value="Correo electrónico" />

        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
          required
          autocomplete="email"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <!-- Código -->
      <div>
        <InputLabel for="code" value="Código de verificación" />

        <TextInput
          id="code"
          v-model="form.code"
          type="text"
          maxlength="6"
          class="mt-1 block w-full tracking-widest text-center"
          required
        />

        <InputError class="mt-2" :message="form.errors.code" />
      </div>

      <div class="flex items-center justify-between">
        <Link
          :href="route('password.otp.request.form')"
          class="text-sm text-gray-600 underline hover:text-gray-900"
        >
          Volver a solicitar código
        </Link>

        <PrimaryButton
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Verificar código
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
