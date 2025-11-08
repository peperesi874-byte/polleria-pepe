<!-- resources/js/Pages/Admin/Configuracion/Edit.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js' // ← IMPORTANTE para usar route('...')

const page = usePage()

const props = defineProps({
  cfg: {
    type: Object,
    default: () => ({
      horario_apertura: '09:00',
      horario_cierre:   '18:00',
      zona_cobertura:   'Tapachula',
      stock_umbral:     5,
    }),
  },
})

const form = useForm({
  horario_apertura: props.cfg.horario_apertura ?? '09:00',
  horario_cierre:   props.cfg.horario_cierre   ?? '18:00',
  zona_cobertura:   props.cfg.zona_cobertura   ?? 'Tapachula',
  stock_umbral:     props.cfg.stock_umbral     ?? 5,
})

const submit = () => {
  form.put(route('admin.config.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Configuración" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Configuración</h2>
          <p class="text-sm text-gray-500">Parámetros básicos del sistema</p>
        </div>
        <Link :href="route('admin.dashboard')" class="text-indigo-600 hover:underline text-sm">
          ← Volver al panel
        </Link>
      </div>
    </template>

    <div class="mx-auto max-w-3xl p-6 space-y-6">
      <!-- Mensaje de éxito -->
      <div v-if="$page.props.flash?.success" class="rounded-lg bg-emerald-50 text-emerald-800 px-4 py-2">
        {{ $page.props.flash.success }}
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border bg-white p-6 shadow-sm space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700">Horario de apertura</label>
          <input
            type="time"
            v-model="form.horario_apertura"
            class="mt-1 w-full rounded-lg border px-3 py-2"
          />
          <p v-if="form.errors.horario_apertura" class="text-sm text-rose-600 mt-1">
            {{ form.errors.horario_apertura }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Horario de cierre</label>
          <input
            type="time"
            v-model="form.horario_cierre"
            class="mt-1 w-full rounded-lg border px-3 py-2"
          />
          <p v-if="form.errors.horario_cierre" class="text-sm text-rose-600 mt-1">
            {{ form.errors.horario_cierre }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Zona de cobertura (ciudad)</label>
          <input
            type="text"
            v-model="form.zona_cobertura"
            class="mt-1 w-full rounded-lg border px-3 py-2"
            placeholder="Tapachula"
          />
          <p v-if="form.errors.zona_cobertura" class="text-sm text-rose-600 mt-1">
            {{ form.errors.zona_cobertura }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Umbral de stock bajo</label>
          <input
            type="number"
            min="0"
            v-model.number="form.stock_umbral"
            class="mt-1 w-full rounded-lg border px-3 py-2"
          />
          <p v-if="form.errors.stock_umbral" class="text-sm text-rose-600 mt-1">
            {{ form.errors.stock_umbral }}
          </p>
        </div>

        <div class="pt-2">
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-white hover:bg-indigo-700 disabled:opacity-60"
          >
            Guardar
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
