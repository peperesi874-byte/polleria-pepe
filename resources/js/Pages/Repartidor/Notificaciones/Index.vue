<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const items = computed(() => page.props.items ?? [])

const fmtDateTime = (value) => {
  if (!value) return ''
  const d = new Date(value)
  return d.toLocaleString('es-MX', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-neutral-900">
            Notificaciones
          </h1>
          <p class="text-sm text-neutral-500">
            Pedidos que te han sido asignados y cambios de estado recientes.
          </p>
        </div>
      </div>
    </template>

    <div class="max-w-3xl mx-auto p-6">
      <div
        v-if="!items.length"
        class="rounded-2xl border bg-white/80 p-6 text-center text-sm text-neutral-500"
      >
        No tienes notificaciones recientes sobre tus pedidos.
      </div>

      <div
        v-else
        class="rounded-2xl border bg-white/80 divide-y shadow-sm"
      >
        <div
          v-for="n in items"
          :key="n.id"
          class="px-4 py-3 flex items-start gap-3"
        >
          <div class="mt-1 text-lg">
            <!-- iconito simple -->
            📦
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-xs text-neutral-400">
              {{ fmtDateTime(n.created_at) }}
            </p>
            <p class="text-sm font-semibold text-neutral-900">
              {{ n.titulo }}
            </p>
            <p class="text-sm text-neutral-600">
              {{ n.mensaje }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
