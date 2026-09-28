import { ref } from 'vue'
import { api, describeApiError } from '../api/client.js'

export function useTasks() {
  const tasks = ref([])
  const loading = ref(false)
  const saving = ref(false)
  const error = ref('')

  async function mutate(action) {
    saving.value = true
    error.value = ''

    try {
      await action()
    } catch (err) {
      error.value = describeApiError(err)
    } finally {
      saving.value = false
    }
  }

  async function load() {
    loading.value = true
    error.value = ''

    try {
      tasks.value = await api.listTasks()
    } catch (err) {
      error.value = describeApiError(err)
      tasks.value = []
    } finally {
      loading.value = false
    }
  }

  async function create(payload) {
    let created = null

    await mutate(async () => {
      created = await api.createTask(payload)
      tasks.value = [created, ...tasks.value]
    })

    return created
  }

  async function toggle(task) {
    await mutate(async () => {
      const updated = await api.updateTask(task.id, { completed: !task.completed })
      tasks.value = tasks.value.map((item) => (item.id === task.id ? updated : item))
    })
  }

  async function remove(task) {
    await mutate(async () => {
      await api.deleteTask(task.id)
      tasks.value = tasks.value.filter((item) => item.id !== task.id)
    })
  }

  return { tasks, loading, saving, error, load, create, toggle, remove }
}
