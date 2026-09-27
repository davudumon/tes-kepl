<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import TaskItem from '../components/TaskItem.vue'
import { useTasks } from '../composables/useTasks.js'
import { summarizeTasks } from '../utils/taskStats.js'

const { tasks, loading, saving, error, load, toggle, remove } = useTasks()
const stats = computed(() => summarizeTasks(tasks.value))

onMounted(load)
</script>

<template>
  <section>
    <div class="stats">
      <div class="stat">
        <b>{{ stats.total }}</b>
        <small>Total tugas</small>
      </div>
      <div class="stat">
        <b>{{ stats.selesai }}</b>
        <small>Selesai</small>
      </div>
      <div class="stat">
        <b>{{ stats.belum }}</b>
        <small>Belum</small>
      </div>
      <div class="stat">
        <b>{{ stats.persen }}%</b>
        <small>Progres</small>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <p v-if="loading" class="alert alert-empty">Memuat data dari Laravel&hellip;</p>

    <p v-else-if="!tasks.length && !error" class="alert alert-empty">
      Belum ada tugas. <RouterLink :to="{ name: 'task-create' }">Tambah yang pertama</RouterLink>.
    </p>

    <ul v-else style="list-style: none; margin: 0; padding: 0">
      <TaskItem
        v-for="task in tasks"
        :key="task.id"
        :task="task"
        :busy="saving"
        @toggle="toggle"
        @remove="remove"
      />
    </ul>
  </section>
</template>
