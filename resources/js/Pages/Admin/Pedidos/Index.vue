<script setup>
import Shared from '@/Pages/Shared/Pedidos/Index.vue'
defineProps({ pedidos:Object, q:String, estado:String, asignado:String, estados:Array })
</script>
<template><Shared v-bind="$props" /></template>
