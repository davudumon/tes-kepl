<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="shell">
    <header class="topbar">
      <div class="brand">Dashboard <span>Tugas</span></div>
      <nav class="nav">
        <RouterLink :to="{ name: 'tasks' }">Daftar Tugas</RouterLink>
        <RouterLink :to="{ name: 'task-create' }">Tambah</RouterLink>
        <RouterLink :to="{ name: 'about' }">Tentang</RouterLink>
      </nav>
    </header>

    <RouterView />
  </div>
</template>
