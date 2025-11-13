<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { route } from 'ziggy-js'

const page = usePage()
const user = computed(() => page.props?.auth?.user ?? {})

const props = defineProps({
  cart: { type: [Array, Object], default: () => [] },
  direcciones: { type: Array, default: () => [] },
  // 👇 NUEVO: viene del controlador
  direccionPerfil: { type: String, default: '' },
})

const direcciones = computed(() => props.direcciones ?? [])

// ✅ Normaliza carrito
const cartRaw = computed(() =>
  Array.isArray(props.cart) ? (props.cart ?? []) : (props.cart?.items ?? [])
)

const cartList = computed(() =>
  cartRaw.value.map(p => ({
    nombre:   p?.nombre   ?? p?.name   ?? '',
    precio:   Number(p?.precio ?? p?.price ?? 0),
    cantidad: Number(p?.cantidad ?? p?.qty ?? 0),
  }))
)

const cartTotal = computed(() =>
  cartList.value.reduce((acc, it) => acc + it.precio * it.cantidad, 0)
)

// tipo de entrega
const tipo = ref('recoger')

// ✅ Nuevo: controla si el usuario quiere usar una nueva dirección
const usarNueva = ref(false)

// ¿tiene direcciones guardadas?
const tieneDirecciones = computed(() => (props.direcciones?.length ?? 0) > 0)

// formulario
const form = useForm({
  tipo_entrega: 'recoger',
  direccion_id: null,
  ciudad: 'Tapachula',
  notas: '',
  usar_nueva: false, // 👈 agregado
  direccion_nueva: { calle: '', numero: '', colonia: '', cp: '' },
})

const money = n =>
  new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n ?? 0))

// 👇 NUEVO: usar la dirección del perfil (precarga como "nueva" para no alterar backend)
const usarDireccionPerfil = () => {
  if (!props.direccionPerfil) return
  usarNueva.value = true
  form.direccion_nueva.calle = props.direccionPerfil
  form.direccion_nueva.numero = ''
  form.direccion_nueva.colonia = ''
  form.direccion_nueva.cp = ''
}

const enviar = () => {
  form.tipo_entrega = tipo.value
  form.usar_nueva = usarNueva.value // 👈 envía el valor al backend

  if (tipo.value === 'domicilio') {
    form.ciudad = 'Tapachula'

    if (usarNueva.value) {
      if (!form.direccion_nueva.calle?.trim()) {
        form.setError('direccion_nueva.calle', 'Captura la calle.')
        return
      }
      if (!form.direccion_nueva.colonia?.trim()) {
        form.setError('direccion_nueva.colonia', 'Captura la colonia.')
        return
      }
      if (!form.direccion_nueva.cp?.trim()) {
        form.setError('direccion_nueva.cp', 'Captura el código postal.')
        return
      }
    } else if (tieneDirecciones.value) {
      if (!form.direccion_id) {
        form.setError('direccion_id', 'Selecciona una dirección de Tapachula.')
        return
      }
    }
  } else {
    form.direccion_id = null
    form.ciudad = null
  }

  form.post(route('cliente.checkout.store'))
}
</script>

<template>
  <div class="min-h-screen bg-neutral-50">
    <!-- Header -->
    <header class="bg-white border-b">
      <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link :href="route('cliente.catalogo.index')" class="text-sm hover:underline">← Seguir comprando</Link>
          <h1 class="text-xl font-semibold">Finalizar compra</h1>
        </div>
        <nav class="flex items-center gap-3 text-sm">
          <span class="font-medium">Carrito</span>
          <span>›</span>
          <span class="font-bold text-rose-600">Checkout</span>
          <span>›</span>
          <span class="text-neutral-500">Confirmación</span>
        </nav>
      </div>
    </header>

    <main class="max-w-5xl mx-auto p-6 space-y-6">
      <!-- Datos del cliente -->
      <section class="rounded-xl border bg-white p-4">
        <h2 class="font-medium mb-2">Datos del cliente</h2>
        <div class="grid sm:grid-cols-3 gap-3 text-sm">
          <div><span class="text-neutral-500">Nombre:</span> <strong>{{ user.name }} {{ user.apellido ?? '' }}</strong></div>
          <div><span class="text-neutral-500">Email:</span> <strong>{{ user.email }}</strong></div>
          <div><span class="text-neutral-500">Teléfono:</span> <strong>{{ user.telefono ?? '—' }}</strong></div>
        </div>
      </section>

      <!-- Resumen del pedido (versión profesional con degradado cálido) -->
      <section
        class="relative rounded-2xl p-5 overflow-hidden text-neutral-800"
        style="
          background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 35%, #fca5a5 100%);
          box-shadow: 0 4px 25px rgba(255, 170, 100, 0.3),
                      inset 0 0 15px rgba(255, 255, 255, 0.5);
        "
      >
        <!-- Glow decorativo -->
        <div
          class="absolute inset-0 opacity-40 pointer-events-none"
          style="
            background: radial-gradient(circle at top right, rgba(255, 200, 150, 0.7), transparent 60%),
                        radial-gradient(circle at bottom left, rgba(255, 180, 180, 0.6), transparent 70%);
          "
        ></div>

        <h2 class="relative font-semibold text-lg mb-3 text-amber-900 tracking-tight drop-shadow-sm">
          🧾 Resumen del pedido
        </h2>

        <ul class="relative divide-y divide-amber-200">
          <li
            v-for="(p, i) in cartList"
            :key="i"
            class="flex justify-between py-2 text-sm"
          >
            <span>{{ p.nombre }} × {{ p.cantidad }}</span>
            <span class="font-medium">{{ money(p.precio * p.cantidad) }}</span>
          </li>
        </ul>

        <div
          class="relative mt-4 flex justify-end items-center text-base font-semibold text-amber-800"
        >
          <span class="mr-2">Total:</span>
          <span class="text-rose-700 drop-shadow-sm">{{ money(cartTotal) }}</span>
        </div>

        <!-- Línea decorativa inferior -->
        <div
          class="absolute bottom-0 left-0 w-full h-1.5"
          style="background: linear-gradient(90deg, #fbbf24, #f87171, #f59e0b)"
        ></div>
      </section>

      <!-- Tipo de entrega -->
      <section class="rounded-xl border bg-white p-4 space-y-4">
        <h2 class="font-medium">Tipo de entrega</h2>

        <div class="flex gap-6">
          <label class="flex items-center gap-2">
            <input type="radio" value="recoger" v-model="tipo" />
            <span>Recoger en tienda</span>
          </label>
          <label class="flex items-center gap-2">
            <input type="radio" value="domicilio" v-model="tipo" />
            <span>Envío a domicilio</span>
          </label>
        </div>

        <!-- DOMICILIO -->
        <div v-if="tipo === 'domicilio'" class="space-y-4">
          <div class="rounded border p-3 bg-amber-50 text-amber-800 text-sm">
            Solo realizamos envíos dentro de <strong>Tapachula</strong>.
          </div>

          <!-- Ciudad fija -->
          <div>
            <label class="block text-sm font-medium mb-1">Ciudad</label>
            <select v-model="form.ciudad" class="w-full border rounded p-2 text-sm" disabled>
              <option value="Tapachula">Tapachula</option>
            </select>
          </div>

          <!-- 👇 NUEVO: Bloque para mostrar y usar la dirección del perfil -->
          <div v-if="props.direccionPerfil" class="rounded border p-3 bg-neutral-50 text-neutral-700 text-sm flex items-start justify-between gap-3">
            <div>
              <div class="font-medium">Dirección de tu perfil</div>
              <div class="mt-1">{{ props.direccionPerfil }}</div>
              <div class="text-xs text-neutral-500 mt-1">Puedes usarla como base para tu envío.</div>
            </div>
            <button
              type="button"
              @click="usarDireccionPerfil"
              class="px-3 py-1.5 text-xs rounded bg-rose-600 text-white hover:bg-rose-700"
            >
              Usar esta dirección
            </button>
          </div>

          <!-- Si hay direcciones guardadas -->
          <div v-if="tieneDirecciones" class="space-y-2">
            <div class="flex gap-6 text-sm mb-2">
              <label class="flex items-center gap-2">
                <input type="radio" name="modoDireccion" :checked="!usarNueva" @change="usarNueva = false" />
                <span>Usar dirección guardada</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="radio" name="modoDireccion" :checked="usarNueva" @change="usarNueva = true" />
                <span>Agregar nueva dirección</span>
              </label>
            </div>

            <!-- Si usa guardada -->
            <div v-if="!usarNueva">
              <label class="block text-sm font-medium mb-1">Dirección</label>
              <select
                v-model="form.direccion_id"
                class="w-full border rounded p-2 text-sm"
                :class="{'border-rose-400': form.errors['direccion_id']}"
              >
                <option :value="null">Selecciona una dirección...</option>
                <option v-for="d in direcciones" :key="d.id" :value="d.id">
                  {{ d.calle }} {{ d.numero ?? '' }}, {{ d.colonia ?? '' }}
                </option>
              </select>
              <p v-if="form.errors['direccion_id']" class="text-rose-600 text-xs mt-1">
                {{ form.errors['direccion_id'] }}
              </p>
            </div>

            <!-- Si agrega nueva -->
            <div v-else class="grid sm:grid-cols-2 gap-3">
              <div class="sm:col-span-2 text-sm text-neutral-600">
                Captura una nueva dirección:
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Calle</label>
                <input
                  v-model="form.direccion_nueva.calle"
                  class="w-full border rounded p-2 text-sm"
                  :class="{'border-rose-400': form.errors['direccion_nueva.calle']}"
                />
                <p v-if="form.errors['direccion_nueva.calle']" class="text-rose-600 text-xs">
                  {{ form.errors['direccion_nueva.calle'] }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Número</label>
                <input v-model="form.direccion_nueva.numero" class="w-full border rounded p-2 text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Colonia</label>
                <input
                  v-model="form.direccion_nueva.colonia"
                  class="w-full border rounded p-2 text-sm"
                  :class="{'border-rose-400': form.errors['direccion_nueva.colonia']}"
                />
                <p v-if="form.errors['direccion_nueva.colonia']" class="text-rose-600 text-xs">
                  {{ form.errors['direccion_nueva.colonia'] }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">CP</label>
                <input
                  v-model="form.direccion_nueva.cp"
                  class="w-full border rounded p-2 text-sm"
                  :class="{'border-rose-400': form.errors['direccion_nueva.cp']}"
                />
                <p v-if="form.errors['direccion_nueva.cp']" class="text-rose-600 text-xs">
                  {{ form.errors['direccion_nueva.cp'] }}
                </p>
              </div>
            </div>
          </div>

          <!-- Si NO hay direcciones -->
          <div v-else class="grid sm:grid-cols-2 gap-3">
            <div class="sm:col-span-2 text-sm text-neutral-600">
              No tienes direcciones guardadas. Captura una nueva:
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Calle</label>
              <input
                v-model="form.direccion_nueva.calle"
                class="w-full border rounded p-2 text-sm"
                :class="{'border-rose-400': form.errors['direccion_nueva.calle']}"
              />
              <p v-if="form.errors['direccion_nueva.calle']" class="text-rose-600 text-xs">
                {{ form.errors['direccion_nueva.calle'] }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Número</label>
              <input v-model="form.direccion_nueva.numero" class="w-full border rounded p-2 text-sm" />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Colonia</label>
              <input
                v-model="form.direccion_nueva.colonia"
                class="w-full border rounded p-2 text-sm"
                :class="{'border-rose-400': form.errors['direccion_nueva.colonia']}"
              />
              <p v-if="form.errors['direccion_nueva.colonia']" class="text-rose-600 text-xs">
                {{ form.errors['direccion_nueva.colonia'] }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">CP</label>
              <input
                v-model="form.direccion_nueva.cp"
                class="w-full border rounded p-2 text-sm"
                :class="{'border-rose-400': form.errors['direccion_nueva.cp']}"
              />
              <p v-if="form.errors['direccion_nueva.cp']" class="text-rose-600 text-xs">
                {{ form.errors['direccion_nueva.cp'] }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Notas -->
      <section class="rounded-xl border bg-white p-4">
        <h2 class="font-medium mb-2">Notas (opcional)</h2>
        <textarea
          v-model="form.notas"
          rows="3"
          class="w-full border rounded p-2 text-sm"
          placeholder="Instrucciones especiales..."
        ></textarea>
      </section>

      <!-- Botón -->
      <div class="flex justify-end">
        <button
          @click="enviar"
          class="px-5 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700"
        >
          Confirmar pedido
        </button>
      </div>
    </main>

    <footer class="border-t bg-white mt-10">
      <div class="max-w-5xl mx-auto px-4 py-3 text-xs text-neutral-500">
        Pollería Pepe · Checkout
      </div>
    </footer>
  </div>
</template>
