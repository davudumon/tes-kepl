export function summarizeTasks(tasks) {
  const list = Array.isArray(tasks) ? tasks : []
  const total = list.length
  const selesai = list.filter((task) => Boolean(task?.completed)).length
  const belum = total - selesai
  const persen = total === 0 ? 0 : Math.round((selesai / total) * 100)

  return { total, selesai, belum, persen }
}

export function validateTaskPayload(payload = {}) {
  const errors = {}
  const title = typeof payload.title === 'string' ? payload.title.trim() : ''
  const description = typeof payload.description === 'string' ? payload.description.trim() : ''

  if (title === '') {
    errors.title = 'Judul wajib diisi.'
  } else if (title.length > 255) {
    errors.title = 'Judul maksimal 255 karakter.'
  }

  if (description.length > 2000) {
    errors.description = 'Deskripsi maksimal 2000 karakter.'
  }

  return {
    valid: Object.keys(errors).length === 0,
    errors,
    value: { title, description },
  }
}

export function formatDate(value) {
  if (!value) {
    return '-'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(date)
}
