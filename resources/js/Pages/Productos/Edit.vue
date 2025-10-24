<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref, toRef } from 'vue'

const props = defineProps({
  producto: {
    type: Object,
    required: true,
  },
})

// Usar `producto` directo en el template
const producto = toRef(props, 'producto')

const form = useForm({
  nombre:          producto.value.nombre ?? '',
  descripcion:     producto.value.descripcion ?? '',
  precio:          Number(producto.value.precio ?? 0),
  stock:           Number(producto.value.stock ?? 0),
  activo:          !!producto.value.activo,
  imagen:          null,           // archivo nuevo (opcional)
  eliminar_imagen: false,          // para borrar la imagen actual sin subir otra
})

/* Preview de la nueva imagen seleccionada */
const preview = ref(null)
const onFile = (e) => {
  const file = e.target.files?.[0] ?? null
  form.imagen = file
  preview.value = file ? URL.createObjectURL(file) : null
}

const guardar = () => {
  // 1) Añadimos _method=put al payload
  form.transform((data) => ({
    ...data,
    _method: 'put',
  }))
  // 2) Enviamos como POST (no put) para mantener multipart/form-data
  form.post(route('productos.update', producto.value.id), {
    forceFormData: true,
    preserveScroll: true,
    onStart:  () => form.clearErrors(),
    onFinish: () => form.transform(d => d), // limpia el transform
  })
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-semibold text-indigo-800">✏️ Editar producto</h1>
      <Link :href="route('productos.index')" class="text-indigo-600 hover:underline">← Volver</Link>
    </div>

    <form
      @submit.prevent="guardar"
      enctype="multipart/form-data"
      class="space-y-4 bg-white border rounded-xl p-6"
    >
      <!-- Nombre -->
      <div>
        <label class="text-sm text-gray-600 mb-1 block" for="nombre">Nombre</label>
        <input
          id="nombre"
          v-model="form.nombre"
          type="text"
          class="w-full rounded-md border-gray-300 focus:ring-indigo-300"
        />
        <p v-if="form.errors.nombre" class="text-sm text-red-600 mt-1">
          {{ form.errors.nombre }}
        </p>
      </div>

      <!-- Precio / Stock / Activo -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm text-gray-600 mb-1 block" for="precio">Precio</label>
          <input
            id="precio"
            v-model.number="form.precio"
            type="number"
            min="0"
            step="0.01"
            class="w-full rounded-md border-gray-300 focus:ring-indigo-300"
          />
          <p v-if="form.errors.precio" class="text-sm text-red-600 mt-1">
            {{ form.errors.precio }}
          </p>
        </div>
        <div>
          <label class="text-sm text-gray-600 mb-1 block" for="stock">Stock</label>
          <input
            id="stock"
            v-model.number="form.stock"
            type="number"
            min="0"
            class="w-full rounded-md border-gray-300 focus:ring-indigo-300"
          />
          <p v-if="form.errors.stock" class="text-sm text-red-600 mt-1">
            {{ form.errors.stock }}
          </p>
        </div>
        <div>
          <label class="text-sm text-gray-600 mb-1 block" for="activo">Activo</label>
          <select
            id="activo"
            v-model="form.activo"
            class="w-full rounded-md border-gray-300 focus:ring-indigo-300"
          >
            <option :value="true">Sí</option>
            <option :value="false">No</option>
          </select>
          <p v-if="form.errors.activo" class="text-sm text-red-600 mt-1">
            {{ form.errors.activo }}
          </p>
        </div>
      </div>

      <!-- Descripción -->
      <div>
        <label class="text-sm text-gray-600 mb-1 block" for="descripcion">Descripción</label>
        <textarea
          id="descripcion"
          v-model="form.descripcion"
          rows="3"
          class="w-full rounded-md border-gray-300 focus:ring-indigo-300"
        />
        <p v-if="form.errors.descripcion" class="text-sm text-red-600 mt-1">
          {{ form.errors.descripcion }}
        </p>
      </div>

      <!-- Imagen -->
      <div>
        <label class="text-sm text-gray-600 mb-1 block">Imagen</label>

        <div class="flex items-center gap-4">
          <!-- Actual (si no hay preview nueva) -->
          <img
            v-if="!preview && producto?.imagenUrl"
            :src="producto.imagenUrl"
            alt="Imagen actual"
            class="h-16 w-16 object-cover rounded border"
          />
          <span v-else-if="!preview" class="text-xs text-gray-400">Sin imagen</span>

          <!-- Preview nueva -->
          <img
            v-if="preview"
            :src="preview"
            alt="Nueva imagen"
            class="h-16 w-16 object-cover rounded border"
          />
        </div>

        <input type="file" accept="image/*" @change="onFile" class="mt-2" />
        <p v-if="form.errors.imagen" class="text-sm text-red-600 mt-1">
          {{ form.errors.imagen }}
        </p>

        <!-- Eliminar imagen actual si no subirás otra -->
        <label
          class="flex items-center gap-2 mt-2 select-none"
          v-if="producto?.imagenUrl && !preview"
        >
          <input type="checkbox" v-model="form.eliminar_imagen" />
          <span class="text-sm text-gray-600">Eliminar imagen actual</span>
        </label>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <Link :href="route('productos.index')" class="px-4 py-2 rounded-md border">Cancelar</Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="bg-indigo-600 text-white px-5 py-2 rounded-md hover:bg-indigo-700 transition"
        >
          {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
        </button>
      </div>
    </form>
  </div>
</template>
