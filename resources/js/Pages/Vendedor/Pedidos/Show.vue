<script setup>
import Shared from '@/Pages/Shared/Pedidos/Show.vue'
defineProps({ pedido:Object, repartidores:Array })
</script>
<template><Shared v-bind="$props" /></template>
