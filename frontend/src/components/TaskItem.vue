<script setup>
import { formatDate } from '../utils/taskStats.js'

defineProps({
  task: {
    type: Object,
    required: true,
  },
  busy: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['toggle', 'remove'])
</script>

<template>
  <li class="card task" :class="{ done: task.completed }">
    <div>
      <h3>
        {{ task.title }}
        <span class="badge" :class="task.completed ? 'badge-done' : 'badge-open'">
          {{ task.completed ? 'Selesai' : 'Belum' }}
        </span>
      </h3>
      <p v-if="task.description">{{ task.description }}</p>
      <p>Dibuat {{ formatDate(task.created_at) }}</p>
    </div>

    <div class="actions">
      <button type="button" :disabled="busy" @click="$emit('toggle', task)">
        {{ task.completed ? 'Batalkan' : 'Tandai selesai' }}
      </button>
      <button type="button" class="danger" :disabled="busy" @click="$emit('remove', task)">
        Hapus
      </button>
    </div>
  </li>
</template>
