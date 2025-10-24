<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
  nombre: '',
  descripcion: '',
  precio: 0,
  stock: 0,
  activo: true,
  imagen: null, // archivo
})

const onFile = (e) => {
  form.imagen = e.target.files?.[0] ?? null
}

const guardar = () => {
  form.post(route('productos.store'), {
    forceFormData: true,     // necesario por el archivo
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-semibold text-indigo-800">➕ Nuevo producto</h1>
      <Link :href="route('productos.index')" class="text-indigo-600 hover:underline">← Volver</Link>
    </div>

    <form @submit.prevent="guardar" enctype="multipart/form-data" class="space-y-4 bg-white border rounded-xl p-6">
      <div>
        <label class="text-sm text-gray-600 mb-1 block" for="nombre">Nombre</label>
        <input id="nombre" v-model="form.nombre" type="text"
               class="w-full rounded-md border-gray-300 focus:ring-indigo-300"/>
        <p v-if="form.errors.nombre" class="text-sm text-red-600 mt-1">{{ form.errors.nombre }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm text-gray-600 mb-1 block" for="precio">Precio</label>
          <input id="precio" v-model.number="form.precio" type="number" min="0" step="0.01"
                 class="w-full rounded-md border-gray-300 focus:ring-indigo-300"/>
          <p v-if="form.errors.precio" class="text-sm text-red-600 mt-1">{{ form.errors.precio }}</p>
        </div>
        <div>
          <label class="text-sm text-gray-600 mb-1 block" for="stock">Stock</label>
          <input id="stock" v-model.number="form.stock" type="number" min="0"
                 class="w-full rounded-md border-gray-300 focus:ring-indigo-300"/>
          <p v-if="form.errors.stock" class="text-sm text-red-600 mt-1">{{ form.errors.stock }}</p>
        </div>
        <div class="flex items-center gap-2 pt-6">
          <input id="activo" type="checkbox" v-model="form.activo" class="rounded border-gray-300">
          <label for="activo" class="text-sm text-gray-700">Activo</label>
        </div>
      </div>

      <div>
        <label class="text-sm text-gray-600 mb-1 block" for="descripcion">Descripción</label>
        <textarea id="descripcion" v-model="form.descripcion" rows="3"
                  class="w-full rounded-md border-gray-300 focus:ring-indigo-300"></textarea>
        <p v-if="form.errors.descripcion" class="text-sm text-red-600 mt-1">{{ form.errors.descripcion }}</p>
      </div>

      <div>
        <label class="text-sm text-gray-600 mb-1 block">Imagen</label>
        <input type="file" accept="image/*" @change="onFile" />
        <p v-if="form.errors.imagen" class="text-sm text-red-600 mt-1">{{ form.errors.imagen }}</p>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <Link :href="route('productos.index')" class="px-4 py-2 rounded-md border">Cancelar</Link>
        <button type="submit" :disabled="form.processing"
                class="bg-indigo-600 text-white px-5 py-2 rounded-md hover:bg-indigo-700 transition">
          {{ form.processing ? 'Guardando...' : 'Guardar' }}
        </button>
      </div>
    </form>
  </div>
</template>
