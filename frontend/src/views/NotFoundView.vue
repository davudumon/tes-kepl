<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <section class="card">
    <h2 style="margin-top: 0">404</h2>
    <p>Halaman yang kamu cari tidak ada.</p>
    <RouterLink :to="{ name: 'tasks' }">Kembali ke daftar tugas</RouterLink>
  </section>
</template>
