<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useTasks } from '../composables/useTasks.js'
import { validateTaskPayload } from '../utils/taskStats.js'

const router = useRouter()
const { create, saving, error } = useTasks()

const form = reactive({ title: '', description: '' })
const errors = ref({})
const sent = ref(false)

async function submit() {
  sent.value = true
  const result = validateTaskPayload(form)

  if (!result.valid) {
    errors.value = result.errors
    return
  }

  errors.value = {}
  const created = await create(result.value)

  if (created) {
    router.push({ name: 'tasks' })
  }
}
</script>

<template>
  <section class="card" style="max-width: 560px">
    <h2 style="margin-top: 0">Tambah tugas</h2>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <form novalidate @submit.prevent="submit">
      <label for="title">Judul</label>
      <input id="title" v-model="form.title" type="text" maxlength="255" placeholder="Contoh: Rancang pipeline CI/CD" />
      <p v-if="sent && errors.title" class="field-error">{{ errors.title }}</p>

      <label for="description">Deskripsi (opsional)</label>
      <textarea id="description" v-model="form.description" rows="4" placeholder="Catatan tambahan"></textarea>
      <p v-if="sent && errors.description" class="field-error">{{ errors.description }}</p>

      <div class="actions">
        <button type="submit" class="primary" :disabled="saving">
          {{ saving ? 'Menyimpan...' : 'Simpan' }}
        </button>
        <RouterLink :to="{ name: 'tasks' }">Batal</RouterLink>
      </div>
    </form>
  </section>
</template>
