<!-- resources/js/Pages/Admin/Configuracion/Edit.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'

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
  form.put(route('admin.config.update'), { preserveScroll: true })
}

// Vista previa legible del horario
const horarioLabel = computed(() => {
  const a = (form.horario_apertura || '').slice(0,5)
  const c = (form.horario_cierre   || '').slice(0,5)
  return `${a}–${c} hrs`
})
</script>

<template>
  <Head title="Configuración" />

  <AuthenticatedLayout>
    <!-- Header con gradiente y CTA -->
    <template #header>
      <div
        class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-indigo-50 to-white px-4 py-4 ring-1 ring-indigo-100/60"
      >
        <div class="flex items-start gap-3">
          <div class="rounded-xl bg-indigo-100 p-2 text-indigo-700">
            <!-- ícono ajustes -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 8a4 4 0 1 0 .001 8.001A4 4 0 0 0 12 8zm8.94 3.06-1.26-.73a7.83 7.83 0 0 0-.44-1.07l.62-1.34a.75.75 0 0 0-.15-.84l-1.5-1.5a.75.75 0 0 0-.84-.15l-1.34.62c-.35-.16-.71-.31-1.07-.44l-.73-1.26A.75.75 0 0 0 12 2h-2a.75.75 0 0 0-.67.41l-.73 1.26c-.36.13-.72.28-1.07.44l-1.34-.62a.75.75 0 0 0-.84.15l-1.5 1.5a.75.75 0 0 0-.15.84l.62 1.34c-.16.35-.31.71-.44 1.07l-1.26.73A.75.75 0 0 0 2 12v2c0 .27.14.52.38.66l1.26.73c.13.36.28.72.44 1.07l-.62 1.34a.75.75 0 0 0 .15.84l1.5 1.5c.22.22.55.28.84.15l1.34-.62c.35.16.71.31 1.07.44l.73 1.26c.14.24.39.38.66.38h2c.27 0 .52-.14.66-.38l.73-1.26c.36-.13.72-.28 1.07-.44l1.34.62c.29.13.62.07.84-.15l1.5-1.5c.22-.22.28-.55.15-.84l-.62-1.34c.16-.35.31-.71.44-1.07l1.26-.73c.24-.14.38-.39.38-.66v-2a.75.75 0 0 0-.41-.67z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">Configuración</h2>
            <p class="text-sm text-gray-500">Parámetros básicos del sistema</p>
          </div>
        </div>

        <Link :href="route('admin.dashboard')" class="text-sm text-indigo-600 hover:underline">
          ← Volver al panel
        </Link>
      </div>
    </template>

    <div class="mx-auto max-w-4xl p-6 space-y-6">
      <!-- Flash -->
      <div
        v-if="$page.props.flash?.success"
        class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-emerald-800"
      >
        {{ $page.props.flash.success }}
      </div>

      <!-- Resumen / Hints -->
      <div class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-4">
        <div class="flex flex-wrap items-center gap-3">
          <span class="text-sm text-gray-700">Horario actual:</span>
          <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-indigo-200">
            {{ horarioLabel }}
          </span>
          <span class="ml-2 text-sm text-gray-600">Zona: <strong class="text-gray-800">{{ form.zona_cobertura }}</strong></span>
          <span class="ml-2 text-sm text-gray-600">Umbral stock bajo:
            <strong class="text-gray-800">{{ form.stock_umbral }}</strong>
          </span>
        </div>
      </div>

      <!-- Formulario -->
      <form
        @submit.prevent="submit"
        class="rounded-2xl border border-gray-200 bg-white shadow-sm"
      >
        <!-- Sección: Horarios -->
        <div class="border-b border-gray-100 px-6 py-5">
          <h3 class="text-sm font-semibold tracking-wide text-gray-900">Horario de atención</h3>
          <p class="mt-1 text-xs text-gray-500">Define el rango en el que aceptas pedidos y entregas.</p>
        </div>

        <div class="px-6 py-6 grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm text-gray-600">Apertura</label>
            <input
              type="time"
              v-model="form.horario_apertura"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
            />
            <p v-if="form.errors.horario_apertura" class="mt-1 text-sm text-rose-600">
              {{ form.errors.horario_apertura }}
            </p>
          </div>

          <div>
            <label class="mb-1 block text-sm text-gray-600">Cierre</label>
            <input
              type="time"
              v-model="form.horario_cierre"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
            />
            <p v-if="form.errors.horario_cierre" class="mt-1 text-sm text-rose-600">
              {{ form.errors.horario_cierre }}
            </p>
          </div>
        </div>

        <!-- Sección: Ubicación -->
        <div class="border-t border-gray-100 px-6 py-5">
          <h3 class="text-sm font-semibold tracking-wide text-gray-900">Cobertura y stock</h3>
          <p class="mt-1 text-xs text-gray-500">Ajusta la ciudad de servicio y el umbral visual de stock bajo.</p>
        </div>

        <div class="px-6 pb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm text-gray-600">Zona de cobertura</label>
            <input
              type="text"
              v-model="form.zona_cobertura"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
              placeholder="Tapachula"
            />
            <p v-if="form.errors.zona_cobertura" class="mt-1 text-sm text-rose-600">
              {{ form.errors.zona_cobertura }}
            </p>
          </div>

          <div>
            <label class="mb-1 block text-sm text-gray-600">Umbral de stock bajo</label>
            <input
              type="number" min="0"
              v-model.number="form.stock_umbral"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 outline-none ring-indigo-200 focus:border-indigo-300 focus:ring-2"
            />
            <p v-if="form.errors.stock_umbral" class="mt-1 text-sm text-rose-600">
              {{ form.errors.stock_umbral }}
            </p>
          </div>
        </div>

        <!-- Acciones fijas -->
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
          <Link
            :href="route('admin.dashboard')"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50"
          >
            Cancelar
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 font-medium text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60"
          >
            <span v-if="form.processing">Guardando…</span>
            <span v-else>Guardar cambios</span>
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
