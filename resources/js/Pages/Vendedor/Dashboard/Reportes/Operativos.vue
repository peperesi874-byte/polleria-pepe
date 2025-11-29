<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script>

<template>
  <AuthenticatedLayout>
    <!-- 🌈 HEADER ESTILO POLLERÍA (REPORTES OPERATIVOS) -->
    <template #header>
      <div class="bg-gradient-to-b from-[#FFF7E3] via-[#FFFDF8] to-[#FFE7E0] pt-4 pb-6">
        <div
          class="relative mx-auto max-w-7xl overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 px-6 py-6 shadow-lg"
        >
          <!-- blobs decorativos -->
          <div
            class="pointer-events-none absolute -left-16 -top-20 h-40 w-40 rounded-full bg-white/15 blur-3xl"
          />
          <div
            class="pointer-events-none absolute -right-10 bottom-0 h-40 w-40 rounded-full bg-black/15 blur-3xl"
          />

          <div
            class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between"
          >
            <!-- Izquierda -->
            <div class="space-y-3">
              <div
                class="inline-flex items-center gap-2 rounded-full bg-black/15 px-3 py-1 text-[11px] font-medium text-amber-50/95 backdrop-blur"
              >
                <span class="text-lg">📊</span>
                <span>Reportes operativos — Pollería Pepe</span>
              </div>

              <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-sm">
                  Reportes del día de trabajo
                </h1>
                <p class="mt-1 max-w-xl text-sm text-amber-50/90">
                  Consulta un resumen de los pedidos, estados y métricas clave del día seleccionado.
                </p>
              </div>

              <!-- Chips / contexto -->
              <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-amber-50/95">
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-white/95 px-3 py-1 font-semibold text-amber-900 shadow-sm ring-1 ring-white/70"
                >
                  <span class="text-sm">🗓️</span>
                  <span>Día seleccionado · vista operativa</span>
                </span>
                <span
                  class="inline-flex items-center gap-2 rounded-full bg-black/20 px-3 py-1"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-300" />
                  Ideal para resumen de turno y cortes de caja
                </span>
              </div>
            </div>

            <!-- Derecha -->
            <div class="flex flex-col items-end gap-3">
              <span
                class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-[11px] font-semibold text-slate-900 shadow-sm ring-1 ring-white/70"
              >
                <span class="relative flex h-2 w-2">
                  <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400/70 opacity-75"
                  />
                  <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400" />
                </span>
                Datos calculados en tiempo real
              </span>
              <p class="text-[11px] text-amber-50/90 max-w-[220px] text-right">
                Usa estos reportes para tomar decisiones rápidas durante el turno.
              </p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- 🌈 CONTENIDO: aquí va TODO tu layout actual de reportes -->
    <div class="bg-gradient-to-b from-slate-50 via-amber-50/15 to-slate-50">
      <div class="mx-auto max-w-7xl p-6">
        <!-- ⬇️ Aquí pegas tal cual tus tarjetas, filtros y tabla
             de "Reportes operativos" (lo que ya tenías hecho) -->
        <!-- TODO: contenido actual de tus reportes operativos -->
      </div>
    </div>
  </AuthenticatedLayout>
</template>
