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
  const a = (form.horario_apertura || '').slice(0, 5)
  const c = (form.horario_cierre   || '').slice(0, 5)
  return `${a}–${c} hrs`
})
</script>

<template>
  <Head title="Configuración" />

  <AuthenticatedLayout>
    <!-- ===== HEADER TIPO DASHBOARD POLLERÍA PEPE ===== -->
<template #header>
  <div class="mx-auto max-w-7xl px-4 lg:px-6">
    <div
      class="relative overflow-hidden rounded-[32px] bg-gradient-to-r from-orange-400 via-orange-400 to-rose-500 px-8 py-7 text-white shadow-[0_24px_60px_rgba(249,115,22,0.45)]"
    >
      <!-- decor borroso -->
      <div class="pointer-events-none absolute -left-16 -top-16 h-32 w-32 rounded-full bg-white/20 blur-3xl" />
      <div class="pointer-events-none absolute -right-10 bottom-0 h-32 w-32 rounded-full bg-black/25 blur-3xl" />

      <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <!-- LADO IZQUIERDO -->
        <div class="flex gap-3">
          <div
            class="grid h-11 w-11 place-items-center rounded-2xl bg-white/20 text-amber-50 shadow-md shadow-black/20"
          >
            <!-- ícono ajustes -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M12 8a4 4 0 1 0 .001 8.001A4 4 0 0 0 12 8zm8.94 3.06-1.26-.73a7.83 7.83 0 0 0-.44-1.07l.62-1.34a.75.75 0 0 0-.15-.84l-1.5-1.5a.75.75 0 0 0-.84-.15l-1.34.62c-.35-.16-.71-.31-1.07-.44l-.73-1.26A.75.75 0 0 0 12 2h-2a.75.75 0 0 0-.67.41l-.73 1.26c-.36.13-.72.28-1.07.44l-1.34-.62a.75.75 0 0 0-.84.15l-1.5 1.5a.75.75 0 0 0-.15.84l.62 1.34c-.16.35-.31.71-.44 1.07l-1.26.73A.75.75 0 0 0 2 12v2c0 .27.14.52.38.66l1.26.73c.13.36.28.72.44 1.07l-.62 1.34a.75.75 0 0 0 .15.84l1.5 1.5c.22.22.55.28.84.15l1.34-.62c.35.16.71.31 1.07.44l.73 1.26c.14.24.39.38.66.38h2c.27 0 .52-.14.66-.38l.73-1.26c.36-.13.72-.28 1.07-.44l1.34.62c.29.13.62.07.84-.15l1.5-1.5c.22-.22.28-.55.15-.84l-.62-1.34c.16-.35.31-.71.44-1.07l1.26-.73c.24-.14.38-.39.38-.66v-2a.75.75 0 0 0-.41-.67z"
              />
            </svg>
          </div>

          <div class="space-y-2">
            <div
              class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
            >
              <span class="text-xs uppercase tracking-wide">Centro de control</span>
              <span class="h-1 w-1 rounded-full bg-emerald-400" />
              <span>Configuración general del sistema</span>
            </div>

            <p class="text-sm opacity-90">
              Ajusta horario, zona de servicio y el umbral de stock bajo que verás en el panel.
            </p>

            <h2 class="text-2xl sm:text-3xl font-extrabold leading-tight">
              Configuración del sistema
            </h2>
          </div>
        </div>

        <!-- LADO DERECHO -->
        <div
          class="mt-2 w-full max-w-xs rounded-3xl bg-white/95 px-5 py-4 text-xs text-slate-800 shadow-xl md:mt-0"
        >
          <p class="text-[11px] font-semibold text-amber-500 uppercase tracking-wide">
            Resumen rápido
          </p>
          <p class="mt-1 text-sm font-semibold text-slate-900">
            Parametriza cómo opera la pollería.
          </p>
          <p class="mt-1 text-[11px] text-slate-500">
            Los cambios en horarios, zona y umbral de stock se reflejan en tiempo real en el panel.
          </p>

          <div class="mt-4 space-y-2">
            <Link
              :href="route('admin.dashboard')"
              class="flex items-center justify-between rounded-2xl bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-700 hover:bg-slate-200"
            >
              <span>Volver al panel</span>
              <span>→</span>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


    <!-- ===== CONTENIDO ===== -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/20 to-slate-50">
      <div class="mx-auto max-w-4xl px-4 py-6 space-y-6 lg:px-6">
        <!-- Flash -->
        <div
          v-if="page.props.flash?.success"
          class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-2.5 text-xs text-emerald-900 shadow-sm"
        >
          <span>✅</span>
          <span>{{ page.props.flash.success }}</span>
        </div>

        <!-- Resumen / Hints estilo tarjeta -->
        <div class="rounded-3xl border border-amber-100 bg-amber-50/60 px-4 py-3 shadow-sm">
          <div class="flex flex-wrap items-center gap-3 text-xs text-amber-900">
            <span class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 font-semibold text-amber-900 ring-1 ring-amber-200">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
              Horario actual:
              <span class="ml-1">{{ horarioLabel }}</span>
            </span>

            <span class="inline-flex items-center gap-1 rounded-full bg-white/70 px-2.5 py-1 font-medium ring-1 ring-amber-200/70">
              🗺 Zona:
              <strong class="ml-1 text-amber-900">{{ form.zona_cobertura }}</strong>
            </span>

            <span class="inline-flex items-center gap-1 rounded-full bg-white/70 px-2.5 py-1 font-medium ring-1 ring-amber-200/70">
              📦 Umbral de stock bajo:
              <strong class="ml-1 text-amber-900">{{ form.stock_umbral }}</strong>
              <span class="text-[10px] text-amber-600">(alertas a partir de este valor)</span>
            </span>
          </div>
        </div>

        <!-- Formulario principal -->
        <form
          @submit.prevent="submit"
          class="overflow-hidden rounded-[28px] border border-amber-100 bg-white/95 shadow-[0_18px_45px_rgba(15,23,42,0.10)]"
        >
          <!-- Sección: Horarios -->
          <div class="border-b border-amber-100 bg-amber-50/60 px-6 py-5">
            <h3 class="text-sm font-semibold tracking-wide text-amber-950">
              Horario de atención
            </h3>
            <p class="mt-1 text-xs text-amber-900/80">
              Define el rango en el que aceptas pedidos y entregas para mostrarlo en el panel.
            </p>
          </div>

          <div class="grid grid-cols-1 gap-4 px-6 py-6 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                Apertura
              </label>
              <input
                type="time"
                v-model="form.horario_apertura"
                class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
              <p v-if="form.errors.horario_apertura" class="mt-1 text-xs text-rose-600">
                {{ form.errors.horario_apertura }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                Cierre
              </label>
              <input
                type="time"
                v-model="form.horario_cierre"
                class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
              <p v-if="form.errors.horario_cierre" class="mt-1 text-xs text-rose-600">
                {{ form.errors.horario_cierre }}
              </p>
            </div>
          </div>

          <!-- Sección: Cobertura y stock -->
          <div class="border-t border-amber-100 bg-amber-50/40 px-6 py-5">
            <h3 class="text-sm font-semibold tracking-wide text-amber-950">
              Cobertura y umbral de stock
            </h3>
            <p class="mt-1 text-xs text-amber-900/80">
              Ajusta la ciudad de servicio y el valor a partir del cual se considera que un producto está
              en <strong>stock bajo</strong>.
            </p>
          </div>

          <div class="grid grid-cols-1 gap-4 px-6 pb-6 pt-4 md:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                Zona de cobertura
              </label>
              <input
                type="text"
                v-model="form.zona_cobertura"
                class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
                placeholder="Tapachula"
              />
              <p v-if="form.errors.zona_cobertura" class="mt-1 text-xs text-rose-600">
                {{ form.errors.zona_cobertura }}
              </p>
            </div>

            <div>
              <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-amber-700">
                Umbral de stock bajo
              </label>
              <input
                type="number"
                min="0"
                v-model.number="form.stock_umbral"
                class="w-full rounded-2xl border border-amber-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-300/80"
              />
              <p v-if="form.errors.stock_umbral" class="mt-1 text-xs text-rose-600">
                {{ form.errors.stock_umbral }}
              </p>
            </div>
          </div>

          <!-- Acciones -->
          <div class="flex flex-wrap items-center justify-end gap-2 border-t border-amber-100 bg-amber-50/60 px-6 py-4">
            <Link
              :href="route('admin.dashboard')"
              class="rounded-xl border border-amber-200 bg-white px-4 py-2 text-xs font-medium text-amber-800 hover:bg-amber-50"
            >
              Cancelar
            </Link>

            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60"
            >
              <span v-if="form.processing">Guardando…</span>
              <span v-else>Guardar cambios</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
