<template>
  <div id="app">
    <RouterView />
  </div>
</template>

<script setup>
import { useAuth } from './composables/useAuth';

const { init } = useAuth()
init()
</script>

<style>
/* Importation du CSS global */
@import './assets/main.css';
</style>
